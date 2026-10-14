# Análise de RelatorioController

Data: 13/10/2026. Análise do código local, rotas, serviços e views associados. O controller e os serviços da aplicação não foram alterados nesta análise.

## Conclusão

O controller mantém um fluxo HTML de relatórios de Licenciamentos funcional apenas para alguns cenários, com falhas de autorização, selecção de tipo, limites temporais e resultados vazios. O método de geração Jasper de Processos é legado e não tem rota registada no estado inspeccionado. Não está preparado para ser activado tal como está.

Não foi encontrado erro de sintaxe no controller sob PHP 8.5.10. A herança de AuthenticatedController chama correctamente o construtor que regista autenticação e resolução da empresa activa. Isto não substitui autorização pela política do recurso.

## Achados por prioridade

### P1 — Relatórios acessíveis sem a permissão do recurso

`RelatorioController.php:38–61` não chama Gate/authorize/authorizeResource. As rotas `relatorio.visualizar` e `relatorio.licenciamento` exigem autenticação e empresa activa, mas os seus nomes não correspondem aos padrões de módulos de Licenciamentos em `config/navigation.php`. Um utilizador com empresa válida e sem `licenciamentos.view` recebe HTTP 200 com dados.

Recomendação: autorizar viewAny de Licenciamento nos dois métodos e aplicar aos relatórios o controlo de módulo existente, sem inventar uma autorização por simples visibilidade do menu.

### P2 — Tipo presente no caminho não é usado

`routes/web.php:186` define `/relatorios/licenciamento/{tipo}`, enquanto `RelatorioController.php:45` lê somente input/query. A chamada directa `/relatorios/licenciamento/periodo`, sem query `tipo`, apresenta o relatório de cliente. O formulário normalmente envia o tipo também na query, mascarando a falha; a query pode contradizer o caminho.

Recomendação: aceitar o parâmetro da rota como fonte canónica, validar a lista cliente/tipo/periodo/localidade e evitar duas fontes de decisão divergentes.

### P2 — Resultado vazio causa HTTP 500

`resources/views/relatorios/licenciamento.blade.php:8` executa `first()->toArray()` sem verificar se existe um primeiro registo. Uma empresa sem licenciamentos ou um período sem movimento quebra a página.

Recomendação: definir colunas por tipo de relatório e apresentar ausência de dados, em vez de deduzir as colunas obrigatoriamente do primeiro resultado.

### P2 — Datas ignoradas e último dia incompleto

`LicenciamentoReportService.php:17–63` aplica o período exclusivamente ao tipo periodo. Os tipos cliente/tipo/localidade continuam a contar todo o histórico, embora recebam datas e o formulário disponibilize esses campos. As datas ainda entram na chave de cache, gerando entradas diferentes para consultas equivalentes.

Em `LicenciamentoReportService.php:43`, whereBetween de created_at recebe datas sem hora. Um fim em 10/10 termina às 00:00 desse dia e exclui um registo criado às 12:00. A consulta agrupa correctamente por ano-mês, mas usa o timestamp armazenado sem a conversão para o dia civil de Angola.

Recomendação: aplicar filtros de forma uniforme, usar início inclusivo e início do dia seguinte exclusivo, convertidos ao fuso de armazenamento, e calcular percentagens sobre a mesma população filtrada.

### P2 — Join de clientes não restringe a empresa

`LicenciamentoReportService.php:23` faz join pelo ID do cliente, sem verificar a pertença à empresa proprietária do licenciamento. As linhas de licenciamentos são correctamente restringidas por empresa, mas uma referência inconsistente a um cliente externo expõe o nome desse cliente no agregado. O problema foi reproduzido com dados descartáveis; não se afirma que haja referências corrompidas na base real.

Recomendação: validar a pertença directa ou pelo pivot customers_empresas no join e agrupar pelo ID do cliente, além do nome. Agrupar apenas por CompanyName também funde clientes diferentes com nomes iguais.

### P2 — Entradas sem validação estruturada

`RelatorioController.php:45–53` não valida enum do tipo, formato das datas, ordem do intervalo ou tipos escalares. Carbon::createFromFormat é usado apenas no tipo periodo e não equivale à validação do pedido. Inputs inválidos podem produzir excepções/normalização inesperada; um intervalo invertido produz um conjunto vazio e encontra também a falha da view.

Recomendação: FormRequest ou DTO validado com date_format, sequência de datas, tipos escalares e limite razoável de intervalo. Nenhuma alegação de SQL injection foi feita: empresaId é tipado e convertido a inteiro na expressão existente.

### P2 latente — Jasper aponta para o esquema antigo e um ficheiro partilhado

`generateReport()` em `RelatorioController.php:27–35` não resolve um Processo da empresa nem verifica ProcessoPolicy::print. No estado actual não foi localizada uma rota ou chamada a esse método; portanto este não é apresentado como endpoint já explorável.

`ReportService.php:14–36` utiliza hello_world.jrxml e devolve sempre reports/hello_world.pdf. O template consulta explicitamente honga_despachantes.Processos/Importacao/Empresa e campos antigos como ProcessoID, CustomerID e FK_Empresa, incompatíveis com o modelo actual baseado em processos.id, customer_id e empresa_id. P_user é usado na apresentação; não restringe o acesso ao processo. A conexão é lida por env(), em vez da configuração efectiva, o que também exige revisão para ambientes com configuração em cache.

O nome fixo de saída pode fazer pedidos concorrentes substituírem o relatório uns dos outros se esse fluxo for activado e conseguir gerar documentos. Antes de activá-lo: autorizar processo e empresa, reconciliar o template, usar saída privada única por pedido e gerir erros/limpeza de forma explícita. Não alterar renderInvoiceReport ou regras de facturação por extensão deste escopo.

### P3 — Código morto e nomenclatura enganadora

JasperService é injectado e construído mas não é utilizado pelo controller; Pdf e SalesInvoice também não são usados. O mapa JRXML e as variáveis input/output em RelatorioLicenciamento não executam Jasper nem PDF. A pasta resources/reports referenciada não existe neste checkout, mas isso não explica as falhas da página HTML porque os ficheiros não são lidos.

O campo “Última Emissão” é MAX(created_at), ou seja, última criação de licenciamento, sem prova de emissão. Os relatórios não mostram data de actualização do cache, não têm paginação dos grupos e o controller analisado não disponibiliza download PDF/Excel/CSV de Licenciamentos.

Recomendação: remover dependências mortas e alinhar títulos com os eventos realmente registados. Reutilizar a arquitectura operacional e as bibliotecas já existentes para filtros e exportação, evitando dois motores analíticos com regras diferentes.

## Verificação executada

- `php -l app/Http/Controllers/RelatorioController.php`: aprovado em PHP 8.5.10.
- Seis diagnósticos, onze verificações, numa base MySQL descartável com esquema clonado sem dados comerciais: todos confirmaram os comportamentos defeituosos esperados.
- Os casos reproduzidos foram acesso sem permissão, tipo do caminho ignorado, HTTP 500 com resultado vazio, corte do último dia, datas ignoradas nos outros três tipos e join de cliente externo com referência inconsistente.
- Ficheiros de diagnóstico e resultado em storage/app/relatorio-review; não houve execução de Java/Jasper, geração de documentos comerciais, alteração do controller ou escrita na base da aplicação.

Não foi certificada a execução ponta a ponta do Jasper nem a concorrência de geração de PDF; esses riscos são sustentados pelo código e template inspeccionados. A validação de inputs e colisão de nomes foi analisada estaticamente, sem alegar reprodução adicional.
