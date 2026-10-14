# Análise completa dos relatórios e templates Jasper

Data: 13/10/2026. Código local do LogiGate. Esta análise não altera controllers, serviços operacionais, templates, facturação ou dados comerciais. Complementa a análise anterior de RelatorioController.

## Conclusão

O sistema mantém três percursos de relatórios: relatórios HTML de Licenciamentos, impressão operacional Jasper de Processos e serviços Jasper legados sem chamada registada. Há controlo de empresa e política nos comandos modernos de Processos, mas o contrato com os templates e a ligação JDBC não garantem coerência com o Laravel.

Os riscos prioritários são: construção insegura do comando externo, consultas a bases fixas diferentes da configurada, ficheiros de saída partilhados, cálculos tributários duplicados no extrato e operações financeiras anteriores à geração da requisição. Duas fontes JRXML apresentam bloqueios estruturais; passar na validação XML/XSD não significa que as restantes gerem documentos correctos.

## Mapa das ligações

| Entrada | Percurso | Resultado e estado |
|---|---|---|
| relatorio.licenciamento | RelatorioController::SelecionarRelatorio → relatorios/selecionar | Formulário HTML activo. |
| relatorio.visualizar | RelatorioController::RelatorioLicenciamento → LicenciamentoReportService → relatorios/licenciamento | Agregados HTML activos. Não executa Jasper; os quatro nomes JRXML referenciados não existem em resources/reports. |
| RelatorioController::generateReport | ReportService::renderProcessReport → hello_world.jrxml | Método legado, sem rota/chamada encontrada; não confundir com os endpoints activos de Processos. |
| processos.print | ProcessoController::printNotaDespesa → PHPJasper → nota_despesa.jrxml | Rota GET activa; autoriza processo/print, mas grava saída fixa em reports/nota_despesa.pdf. |
| processos.Extrato_mercadoria | ProcessoController::printExtratoMercadoria → PHPJasper → extrato_mercadoria.jrxml | Rota GET activa; autoriza processo/print, saída fixa em reports/extrato_mercadoria.pdf. |
| processos.imprimirCarta / processo.print.requisicao | ProcessoController::printCartaDiversa → conta corrente/facturas → PHPJasper → Requisicao.jrxml | Rotas POST activas. Geração tem efeitos financeiros anteriores à impressão. |
| ProcessoShow::emitirNotaDespesa | EmitirNotaDespesaProcessoAction → ProcessoJasperService → nota_despesa.jrxml | Fluxo Livewire activo. Verifica tenant e print; saída privada por processo, mas o nome é fixo para pedidos desse processo. |
| ProcessoShow::gerarExtratoMercadoria | GerarExtratoMercadoriaProcessoAction → ProcessoJasperService → extrato_mercadoria.jrxml | Fluxo Livewire activo com tenant/print; mesmo template e ligação JDBC dos riscos abaixo. |
| ReportService::renderInvoiceReport | PHPJasper → facturas_geral.jrxml | Método sem chamada encontrada no código inspeccionado. Não é prova de que este template seja usado na emissão actual de facturas. |
| JasperService | Wrapper compileReport/generateReport | Injectado no RelatorioController, mas não utilizado pelos seus métodos. |
| Painéis operacionais | OperationalReportController → OperationalReport → DomPDF/Excel/CSV | Percurso separado, sem uso de JRXML. Uma futura integração deve partilhar consultas/definições, preservando permissões. |

## Verificação dos cinco templates

Foi usada leitura XML sem resolução de entidades externas e validação contra **jasperreport.xsd e components.xsd extraídos do JasperReports 6.7.0 instalado**, sem rede. Os comentários dos templates referem Studio/Library 6.21.0 ou 6.21.3; essa diferença exige teste real e não prova, por si só, incompatibilidade de todos os templates.

| Template | XML | XSD 6.7 instalado | SQL examinado | Avaliação |
|---|---|---|---|---|
| hello_world.jrxml | Bem formado | Aprovado | EXPLAIN falha no original e após remover só o prefixo da base | Legado incompatível com o esquema actual. |
| nota_despesa.jrxml | Bem formado | Aprovado | EXPLAIN válido | Contrato de parâmetros, precisão, moeda e origem dos dados precisam de reparação. |
| extrato_mercadoria.jrxml | Bem formado | Rejeitado: jr:detail, linha 921 | Consultas principal e subdataset válidas com EXPLAIN | Bloqueio estrutural no motor instalado e divergências tributárias relevantes. |
| Requisicao.jrxml | Malformado | Não validável | Duas consultas válidas após recuperação exclusivamente em memória para análise | Não compilar o original; corrigir também o fluxo financeiro anterior à geração. |
| facturas_geral.jrxml | Bem formado | Aprovado | Query vazia, sem campos de linhas | Maquete com valores fixos; não representa uma factura preenchida por dados reais. |

EXPLAIN verifica planeamento e resolução de tabelas/colunas; não executa a consulta de dados nem prova que a população, moeda e significado dos valores sejam correctos. Não foi executado EXPLAIN ANALYZE. As duas consultas da requisição foram extraídas de uma cópia lógica em memória onde se fechou o parâmetro descricao; o original no disco foi preservado.

### hello_world.jrxml

- A query, nas linhas 9–96, fixa honga_despachantes e tabelas antigas Processos, Importacao, Empresa, TarifaDAR, TarifaPortuaria e TarifaDU.
- No ambiente inspeccionado, a base honga_despachantes não existe. Remover apenas esse prefixo continua a falhar porque empresa, no singular, não existe na base configurada; também há identificadores legados como ProcessoID, CustomerID, UserID e FK_Empresa.
- P_processo e P_user são String. P_processo é inserido por `$P!{}` no SQL; P_user aparece no rodapé, sem limitar empresa ou acesso ao processo.
- ReportService guarda hello_world.pdf num caminho comum, sem selecção de processo autorizada nem isolamento de saída por pedido.
- Não foi encontrada uma rota/chamada a generateReport. Este risco é latente nesse método; não deve ser apresentado como falha de autorização já exposta por uma rota activa.

### nota_despesa.jrxml

- Linhas 56–65: mercadorias são lidas de **logigatedb.mercadorias**, mesmo que o Laravel esteja ligado a outra base. O filtro usa apenas Fk_Importacao. A existência e validade SQL dessa base foram confirmadas com EXPLAIN, sem ler os seus registos.
- A nota combina cabeçalho e totais recebidos do Laravel com linhas carregadas pelo JDBC. Um ID coincidente noutra base pode produzir um documento híbrido. Não há parâmetro empresa_id no contrato SQL.
- EmitirNotaDespesaProcessoAction passa multas, mas o JRXML não declara esse parâmetro. Não foi testada a reacção do CLI ao parâmetro desconhecido; o template actual não o representa adequadamente.
- juros_mora e orgaos_ofiais são declarados mas não têm expressão de apresentação. O total guia_fiscal vindo do serviço canónico inclui estes valores e multas; por isso a soma das linhas visíveis pode não justificar o total quando forem diferentes de zero.
- TotalDespesa usa calculation=Sum sobre uma expressão composta por parâmetros do processo: acumularia o mesmo montante por cada linha de mercadoria. A variável **não é apresentada actualmente**, pelo que não se afirma que ela já multiplique o total impresso. Deve ser removida ou reconciliada antes de ser reutilizada.
- guia_fiscal, Cambio e despesas usam java.lang.Float; os valores monetários passam de precisão decimal/double para uma representação de 32 bits. Recomenda-se contrato decimal/BigDecimal consistente, não fórmulas financeiras em Float no template.
- total_moeda divide guia_fiscal por Cambio sem guarda para zero/ausência. O comando de impressão não exige finalização nem câmbio positivo; processos ainda em preparação podem chegar a este ponto. A conversão não inclui proveniência/data do câmbio no documento.
- O parâmetro Fob_total recebe processo.cif. A legenda impressa diz “Valor Cambial”, não “FOB”; há uma inconsistência de nomenclatura que exige formalizar se este campo representa FOB ou CIF, sem trocar valores automaticamente.
- Ref_Cliente vem de cliente.RefCliente, em vez de processo.RefCliente; um cenário com referência do processo preenchida produziu parâmetro vazio. nacionalidade_transporte é preenchida com o país de origem, em vez do campo/relação de nacionalidade do transporte. Ambos foram confirmados capturando os parâmetros do comando moderno com gerador falso.
- As rotas antigas consultam tipoTransporte, enquanto o modelo tem transporte. O comando moderno usa a relação correcta.
- DataAbertura/DataChegada são java.util.Date; as chamadas passam strings, ou string vazia quando falta data. O formato e a semântica de nulo precisam de contrato explícito e teste do CLI.
- Não existe tratamento explícito de noData no relatório principal. Deve haver teste para processo sem mercadorias, para não depender do comportamento padrão do Jasper.

### extrato_mercadoria.jrxml

- A fonte contém `<jr:detail/>` como filho de jr:table, na linha 921. O XSD de componentes instalado rejeita esse elemento; as células já existem como jr:detailCell dentro das colunas. O erro foi confirmado com os dois esquemas carregados, não apenas com o esquema principal.
- A query principal usa logigatedb.processos/customers; o subdataset MercadoriaExtrato usa mercadorias/processos/pauta_aduaneira sem prefixo. Assim, **cabeçalho e detalhe podem vir de bases diferentes na mesma ligação JDBC**.
- O subdataset recebe processoID a partir de id e utiliza REPORT_CONNECTION; a ligação de parâmetros do dataset foi encontrada, não está ausente.
- O template reparte frete/seguro pelo preço do item, mas usa o **ValorAduaneiro de todo o processo em cada item** para Direitos, Emolumentos e IVA. Exemplo puramente matemático testado: duas mercadorias com base individual 50, processo com base 100 e taxa 10% produzem 20 pela repetição da base total, em vez de 10 sobre as bases individuais. Este não é um parecer sobre a taxa legal aplicável.
- A regex de taxa só aceita algarismos inteiros. 7.5 e 10% são rejeitados como percentagens, embora TaxaPautal aceite esses formatos. O ramo ELSE devolve texto/número de taxa como “Direito”, podendo imprimir uma taxa onde se espera um montante.
- O template fixa 2% e 14%, não aplica a selecção rg/sadc/ua, não usa ieq e não reutiliza os snapshots pautais de Mercadoria. Os serviços actuais de tributação têm outra base, configuração e arredondamento; o relatório pode divergir de valores calculados ou confirmados na aplicação.
- A ligação à pauta é feita por código sem pontos, em vez de pauta_aduaneira_id/snapshot. Alterações de códigos ou várias correspondências podem mudar/multiplicar linhas. Nenhuma duplicação real do catálogo foi alegada sem teste de dados.
- Não há guarda para fob_total nulo/zero no rateio, nem restrição explícita de empresa nas queries. A autorização PHP existe nos fluxos modernos, mas não corrige uma query que aponta para outra base.
- A resolução do ID e a verificação de ProcessoPolicy::print nos comandos modernos são aspectos positivos e devem ser preservados.

### Requisicao.jrxml e printCartaDiversa

- Linha 45: o parâmetro descricao é aberto e não fechado antes do parâmetro Logotipo. A leitura XML falha no encerramento do relatório (linha 648). Esse original não passa para uma etapa de compilação válida.
- O PHP fornece logotipo em minúsculas, mas o template declara e utiliza Logotipo. O nome é sensível à capitalização. A chamada também fornece apenas o valor da propriedade, sem a resolução de caminho absoluto usada na nota.
- Ambas as consultas fixam logigatedb. O subdataset bancário filtra EmpresaID recebido do resultado principal; isto não demonstra que se trata da mesma empresa activa validada no Laravel, quando a base é diferente.
- ps.created_at é apresentado como DataCambio: criação do processo não é prova da data de câmbio. O campo cif é calculado como et.guia_fiscal / ps.Cambio, não como o CIF da mercadoria. Sem uma definição comercial validada, esse nome é enganador; a divisão não tem guarda para câmbio zero.
- O método de impressão cria um débito na conta corrente **antes** de executar Jasper, sem transacção ou idempotência. Falha/repetição do pedido pode deixar ou repetir movimento sem documento gerado.
- A criação não informa empresa_id; a leitura posterior soma movimentos apenas por cliente_id, e o modelo ContaCorrente não tem BelongsToTenant/TenantScope. Clientes partilhados entre empresas tornam esse saldo especialmente sensível. Não foi criado movimento real nesta análise.
- saldoCliente vem do browser e influencia a opção FR/FT. Deve ser calculado no servidor a partir dos movimentos da empresa e submetido às permissões financeiras próprias; print, por si só, não deve autorizar alteração de saldo e facturação.
- Quando emitirComFatura é verdadeiro, o método chama emitirFatura, que não está definido no controller nem na hierarquia inspeccionada. Pode falhar depois do débito. Não se alterou a facturação para reparar esse caminho.
- numeroParaExtenso define uma função global aninhada que lê arrays globais, enquanto os arrays foram declarados localmente. A chamada com 123 reproduziu o erro de acesso a array nulo. Repetir a definição no mesmo processo PHP também exige revisão; esse segundo caso não foi executado para evitar encerramento fatal do ensaio.
- A normalização monetária transforma em float antes de validar; “texto” pode tornar-se zero, e formatos de milhares não são tratados de forma fiável. O parametro fobTotal é validado mas não é usado na geração observada.
- Requisicao.pdf é uma saída global fixa, com risco de colisão entre pedidos/empresas.

### facturas_geral.jrxml

- XML e XSD aprovados, mas sem consulta efectiva, sem campos de linhas e com um subdataset vazio.
- A banda detail contém texto fixo Artigo/Serviço, descrição, quantidade 1, preço 125, IVA 17.50 e total 124.50. Resumos e totais contêm 0.00. Não está ligado a sales_line nem aos totais reais da factura.
- ReportService fornece cabeçalho, mas não fornece linhas, impostos ou totais. Não passa email, embora o template o apresente. morada_cliente recebe CompanyName.
- As datas são extraídas de invoiceType com valores de 2024 como fallback, em vez de um contrato claro de datas do documento; são enviadas como strings dd-mm-yyyy a parâmetros java.util.Date.
- A origem “One Empty Record” é uma propriedade de desenho no Studio; a execução actual não configura explicitamente datasource vazio. O comportamento do CLI fica por verificar.
- Etiquetas Total (AKZ) e Total (USD/EUR) são fixas e não demonstram moeda/taxa do documento.
- Não foi localizada chamada a renderInvoiceReport. O template deve ser considerado maquete legada, e não reutilizado para emissão fiscal sem implementação e validação próprias.

## Segurança e operação do motor Jasper

### P1 — Comando externo sem escape de parâmetros

O PHPJasper instalado monta `key="value"` por concatenação em src/PHPJasper.php:142–146 e executa a string com exec, nas linhas 239–253. Um valor contendo aspas e separadores de shell sai do argumento. Foi construído um comando com um marcador REM inofensivo, **sem execute e sem credenciais**, confirmando que o separador fica sem escape. Campos como nomes de empresa/cliente chegam a esse comando pelas impressões activas.

Recomendação: encapsular execução em serviço que use argumentos estruturados, sem shell, com timeout e tratamento seguro de stdout/stderr. Não usar printOutput em produção nem apresentar comandos/credenciais ao utilizador.

### P1 — Origem dos dados e saídas globais

Não misturar a empresa autorizada numa ligação com queries fixadas noutra base. Preferir fornecer ao renderizador dados já autorizados e calculados pela aplicação; caso se mantenha JDBC, usar configuração efectiva, parâmetros vinculados, empresa explícita, exclusão de soft deletes onde aplicável e a mesma origem para cabeçalho e detalhe.

As rotas antigas gravam ficheiros globais em reports. Os comandos modernos melhoram o isolamento com storage/app/reports/processos/{id}, mas não criam nome único por execução. A existência de um ficheiro não prova que seja o resultado do pedido actual; erro, geração vazia e ficheiro antigo devem ser distinguidos.

### Ambiente e compatibilidade

- Controller passa no lint de PHP 8.5; os problemas encontrados não são solucionados apenas actualizando Laravel.
- geekcom/phpjasper 3.4.0 está instalado; o construtor tem tipo implicitamente nullable (`string $pathExecutable = null`), a rever para PHP moderno. Não se declarou falha geral de execução por esse aviso.
- Há também cossou/jasperphp em composer.lock, marcado como abandonado em favor de geekcom/phpjasper; não foram encontradas chamadas ao primeiro. Não instalar ou actualizar dependências automaticamente durante uma análise.
- JasperReports instalado: 6.7.0; fontes JRXML com comentários de 6.21.x. Três fontes passaram no XSD 6.7, uma falhou e uma é XML inválido. Compilação de expressões, desserialização e exportação continuam a exigir teste real.
- JasperStarter.exe existe. Java não foi encontrado no PATH, nem via JAVA_HOME válida. Isso impede certificar geração ponta a ponta neste ambiente sem configurar um runtime apropriado.
- O código nomeia com.mysql.cj.jdbc.Driver; o jar local mysql-connector-java-5.1.48 contém com.mysql.jdbc.Driver e não contém a classe cj indicada. É uma incompatibilidade de configuração a verificar no caminho efectivo do CLI, não um ClassNotFoundException já observado.
- As chamadas usam env() e jdbc_url sem os mesmos defaults das restantes opções; configuração em cache/variáveis ausentes pode seleccionar origem diferente ou URL inválida.
- Locale en e formatos de datas em inglês foram observados no PDF histórico. Devem ser alinhados com o público e com a apresentação inequívoca das datas.
- Há nota_despesa.jasper e facturas_geral.jasper, ambos serializados Java (AC ED 00 05). O código passa caminhos .jrxml; a presença dos binários não prova que estejam sincronizados ou compatíveis com o motor. Não foram alterados/desserializados.

## Inspecção visual do PDF existente

Foi renderizada a nota_despesa.pdf já guardada em reports: uma página A4. O documento histórico mostra “null/null” em marcas, “null” em qualificações, campos de referência/origem/destino vazios, valores longos sem agrupamento uniforme e rodapé/data em inglês. A hierarquia básica e a tabela são legíveis nesse exemplo de quatro mercadorias.

Não foram transcritos nomes/NIFs do documento para esta análise. Os zeros nas despesas **não foram classificados como erro**, pois não se confrontaram esses valores históricos com a base correspondente. Este PDF não valida o JRXML actual, não prova impressão multipágina e não certifica as fontes/layout nos restantes ambientes.

## Plano de reparação recomendado

1. Fechar autorização dos relatórios HTML; corrigir tipo canónico, datas e estado vazio, reutilizando filtros/consultas dos painéis quando adequado.
2. Proteger a execução externa e resolver a origem única dos dados e ficheiros únicos por pedido. Preservar TenantAccess/ProcessoPolicy dos comandos modernos.
3. Corrigir a estrutura da requisição e a tabela do extrato; validar todos os contratos de parâmetros, imagem, datas, nulos e precisão monetária.
4. Fazer os templates apresentarem os valores calculados pelos serviços canónicos; remover lógica tributária paralela e nomes comerciais sem significado validado. Não trocar taxas/regimes ou regras de facturação por suposição.
5. Tratar a requisição financeira como comando próprio, com autorização financeira, saldo do servidor, transacção/idempotência e estratégia para falha de geração. Não esconder esse efeito sob um simples “imprimir”.
6. Manter hello_world e a factura de demonstração fora dos percursos novos até substituição/validação. Reconciliar JRXML/Jasper compilado mediante build controlado.
7. Validar geração real com Java/driver/motor alinhados: nulos, zero, câmbio, ausência de mercadorias, centenas de linhas, concorrência, empresa externa, decimais pautais e concordância dos totais com a aplicação.

## Evidências e limites

- Cinco JRXML inspeccionados; quatro bem formados, um inválido. Três aprovados no XSD instalado e extrato rejeitado com componentes carregados.
- Seis queries analisadas em dois cenários (original e sem o prefixo fixo): doze EXPLAIN, sem leitura de linhas comerciais. hello_world falha em ambos; as restantes queries resolvem no ambiente local.
- Dez diagnósticos e 23 verificações aprovados numa base descartável: seis falhas do controller/relatórios HTML e quatro provas de contrato/cálculo/conversão por extenso. “Aprovados” significa confirmação dos comportamentos defeituosos esperados, não certificação de funcionamento correcto.
- Parâmetros da nota capturados com ProcessoJasperService falso; não houve execução de shell/Java, cobrança, débito ou emissão de facturas reais.
- XML/XSD/EXPLAIN não substituem compilação, preenchimento e inspecção de PDFs gerados pelo motor actual. Esses passos ficaram expressamente por verificar por falta de runtime Java configurado.
- Evidências técnicas em storage/app/relatorio-review: jrxml-metadata.json, xsd-results.json, query-results.json, complete-tests.txt e imagem do PDF histórico. Os templates/controllers originais e as bases da aplicação foram preservados.
