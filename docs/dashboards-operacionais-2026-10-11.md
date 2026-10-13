# Dashboards operacionais de Processos e Licenciamentos

Trabalho iniciado em 11/10/2026 e concluído em 12/10/2026. Branch de revisão: `codex/dashboards-operacionais`. Sem publicação em produção, dependências novas ou migrations na aplicação. As alterações anteriores de navegação/subscrições foram preservadas.

## Diagnóstico confirmado

O código anterior agregava modelos inteiros em memória, utilizava nomes incompatíveis com Processos (`valor_total`, `data_entrada`, `customer`, `tipoProcesso`), somava valores sem separar moedas, confundia datas de criação/entrada com duração de conclusão e agrupava parte das séries por mês sem ano. Licenciamentos apresentava estado da factura como estado operacional.

O esquema local é MySQL 8.0.46 com modo estrito; aplicação em PHP 8.5.10, Laravel 12.69.3 e Livewire 3.8.10. Datas de Processos são `DATE`; `created_at` de Licenciamentos é armazenado em UTC. Os filtros e fronteiras de Licenciamentos são convertidos para o mês/dia civil de Angola, sem alterar o fuso global da aplicação.

## Ficheiros e arquitectura

| Camada | Ficheiros principais | Responsabilidade |
|---|---|---|
| Filtros | `app/Services/Dashboard/OperationalFilters.php` | Períodos rápidos, intervalo validado até dez anos, dimensões e ordenação permitidas. |
| Consultas | `app/Services/Dashboard/OperationalAnalytics.php` | Agregações SQL, coortes, saídas, carteira, valores por moeda, cobertura, rankings e tabelas paginadas. |
| Integração | `DashboardQueryService.php`, `DashboardController.php` | Substituição apenas dos métodos analíticos de Processos/Licenciamentos e autorização pelas políticas existentes. |
| Critérios existentes | `EstadoLicenciamentoService.php`, `LicenciamentoProcessLink.php` | Expressões SQL de leitura equivalentes aos critérios operacionais, com restrição de empresa; operações existentes preservadas. |
| Interface | `dashboard_processos.blade.php`, `dashboard_licenciamento.blade.php`, `analytics/operational.blade.php` | Cinco áreas comuns, oito KPIs, filtros, definições, tabelas, cobertura e relatórios. |
| Gráficos | `resources/js/operational-analytics.js`, importação em `app.js` | Chart.js 2.9.4 local já instalado; inicialização e destruição durante navegação, legendas, unidades e alternativa tabular. |
| Relatórios | `OperationalReport.php`, `OperationalReportController.php`, `OperationalDashboardExport.php`, `reports/operational.blade.php`, duas rotas em `routes/web.php` | CSV/Excel/PDF usando bibliotecas existentes. Tabelas completas em CSV/Excel, PDF limitado explicitamente a 500 linhas. |
| Verificação | `tests/Feature/OperationalDashboardsTest.php`, `tests/run-operational-dashboards-isolated.php`, `scripts/verify-operational-dashboards.cjs`, `scripts/audit-operational-dashboards.php` | Casos analíticos, regressões, exportações e interface responsiva. |

## Definições dos indicadores

Todos os cálculos aplicam a empresa activa e as dimensões seleccionadas. A coorte de Processos usa `DataAbertura`; a de Licenciamentos usa a criação em Angola. Dimensões de referência são as actualmente registadas, sem reconstrução histórica de alterações de cliente/transporte/país.

| Indicador de Processos | Definição |
|---|---|
| Abertos | DataAbertura dentro do intervalo. |
| Finalizados | Estado Finalizado e DataFecho dentro do intervalo, independentemente da data de abertura. |
| Carteira à referência | Estados activos reconhecidos com abertura válida até à referência e sem fecho até essa data; inclui finalizados com fecho posterior. Não é limitada às aberturas do período. |
| A aguardar documentos | Parte da carteira com estado actual Aguardando documentos. |
| Idade média | Média de referência − abertura dos integrantes válidos da carteira; amostra identificada. |
| Tempo de conclusão | Média de fecho − abertura dos finalizados no período; exclui datas ausentes, inválidas ou invertidas, com amostra e número excluído. |
| Finalização da coorte | Finalizados com datas válidas até à referência ÷ abertos no intervalo; não usa saídas ÷ entradas de populações diferentes. |
| Clientes | Clientes distintos identificáveis da coorte e acessíveis na empresa. |
| Cancelamentos | Cancelados da coorte como estado observado; cancelamentos ocorridos no período ficam indisponíveis, pois não existe data validada do evento. |
| Valor declarado | ValorTotal agrupado pela moeda registada; não é receita do despachante. Valores negativos/ausentes não entram nos totais; sem moeda o montante agregado fica indisponível. |
| Peso | Total e média dos pesos não negativos, em kg, com amostra/coorte. Zero preenchido é válido, diferente de ausência. |

| Indicador de Licenciamentos | Definição |
|---|---|
| Criados | Criação UTC convertida para intervalo civil de Angola; não usa data de entrada da mercadoria. |
| Por licenciar | txt_gerado = 0. |
| Licenciado | txt_gerado = 1 e relação paga em proc_licen_sales da mesma empresa. |
| Em licenciamento | Restantes registos, segundo EstadoLicenciamentoService; o status_fatura do próprio licenciamento não substitui esse critério. |
| Associação | Vínculo canónico licenciamento_processos com processo existente, não eliminado, da mesma empresa. Percentagem sobre a coorte seleccionada; não é funil histórico. |
| Idade em aberto | Referência − criação local, na coorte ainda Por licenciar/Em licenciamento; não é duração de aprovação. |
| Clientes | Clientes distintos identificáveis e acessíveis da coorte. |
| FOB/frete/seguro/CIF | Totais e cobertura separados por moeda. Não há conversão nem total entre moedas. |
| Peso/volumes | Peso em kg e quantidade de volumes como contagem, com cobertura. Não se inventa medida em m³. |

Séries mensais usam ano-mês e preenchem com zero meses sem movimento. Os meses cortados pelo intervalo são marcados como parciais. Comparação de entradas: intervalo imediatamente anterior com exactamente o mesmo número de dias; base zero resulta em “sem base de comparação”. Não são calculadas medianas/percentis sem necessidade demonstrada nesta versão.

## Segurança, desempenho e exportações

As políticas viewAny são verificadas antes de apresentar ou exportar dados. Os detalhes continuam nas rotas existentes, com as suas políticas. As exportações têm nomes `processos.analytics-report` e `licenciamentos.analytics-report` para reutilizar a verificação existente de direitos de módulo. A empresa vem do contexto autenticado; o middleware existente rejeita empresa_id incompatível enviado pelo browser. Referências corrompidas a clientes de outra empresa são mostradas como indisponíveis, sem expor o nome.

As views não carregam modelos por linha nem fazem consultas. Indicadores e distribuições são agregados em SQL, rankings limitados a 10/20 grupos e tabelas paginadas em 15 linhas. Análises integrais de dimensões estão disponíveis na tabela paginada e no relatório específico. Exportações detalhadas usam consultas lazy em blocos de 500. Os menus de filtros carregam apenas valores distintos usados pela empresa, não todos os processos/licenciamentos.

Cache: 60 segundos, chave por empresa, painel, período e dimensões. Ordenação/paginação não mudam os totais. Painel e relatório usam as mesmas definições e o mesmo resumo em cache; a data de actualização está visível. Após expiração ou mudança de dados, uma nova emissão pode legitimamente reflectir outra actualização. O detalhe paginado/streaming é consultado no momento; não se declara um snapshot histórico imutável de cada registo durante alterações concorrentes.

CSV neutraliza texto que possa iniciar fórmulas, mesmo com espaços/controlos antes de =, +, - ou @. Excel grava strings explicitamente como texto. PDF usa Blade com escape e bibliotecas existentes. Relatórios incluem empresa, período, filtros, emissão, actualização, unidades, moedas, cobertura e definições. Não há emissão de facturas nem alterações de facturação, AppyPay, Hongayetu, SAFT-AO ou S3 global.

## Limitações comprovadas

- A base local contém estados legados como Em processamento, Submetido, Concluído e Suspenso que não constam de EstadoProcessoEnum. Não são adivinhados como Finalizado/activo; são preservados nas distribuições e assinalados na qualidade. É necessária uma decisão de negócio antes de qualquer normalização.
- A tabela licenciamento_processos está ausente na base local analisada. O painel e relatório mostram a associação como indisponível. A verificação de vínculos foi feita com essa estrutura criada **apenas na base descartável**, sem migration ou alteração na base da aplicação.
- Sem eventos estruturados de transição não há cancelamentos por data, duração de aprovação, funil histórico ou reconstrução integral de cancelamentos/licenciamentos antigos. A referência temporal e a observação actual são identificadas.
- Não existe SLA validado: os registos são antigos, não classificados como atrasados.
- A validação visual usa HTML Blade real com autenticação e dados descartáveis, servido por interceptação local no browser. Não foi feita autenticação manual numa sessão de produção. Recursos externos do layout, como avatares, ficam bloqueados nesse ensaio.
- Não foi executado benchmark representativo de uma base de produção com grande volume; a estrutura limita consultas e carregamento, mas os índices/EXPLAIN devem ser avaliados com volume real antes de propor migrations.

## Validação

Executar `php tests/run-operational-dashboards-isolated.php`. O runner verifica o destino, clona somente o esquema para uma base aleatória descartável e elimina somente essa base no final. HTTP externo é impedido e armazenamento é falso nos testes. Inclui os testes de navegação/subscrições anteriores e testes de políticas e ciclo de vida de Processos, além dos novos casos analíticos.

A verificação visual em Edge headless usa 1440 × 1000 e 390 × 1000, ambos os painéis, filtro personalizado, tabelas acessíveis e navegações repetidas. Verifica instâncias dos gráficos, ausência de erros JavaScript e transbordamento horizontal. Resultados e capturas ficam em `storage/app/dashboard-visual/`; logs em `storage/logs/operational-dashboard-*.txt`.

Os resultados finais de testes, compilação e verificação visual são registados na entrega após a última execução.
