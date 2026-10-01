# Auditoria do back-end: Customer, Processos, Licenciamentos e Mercadorias

Data: 01/10/2026. Decisão: **não aprovar a V1 para produção no estado avaliado**.

## Escopo e confiança

Avaliação do código atual do workspace, incluindo alterações locais ainda não commitadas. Foram inspecionados modelos, Actions, DTOs, repositórios, Policies, contexto da empresa ativa, Controllers, Livewire, importadores, jobs, observers, migrations e testes relevantes. Nenhum código de negócio foi alterado. Não foram executadas migrations, seeds ou limpeza de dados. Os testes usados operam com transações e rollback no banco isolado `logigate_testing`, protegido pelo runner existente.

Esta é uma auditoria técnica dos caminhos examinados, não uma certificação exaustiva de cada funcionalidade. Não valida infraestrutura real, backups/restauro, carga, S3 em produção, documentos oficiais aceites por sistemas externos ou conformidade fiscal/aduaneira. A análise das fórmulas compara contratos internos do código, sem parecer jurídico ou fiscal.

## Comparação dos módulos

| Ordem | Módulo | Estrutura observada | Principal fragilidade | Parecer |
|---|---|---|---|---|
| 1 | Processos | Actions, DTOs tipados, repositório, enums, regras de ciclo de vida e finalização, Policy e observer | A edição geral contorna a finalização; DTO perde campos; numeração sem garantia suficiente | É a melhor referência de organização, mas ainda bloqueado |
| 2 | Licenciamentos | Actions por operação, Value Objects monetários, suporte dos formulários, Policy e serviço de prontidão | Regras repartidas entre UI e Actions; conversão, duplicação e importação inconsistentes | Funcionalmente desenvolvido, mas com riscos de integridade |
| 3 | Mercadorias | CRUD centralizado em Actions, validação contextual, pauta com snapshots/auditoria e sincronização de contentores | Dupla vinculação, totais derivados e agrupamentos frágeis | Fluxo relativamente claro; integridade ainda insuficiente |
| 4 | Customer | Policies e acesso por empresa, DTOs, repositório, portal e separação de alguns casos de uso | Contrato de cliente compartilhado indefinido e namespaces incompatíveis com os caminhos | É o módulo mais confuso estruturalmente |

Ordem qualitativa de organização/manutenibilidade, não percentagem de conclusão nem prova de segurança. Mercadorias tem Actions mais uniformes que Customer, mas os seus defeitos afetam diretamente Processos e Licenciamentos.

## Bloqueios e achados prioritários

### A01 — P1 — Actions de Customer não carregam pelo namespace utilizado

`app/Application/Customer/Actions/DeleteCustomerAction.php:3` e `ToggleCustomerStatusAction.php:3` declaram `App\Domains\Customers\Actions`, enquanto os arquivos estão em `Application/Customer/Actions`. O Controller importa exatamente o namespace Domains. O PSR-4 em composer.json mapeia `App\` para `app/`; não há entrada dessas classes no classmap atual. A sondagem com o autoloader atual retornou `false` para `class_exists('App\Domains\Customers\Actions\DeleteCustomerAction')`.

Consequência: a resolução da dependência de exclusão pode falhar antes de entrar no método do Controller; a mudança de estado tem o mesmo desalinhamento de caminho/namespace. `Domains/Customers/Services/CustomerService.php` também depende de um `Domains/Customers/Actions/CreateCustomerAction` que não foi encontrado. Normalizar namespaces, imports e contratos; verificar resolução com o autoloader do build de produção.

### A02 — P1 — Edição comum consegue saltar o comando de finalização

`Application/Processo/Actions/AtualizarProcessoAction.php:28` chama apenas as regras de transição e datas. `Domains/Processo/Services/ProcessoLifecycleRules.php:20` impede voltar de estados terminais, mas permite passar de aberto a finalizado. O formulário aceita qualquer valor do enum em `ProcessoFormSupport.php` e encaminha Estado pelo DTO.

Consequência: um utilizador com `processos.update` pode atribuir Finalizado sem `processos.finalize`, sem as verificações de `ProcessoFinalizacaoRules`, sem gerar ContaDespacho ou DataFecho pela Action específica. Encaminhar transições terminais para comandos próprios e tornar as invariantes obrigatórias na camada de aplicação. Definir também quais campos/mercadorias podem mudar após fecho ou cancelamento: as Policies de edição e MercadoriaRules não impõem esse bloqueio.

### A03 — P1 — Editar mercadoria pelo licenciamento remove o vínculo ao processo

`Application/Mercadoria/DTOs/MercadoriaData.php:89` coloca `Fk_Importacao = null` no contexto de licenciamento. Depois de constituir processo, a mesma mercadoria tem os dois vínculos; `AtualizarMercadoriaAction` grava esses atributos. `MercadoriaParentTotalsService.php:16` aplica somente o delta ao proprietário presente no objeto atualizado.

Consequência: uma edição pelo licenciamento desassocia a mercadoria do processo, sem retirar o valor anterior do total do processo. A remoção do agrupamento procura os dois IDs em conjunto; após conversão, o agrupamento antigo pode continuar sem processo_id e não ser encontrado. Preservar vínculos na edição e explicitar se a constituição partilha itens ou cria um snapshot independente.

### A04 — P1 — Totais derivados ficam desatualizados ou duplicados

`MercadoriaParentTotalsService.php:30` incrementa FOB dos pais e peso do licenciamento; não recalcula CIF, ValorAduaneiro nem peso do processo. Licenciamento e Processo aceitam FOB/peso no cabeçalho, além de somarem valores quando se adicionam itens.

Exemplos: FOB inicial 100 + item de 100 pode produzir FOB 200; aumentar preço de um item muda FOB sem mudar CIF. A atualização de licenciamento recalcula CIF apenas quando o CIF recebido é zero. Há várias autoridades para os mesmos totais.

Escolher a fonte de verdade: somatórios de mercadorias, ou valores declarados separados dos calculados. Recalcular e reconciliar de forma transacional; distinguir peso bruto declarado de peso somado dos itens. Não basta trocar float por decimal.

### A05 — P1 — Conversão soma frete e seguro duas vezes e fixa câmbio

`Application/Licenciamento/Actions/ConstituirProcessoAction.php:71` fixa Cambio em 1 e, na linha 74, calcula ValorAduaneiro como CIF + frete + seguro. `CalcularCifLicenciamentoService` já define CIF como FOB + frete + seguro; `ProcessoFormSupport::calculatedValues` define ValorAduaneiro como CIF × câmbio.

Exemplo interno: FOB 100, frete 10, seguro 5 gera CIF 115, mas a conversão grava ValorAduaneiro 130. Utilizar o mesmo serviço de cálculo e exigir o câmbio aplicável; quando a intenção for um rascunho, deixar explícita a pendência do câmbio.

### A06 — P1 — Duplicação conserva ligações e estado do original

`Application/Licenciamento/Actions/DuplicarLicenciamentoAction.php:38` replica mercadorias, alterando somente licenciamento_id. Se o original já foi convertido, Fk_Importacao continua a apontar para o processo original. Na linha 45, replica agrupamentos sem remapear `mercadorias_ids` para os IDs novos, nem limpar processo_id. O cabeçalho também conserva Nr_factura/status_fatura, embora a associação de fatura não seja duplicada.

Consequência: novos itens ficam ligados a um processo antigo e agrupamentos apontam para itens originais; edições/exclusões podem deixar totais errados. Duplicar a partir de uma lista explícita de campos, limpar estado operacional/financeiro e reconstruir agrupamentos dos itens novos.

### A07 — P1 — Importações em fila não transportam a empresa

`Jobs/ImportCustomers.php:34` e `Jobs/ImportProcessos.php:33` instanciam importadores sem empresa/ator. `Models/Scopes/TenantScope.php:23` desativa o scope em console; `BelongsToTenant` também não atribui empresa em console. CustomersImport usa updateOrCreate por NIF, podendo atualizar um cadastro global compartilhado sem a proteção da CustomerPolicy. ProcessosImport não atribui empresa_id/user_id, nem usa as regras de criação.

Consequência: risco de alteração de cadastro de outra empresa e importação de processos inválida por falta de ownership. O registro Migracao tem empresa, mas essa informação não chega ao importador. Passar empresa e ator explicitamente, validar associações por empresa, evitar depender de Auth/session em workers e registrar resultado por linha. MigracaoController também não mostra autorização específica de customers.create/processos.create nesses métodos.

### A08 — P1 — Importação de licenciamentos aceita referências de outras empresas

`Imports/LicenciamentosImport.php:25` aceita cliente_id/exportador_id do arquivo. As três regras na linha 56 verificam apenas código, descrição e FOB; não verificam pertença desses IDs. O importador escreve o modelo diretamente, sem CriarLicenciamentoAction, criação de pasta e cálculo uniforme.

Além disso, as Actions CSV/Excel retornam void, enquanto `LicenciamentoController::import` usa o retorno para redirecionar a `licenciamentos.show`. Pode importar com sucesso e, depois, falhar no redirect por falta do parâmetro. Retornar um resultado de lote e redirecionar à lista/resumo; unificar validação e invariantes dos três formatos.

### A09 — P1 — Conversão não é idempotente sob concorrência e depende da tabela de faturação

`ConstituirProcessoAction.php:34` procura associação sem lock antes de criar o processo. `updateOrCreate` posterior não garante exclusão mútua. A migration de proc_licen_sales não estabelece unicidade de licenciamento_id, e fatura_id é obrigatório, embora o ramo de criação envie somente empresa_id/processo_id/licenciamento_id.

O problema de fatura_id é uma incompatibilidade com o schema versionado; não foi confirmado contra todos os índices/colunas do banco real. Se a tabela não existir, o código prossegue sem persistir a ligação usada para idempotência. Separar vínculo licenciamento/processo de vínculo com fatura, aplicar lock no licenciamento e restrição adequada no banco. O serviço de prontidão bloqueia na UI, mas ConstituirProcessoAction não aplica o mesmo contrato de prontidão.

### A10 — P1 — Numeração de ContaDespacho não é segura sob concorrência

`Domains/Processo/Services/ContaDespachoSequencialService.php:19` lê a última conta e calcula +1, sem lock. Uma transação por si só não reserva o número. A consulta filtra pelo created_at do processo, embora o número seja atribuído na finalização; finalizar em outro ano um processo antigo pode não entrar na sequência das próximas finalizações. As migrations examinadas não mostram unicidade para ContaDespacho ou (empresa_id, NrProcesso).

Definir série por empresa/ano de emissão, usar contador reservado atomicamente e índice único. GeradorNumeroProcessoService tem lockForUpdate, o que melhora o fluxo normal, mas falta proteção de unicidade no schema versionado e tratamento explícito da primeira emissão concorrente.

### A11 — P2 — Campos validados na edição de Processo não chegam ao banco

ProcessoEdit valida e apresenta vinheta, DataPartida e dados de exportação como quantidade_barris, valor_barril_usd, certificado_origem e guia_exportacao. AtualizarProcessoDTO não possui esses campos nem os inclui em toArray, embora CriarProcessoDTO contemple vários deles.

Consequência: edição pode anunciar sucesso enquanto descarta alterações. Atualizar o contrato e verificar cada campo editável através do formulário até à persistência. O DTO também remove todos os nulls, impedindo limpar campos opcionais: distinguir campo ausente de limpeza intencional.

### A12 — P2 — Customer mistura cadastro compartilhado e vínculo operacional

CustomerTenantAccessService aceita acesso pelo empresa_id direto OU pivot; canModifyProfile bloqueia perfis compartilhados. EloquentCustomerRepository::detachFromEmpresa remove apenas a pivot, portanto o dono direto continua com acesso. Ativo/inativo é global, enquanto a pivot também tem status. A busca por NIF é tenant-scoped no HTTP, mas a validação unique é global; o fluxo sugere localizar/associar cadastro existente sem um contrato de descoberta que funcione entre empresas.

Escolher explicitamente entre cadastro global + atributos por vínculo e cadastro integralmente por empresa. A proteção de escrita compartilhada é positiva, mas atualmente pode tornar o perfil não editável por nenhuma empresa associada. Não remover as proteções para resolver a UX: definir um responsável pelo cadastro e um fluxo autorizado de alteração.

### A13 — P2 — Auditoria de negócio é desigual

Processo implementa Auditable e tem observer manual. Na criação, ProcessoObserver.php:29 liga o evento manual ao usuário, não ao processo, antes de o processo ter ID. A atualização escreve uma segunda trilha manual além do mecanismo do modelo; é necessário verificar duplicação de eventos no HTTP. CustomerObserver preenche defaults, sem histórico de alterações. Licenciamento não mostra Auditable/observer equivalente; Mercadoria tem auditoria específica de pauta, mas não uma trilha completa do CRUD.

Incrementos em massa dos totais e a atribuição de Fk_Importacao por query não disparam observers dos modelos afetados. Implementar eventos de negócio com empresa, ator, entidade, antes/depois, motivo e correlação da operação. A timeline de prontidão de licenciamento baseada em updated_at não substitui histórico persistido.

### A14 — P2 — Agrupamentos usam somatórios não protegidos

MercadoriaAgrupada::updateExistingAgrupamento lê totais, soma e salva sem lock, permitindo perda de atualização concorrente. O método soma mesmo se o ID já estiver no JSON, logo não é idempotente. `mercadorias()` usa hasMany com JSON de IDs como chave local, o que não representa uma relação relacional válida.

Recalcular agrupamentos a partir dos itens ou proteger a atualização com lock e unicidade da chave de agrupamento. Substituir a relação baseada em JSON por vínculo apropriado ou consulta explícita. Adicionar testes de concorrência e repetição.

## Validação executada

| Bateria | Testes | Assertions | Resultado |
|---|---:|---:|---|
| CustomerPolicy, ProcessoTenantIsolation, LicenciamentoTenantIsolation, ContentorMercadoriaSync | 10 | 20 | 1 falha, 1 erro |
| ProcessoPolicy/ShowActions e LicenciamentoPolicy/ShowActions | 18 | 13 | 10 falhas, 7 erros |
| ActiveTenantContext Feature/Unit e CompanyScopedRbac | 34 | 318 | Sem falhas ou erros; avisos de depreciação |

Total: 62 testes, 351 assertions, 11 falhas e 8 erros. Os 43 restantes não falharam; alguns são cenários negativos, portanto não provam que o fluxo positivo correspondente funciona. O runtime foi PHP 8.5.10, enquanto composer configura plataforma 8.2. As execuções relataram depreciações, não investigadas nesta auditoria.

Importante: grande parte das falhas antigas resulta de fixtures que não autenticam o ator, não configuram sessão empresa_id ou atribuem permissões sem o contexto RBAC da empresa. CustomerPolicyTest usa Gate::forUser sem sessão/ator autenticado e sem grants; ContentorMercadoriaSyncTest autentica, mas não seleciona empresa ativa. Isso NÃO demonstra que as proteções novas devem ser relaxadas. Demonstra que a suíte dos módulos ainda não comprova os fluxos positivos após a mudança de tenancy/RBAC. Os 34 testes novos são evidência favorável à fundação de segurança, não cobertura suficiente para aprovar os quatro módulos.

Não executei MercadoriaLicenciamentoLivewireTest, pois usa RefreshDatabase, que pode recriar o banco. Não executei a suíte inteira nem chamadas a serviços externos.

## Plano de liberação

1. Corrigir resolução PSR-4 de Customer e provar CRUD/estado com autoloader do build final.
2. Fechar invariantes de lifecycle: editar não finaliza; finalização exige permissão própria e requisitos completos; definir edição de itens após fecho/TXT/faturação.
3. Definir fonte de verdade dos totais e propriedade das mercadorias; corrigir A03–A06 e reconciliar dados existentes.
4. Corrigir ou desativar no servidor importações inseguras e duplicação/conversão até serem verificadas. Ocultar botões é insuficiente.
5. Resolver concorrência/idempotência e compatibilidade do schema de numeração/conversão.
6. Atualizar fixtures antigas com empresa ativa e grants por empresa, mantendo testes negativos; acrescentar regressões dos achados e fluxo completo Customer → Licenciamento → Mercadorias → TXT → Processo → Finalização.
7. Ensaiar deploy num banco descartável com schema criado pelas migrations, validar dados legados, workers, relatórios, arquivos, backup/restauro e comportamento com dois tenants e utilizadores com permissões diferentes.

Critério de aprovação: todos os P1 resolvidos ou funcionalidade efetivamente desativada no servidor; fluxos positivos e negativos verdes; totais/vínculos reconciliados; concorrência testada; schema reproduzível e ensaio operacional concluído. P2 pode ser faseado apenas quando não quebra um fluxo exposto da V1 — campos silenciosamente descartados não devem ser publicados como editáveis.

É viável preparar uma primeira versão sem reescrever tudo. Priorizar integridade, autorização e contratos dos casos de uso; consolidar Actions e DTOs depois. A reorganização de pastas por si só não resolve os bloqueios encontrados.
