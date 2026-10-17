# Fluxo de exportação CRUD — 17/10/2026

## Utilização

Processos → Exportação abre a escolha entre exportação geral e exportação CRUD (petróleo bruto). O menu existente é reutilizado: a migration altera apenas o destino e o slug, preservando ID, posição, módulo e permissões. O cadastro Exportadores continua em Gestão de Clientes.

| Percurso | Rota |
| --- | --- |
| Escolha do fluxo | `processos.exportacao`, `/processos/exportacao` |
| Exportação geral | `processos.create?regime=exportacao` |
| Criação CRUD | `processos.exportacao-crude.create`, `/processos/exportacao-crude/create` |
| Edição de qualquer processo | `processos.edit`; o componente é escolhido pela natureza gravada |

## Classificação e responsabilidades

`processos.natureza_operacao` guarda `geral` ou `exportacao_crude` para novos processos. Os registos anteriores ficam com `null`, sem inferência por regime, descrição, RSM ou número de barris. A edição comum não permite mudar essa classificação. Dados de exportação antigos continuam acessíveis no formulário de edição, com indicação de que a natureza está por classificar.

`TipoProcesso` continua a guardar o ID de `regiao_aduaneiras`. Os padrões são IM + código 4 (importação definitiva) e EX + código 1 (exportação definitiva). O formulário CRUD lista apenas regimes de exportação/reexportação do catálogo: EX, EXS e EXV, códigos 1, 2 e 3. Os códigos 11/21 do licenciamento deixaram de controlar o formulário de processos.

`ExportacaoCrudeCreate` e `ExportacaoCrudeEdit` especializam os componentes comuns. Os partials de formulário e de campos CRUD evitam duplicar o HTML. `CriarExportacaoCrudeAction` define a natureza no servidor e delega em `CriarProcessoAction`; a atualização específica delega em `AtualizarProcessoAction`. A numeração, empresa, autorização, transação, regras de estados e pastas existentes são reutilizadas.

`ProcessoNaturezaService` valida a natureza e as regras CRUD também nas ações comuns, incluindo chamadas por outros pontos de entrada. O formulário geral rejeita campos específicos de petróleo. A guia usa o nome canónico `guia_exportacao` na persistência; a leitura do alias antigo `guia_exploracao` continua suportada pelo DTO de criação.

## Regras de abertura

Documentos, RSM, carregamento e quantidade podem ser completados ao longo da preparação do processo. A abertura não exige documentos que ainda não estejam disponíveis. Datas de carregamento informadas devem ser iguais ou posteriores à abertura; valores e deslocações não podem ser negativos.

A coluna existente `quantidade_barris` é inteira. O fluxo mantém esse contrato, preserva zero e rejeita frações antes da conversão do DTO. Barris fracionários requerem uma futura alteração coordenada do schema e contratos; não são arredondados ou truncados silenciosamente. Os cálculos de FOB/CIF existentes são preservados, sem inferir o FOB a partir de barris e preço.

## Migrations e ambiente local

- `2026_10_17_100000_add_natureza_operacao_to_processos.php`: coluna nullable e índice; nenhum backfill.
- `2026_10_17_100001_link_existing_exportacao_menu.php`: ligação idempotente do menu existente à escolha de fluxos.

Aplicadas na base descartável `logigate_testing_v1_a76b0c1a31` e na base local confirmada `logigate_testing`. O script verifica ligação, ambiente e fingerprint dos dados anteriores dos processos; verifica também que os IDs dos menus permanecem iguais. O menu Exportação mantém ID 4. Nenhuma migration foi executada num servidor remoto.

```powershell
php scripts/apply-processo-crude-local.php --sandbox
php scripts/apply-processo-crude-local.php
```

Para outro ambiente, publicar código/assets e aplicar exclusivamente as duas migrations pelo procedimento de deploy desse ambiente. Antes de remover a coluna numa reversão, preservar as classificações já gravadas. O script local recusa outros ambientes e bases.

## Verificação

- Bateria focada: 52 testes, 409 assertions, todos aprovados. Inclui 15 testes específicos de CRUD e regressões de criação, edição, despesas, ciclo de vida e DTOs.
- Módulo Processo completo + unitários: 65 de 66 aprovados, 493 assertions. Permanece a falha já observada em `ProcessoTenantIsolationTest::test_tenant_cannot_access_or_operate_on_another_tenant_process`: espera 404 e recebe 403 na linha 28. A autorização mantém o acesso bloqueado.
- Browser com HTML e JavaScript Livewire reais: escolha → criação CRUD, edição CRUD, envio de quantidade/RSM/guia, desativação e reativação de botões, 320/390/768/1440 px, sem overflow e sem erros JavaScript. A fronteira HTTP de gravação é simulada neste ensaio; persistência real é verificada nos testes PHP na base descartável, em transações.
- `npm run build`: aprovado. PHP syntax e `git diff --check`: aprovados.

```powershell
php tests/run-isolated.php --v1-sandbox tests/Feature/Processo/ExportacaoCrudeTest.php tests/Feature/Processo/ProcessoLivewireTest.php tests/Feature/Processo/ProcessoLifecycleAuditTest.php tests/Feature/Processo/DespesasLivewireTest.php tests/Unit/Processo

$env:PROCESSO_CRUDE_BROWSER_FIXTURE = '1'
php tests/run-isolated.php --v1-sandbox tests/Feature/Processo/ExportacaoCrudeTest.php --filter test_pages_route
Remove-Item Env:PROCESSO_CRUDE_BROWSER_FIXTURE
# Configurar PLAYWRIGHT_MODULE e LANDING_BROWSER conforme o runtime local.
node scripts/verify-processo-crude.cjs
```

Evidências locais: `storage/logs/crude-combined.xml`, `storage/logs/crude-full-processo.xml` e `storage/app/processo-crude-qa/`. São artefactos locais e não garantem retenção no Git.
