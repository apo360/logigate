# Correções da auditoria de 01/10/2026

Este documento acompanha entregas pequenas. Não substitui a auditoria nem aprova a V1 para produção. As alterações da Etapa 1 foram preservadas; não foram feitos commits, migrations em produção, chamadas a serviços externos ou reconciliação de dados reais.

## Etapa 2 — A02 e A11

### Problemas confirmados antes da alteração

- `AtualizarProcessoAction` permitia passar para Finalizado através de `processos.update`, sem os requisitos e a autorização da finalização.
- A criação também aceitava estado Finalizado e campos de finalização.
- `AtualizarProcessoDTO` omitia vinheta, DataPartida e os campos de exportação. Eliminava todos os nulls, impossibilitando limpar opcionais.
- O modelo não incluía DataPartida em fillable e não existia migration para essa coluna. O repositório descartava campos sem coluna silenciosamente.
- Mercadorias de processos terminais podiam ser alteradas, também pelo licenciamento quando os itens eram partilhados.

### Contratos implementados

- Finalizado, ContaDespacho e DataFecho são emitidos exclusivamente pelo comando FinalizarProcessoAction. A criação normal não aceita esses campos.
- A finalização exige processos.finalize, os requisitos obrigatórios e um processo não terminal. Uma segunda finalização é recusada sem emitir outra conta.
- Após Finalizado ou Cancelado, apenas observacoes podem mudar. Campos equivalentes enviados pelo formulário são aceites; qualquer alteração material nos restantes é recusada.
- Criar, editar, remover ou sincronizar contentores de mercadorias de um processo terminal é recusado, incluindo o acesso por licenciamento para itens partilhados.
- Atualização e finalização bloqueiam a linha do processo dentro da transação. Escritas de mercadorias bloqueiam o processo antes de verificar o estado.
- fromArray/fromRequest usam a presença da chave para distinguir ausência de null explícito. Campos ausentes ficam intactos; null e string vazia limpam opcionais; zero numérico é preservado. Na construção direta com argumentos nomeados, os valores não nulos continuam a representar a atualização parcial.
- Campos enviados sem coluna provocam erro explícito. A migration aditiva `2026_10_01_210001_add_data_partida_to_processos_table.php` prepara DataPartida; não foi aplicada ao banco existente.
- O formulário de edição só apresenta e envia DataPartida quando a coluna existe, mantendo os restantes campos funcionais antes da aplicação da migration.

### Ficheiros afetados

- app/Application/Processo/Actions/{AtualizarProcessoAction,CriarProcessoAction,FinalizarProcessoAction}.php
- app/Application/Processo/DTOs/AtualizarProcessoDTO.php
- app/Domains/Processo/Services/{ProcessoLifecycleRules,ProcessoFinalizacaoRules}.php
- app/Domains/Processo/Repositories/EloquentProcessoRepository.php
- app/Application/Mercadoria/Services/MercadoriaTenantAccessService.php
- app/Livewire/Processo/ProcessoEdit.php
- resources/views/livewire/processo/processo-edit.blade.php
- app/Models/Processo.php
- database/migrations/2026_10_01_210001_add_data_partida_to_processos_table.php
- tests/Feature/Processo/ProcessoLifecycleAuditTest.php
- tests/Unit/Processo/AtualizarProcessoDTOTest.php
- tests/Support/IsolatedDatabaseTestCase.php

### Validação e limites

Os novos testes de banco verificam APP_ENV=testing, conexão MySQL local, nome configurado e efetivo logigate_testing e tabelas InnoDB antes de abrir a transação. Cada teste reverte as escritas. Não usam RefreshDatabase, migrations ou seeds.

A bateria verifica o bloqueio de finalização pela atualização e criação, formulário Livewire, ausência da permissão de finalização, campos de exportação, null explícito, invariantes dos estados terminais, mercadorias partilhadas e finalização autorizada com ContaDespacho/DataFecho. O teste de DataPartida verifica o erro explícito no schema atual; a persistência nesse campo aguarda aplicar e ensaiar a migration num ambiente descartável.

Execução final: `php tests/run-isolated.php tests/Feature/Processo/ProcessoLifecycleAuditTest.php tests/Unit/Processo/AtualizarProcessoDTOTest.php tests/Feature/Customer/CustomerPolicyTest.php tests/Unit/Customer/CustomerAutoloadTest.php`. Resultado: 17 testes, 182 assertions, nenhuma falha ou erro; 33 depreciações. A bateria da Etapa 2 isoladamente contém 10 testes e 116 assertions. A sintaxe PHP e `git diff --check` passaram; os testes Livewire também renderizaram o Blade alterado.

Não foi provada a unicidade concorrente da numeração (A10), nem ensaiado o schema completo ou o deploy. Estes pontos continuam nas Etapas 5 e 6. Permanecem avisos de depreciação do runtime PHP 8.5 e avisos PSR-4 previamente identificados.

## Decisões da Etapa 3

- **Mercadorias: confirmado pelo utilizador.** Processo e licenciamento referenciam os mesmos itens; um licenciamento também pode nascer durante um processo para licenciar as mercadorias desse processo. Edições devem preservar ambos os vínculos. Duplicação cria itens novos sem ligação ao processo original.
- **FOB e pesos: confirmado pelo utilizador.** Separar valores declarados dos somatórios das mercadorias. Os campos existentes fob_total/peso_bruto conservam os valores declarados. Somatórios calculados são projeções das mercadorias, sem incrementos sobre o cabeçalho.
- **Câmbio: confirmado pelo utilizador.** A conversão permite rascunho com taxa pendente; a finalização exige taxa informada e confirmada, com origem e data.

## Etapa 3 — A03, A04, A05, A06 e A14

### Problemas confirmados e alterações

- A edição pelo licenciamento atribuía Fk_Importacao=null. Essa atribuição foi removida, preservando ambos os vínculos.
- A criação de itens num licenciamento com itens já ligados a um único processo herda esse processo. Escritas em licenciamento com vínculos a vários processos são recusadas. A edição, remoção e criação por licenciamento também respeitam o estado terminal do processo.
- Foram removidos incrementos de FOB/peso dos cabeçalhos. `totaisMercadorias()` agrega os itens atuais em uma consulta e fornece FOB, peso, CIF calculado e Valor Aduaneiro calculado. Como os somatórios são projeções, criação/edição/remoção não deixam cópias persistidas desatualizadas. Não há backfill de cabeçalhos legados: valores anteriormente corrompidos precisam do diagnóstico e da reconciliação da Etapa 6.
- CIF declarado derivado é recalculado como FOB declarado + frete + seguro, na criação/edição de Processo e Licenciamento. Valor Aduaneiro é CIF × câmbio, ou null quando a taxa está pendente. Valores derivados enviados pelo cliente não substituem o cálculo do servidor.
- Formulários de edição identificam os valores declarados e mostram separadamente FOB/peso calculados das mercadorias.
- Agrupamentos são reconstruídos por documento/código a partir dos itens sob bloqueio de linha do pai e transação. Itens partilhados têm agrupamentos independentes em ambos os documentos. Repetir a operação não acumula valores ou adições. A consulta de itens do agrupamento é explícita; não usa JSON como chave de relação hasMany. O reagrupamento deixou de chamar uma stored procedure sem contexto/autorização.
- Duplicação usa listas explícitas de campos, cria IDs novos, limpa vínculo de processo e estado operacional e reconstrói agrupamentos dos itens novos. Não replica faturas, documentos ou contentores.
- Conversão bloqueia o licenciamento, recarrega os itens, verifica a prontidão dentro da Action e cria processo Aberto com Cambio/ValorAduaneiro pendentes. Preserva IDs dos itens. O código do tipo de declaração é resolvido para o ID do regime aduaneiro, em vez de ser usado como ID diretamente.
- A conversão deixou de gravar em proc_licen_sales. Nesta entrega, o processo existente é identificado pelos vínculos das mercadorias, com rejeição de vínculos parciais/ambíguos. O vínculo próprio e permanente e sua unicidade continuam na Etapa 5; ainda não há garantia para um licenciamento convertido depois de remover todos os seus itens.
- A migration aditiva `2026_10_01_210002_add_exchange_confirmation_to_processos_table.php` prepara origem/data/confirmação de câmbio. O DTO e o formulário suportam esses campos quando o schema os contém. Alterar a taxa sem confirmar remove a confirmação. A finalização recusa taxa não confirmada e também recusa schema sem esses campos. Nenhuma migration foi aplicada: **a finalização permanece bloqueada até o ensaio e aplicação desta migration no ambiente autorizado**. Dados antigos não serão confirmados automaticamente.

### Validação e pendências

A bateria adicional usa banco local logigate_testing com verificação de InnoDB e rollback. Exercita Actions reais de mercadorias, preservação dos vínculos, somatórios em ambos os documentos, remoção, criação após conversão, bloqueio de estados terminais, repetição dos agrupamentos, IDs da duplicação, cálculos com taxa pendente/positiva, recusa de conversão incompleta e conversão em rascunho. A criação de pasta na conversão usa um test double: não há chamada a S3 ou a serviços externos.

Ficheiros da Etapa 3: Actions de criar/atualizar/excluir/reagrupar Mercadoria; MercadoriaData; MercadoriaAgrupamentoService, MercadoriaParentTotalsService e MercadoriaTenantAccessService; modelos MercadoriaAgrupada, Licenciamento e Processo; Actions de criar/atualizar/duplicar/constituir processo de Licenciamento; Actions de criar/atualizar Processo, AtualizarProcessoDTO, ProcessoFormSupport e ProcessoFinalizacaoRules; ProcessoEdit; os Blades de edição de Processo/Licenciamento; migration de confirmação de câmbio; MercadoriaAuditContractsTest e regressões de DTO/lifecycle.

Execução conjunta das Etapas 1–3: 25 testes, 268 assertions, nenhuma falha/erro e 33 depreciações. Sintaxe PHP e diff passaram. Estes números representam a bateria selecionada, não a suíte inteira nem o schema atualizado.

Ainda não foram executados testes com dois workers concorrentes nem aplicadas as migrations num banco descartável. A persistência e confirmação do câmbio com o novo schema e a finalização positiva após essa confirmação aguardam esse ensaio; o teste no schema atual comprova a recusa sem emitir ContaDespacho/DataFecho. A série anual, a associação permanente licenciamento/processo, índices únicos, importações e dados legados continuam pendentes nas etapas seguintes.

## Preparação das Etapas 4–6

### Alinhamento do submódulo Exportadores — 04/10/2026

Por instrução do utilizador, esta implementação mantém a sobreposição com Clientes em standby e adia os testes. Não altera Policies, permissões ou o contexto global de autenticação/tenancy.

CreateOrAssociateExportadorAction é o caminho canónico de criação para formulário, modal, serviço e importação; as duas Actions antigas delegam nele, sem regras próprias. ExportadorValidation reúne os limites dos campos e a existência do país. UpdateExportadorAction decide o âmbito no domínio, dentro de transação com lock, mantendo Actions separadas para perfil e associação. O formulário envia explicitamente global para o cadastro e local para a associação; o perfil partilhado permanece protegido pela Policy existente.

ExportadorDetailQuery fornece uma página show funcional com cadastro, país, associação e contagens de processos/licenciamentos da empresa ativa. A listagem dá acesso ao detalhe. O contador com_licenciamentos conta exportadores associados que tenham pelo menos um licenciamento da empresa ativa, através de EXISTS, sem duplicar exportadores por número de licenciamentos.

A importação passa a exigir cabeçalho e mapear Exportador, ExportadorTaxID, AccountID, Endereco, Telefone, Email, Pais (ID), Website, Cidade e os campos da associação. Usa a validação e criação canónicas. O job transporta ator/empresa, verifica pertença e permissão atual, instala um contexto temporário restrito à execução e restaura-o em finally. A importação é transacional: uma linha inválida reverte o lote, marca failed e lança o erro identificado pela linha. O motivo é registado no log; a interface existente mostra o estado do lote, sem relatório por linha persistido. Exportadores existentes são associados sem atualização do perfil central. Filas antigas sem ator/empresa não devem ser reexecutadas como se fossem novos pedidos; os novos argumentos são obrigatórios.

Verificação realizada: lint PHP dos ficheiros afetados, compilação/análise de sintaxe dos quatro Blades afetados e diff do escopo. Não foram criados/executados testes, aplicadas migrations, importados dados reais ou chamados serviços externos. Ensaios funcionais e de fila ficam pendentes conforme pedido; as restantes fases da auditoria continuam pendentes.

### Correção do accessor guia_fiscal

O accessor de Processo lia os custos no próprio processo e resolvia porto como a relação Porto, causando erro em array_sum durante a serialização. Agora delega ao EmolumentoTarifaTotalsService com a relação emolumentoTarifa: usa os custos da tarifa (incluindo multas) e retorna zero quando ela não existe. A relação porto permanece disponível. Regressões cobrem a relação Porto carregada, soma numérica e toArray com/sem tarifa, sem acesso ao banco. Validação selecionada: 5 testes, 13 assertions, nenhuma falha/erro e 33 depreciações; lint e diff passaram.

A inspeção confirmou os achados de importação: os jobs ImportCustomers e ImportProcessos não transportam ator/empresa; os importadores escrevem diretamente nos modelos; CustomersImport pode alterar perfis partilhados; CSV/Excel de licenciamento retornam void enquanto o controller redireciona para um detalhe. As importações continuam por corrigir. O lock e a validação de prontidão da conversão foram antecipados na Etapa 3; não representam a conclusão da Etapa 5.

Permanecem a implementar resultados de lote por linha, contexto explícito dos workers, validação tenant de referências, cálculos consistentes, conversão/numeração concorrentes, proposta de separação do vínculo de faturação, auditoria atribuível, diagnóstico de legados e ensaio operacional descartável.


### Front-end de Exportadores — 04/10/2026

Index/listagem, criação/edição, modal rápido e detalhe foram alinhados com o padrão visual de Clientes e Licenciamento: cabeçalhos, cartões, azul nas ações principais, grelhas responsivas e modo escuro. Os campos de cadastro são partilhados entre formulário e modal; erros por campo, valores antigos, indicação dos campos obrigatórios e estados de carregamento foram adicionados. Cadastro e associação mantêm formulários separados na edição. A listagem mostra estado/código interno da associação, ordenação acessível, pesquisa, paginação, estados vazios e ações conforme Policy. A confirmação esclarece que remove apenas a associação. Os modais incluem Escape, foco contido e bloqueio de scroll.

A consulta base conserva agora a relação BelongsToMany para hidratar pivot com os dados necessários à tabela. Foram removidos os scripts Select2 e dependências CDN particulares dos formulários. Não se alteraram regras de negócio nem a sobreposição com Clientes. Verificação: sintaxe dos oito Blades, lint do repositório e diff do escopo. Não foram executados testes funcionais nem validação visual no navegador; estes ensaios permanecem pendentes conforme pedido anterior.
