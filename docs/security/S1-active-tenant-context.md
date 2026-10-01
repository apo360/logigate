# LOGIGATE — SECURITY S1 — Active Tenant Context

Implementação local em 2026-09-30. S1 termina aqui; S2 não foi iniciada.

## A. Git State

Antes: working tree limpa; HEAD `bbb92244068ee5cb0237166a1fc4f1e4b2500bc7`.
Depois: alterações locais de S1, sem stage, commit ou push; HEAD permanece igual.
Não havia alterações preexistentes a preservar. `.env` não foi alterado.

## B. Source of Truth Antes

`TenantContext` usava a primeira membership (`value('empresas.id')`).
`User::empresaAtiva()` usava sessão com fallback para a primeira empresa.
Services/controllers/Livewire misturavam `user->empresa_id`, sessões alternativas,
primeira membership e união de memberships. Estas fontes podiam discordar entre si.

## C. Source of Truth Depois

`session('empresa_id')` é a única seleção operacional SaaS. Só é válida com um
`App\Models\User` autenticado e membership existente em `empresa_users`.
Contexto ausente, inválido, revogado ou actor diferente do autenticado retorna `null`.
Não existe fallback operacional. Listagem de memberships permanece separada.

## D. TenantContext Changes

API: `empresaId(?User)`, `empresa(?User)`, `setEmpresa(User, Empresa)`, `clear()` e
`userBelongsToEmpresa(User, int)`. ID positivo e membership são verificados no servidor.
Não há cache estática de actor/empresa; alterações de sessão e membership são reavaliadas.
Troca inválida não modifica a sessão. Troca válida regenera a sessão/token CSRF e limpa
as chaves alternativas e as relações carregadas `empresas`, `roles`, `permissions`.
O cache global de Spatie continua global: não foi tratado como RBAC por empresa.

## E. User::empresaAtiva Changes

Delega para `TenantContext::empresa($this)`. A identidade do actor é verificada;
invocar este método num outro User não lhe empresta o contexto do User autenticado.

## F. Empresa Switch Flow

GET `/empresa-contexto`: lista apenas memberships do User autenticado.
POST `/empresa-contexto`: valida o ID e a membership antes de trocar; integra middleware
web/CSRF, `auth:sanctum` e autenticação de sessão Jetstream. Redirecionamento fixo para
`dashboard`, sem aceitar URL fornecida pelo cliente. Formulário mínimo e links nos menus.
Seleção e logout ficam acessíveis sem empresa ativa; restantes rotas do grupo SaaS
exigem contexto válido via `empresa.active`.

Login/registo usam uma Action explícita: preservam seleção válida; sem contexto válido,
selecionam automaticamente apenas quando existe exatamente uma membership. Zero ou
várias memberships levam ao formulário. A criação original de empresa no registo foi preservada.

## G. TenantScope Changes

`TenantScope` já consumia `TenantContext`; não foi editado. Em HTTP SaaS fica agora
limitado à empresa selecionada; contexto ausente impõe `1 = 0`.
O bypass existente `runningInConsole()` foi preservado, tal como a estratégia específica
de cada modelo e a verificação de existência da coluna. Esse bypass não é uma autorização.
Jobs/console continuam a precisar de ownership/filtros explícitos fornecidos pelos seus fluxos.

`BelongsToTenant::creating` foi reforçado somente para HTTP com actor SaaS: exige contexto,
atribui ownership ausente e rejeita ownership de outra empresa. Console continua sem mudança;
criação anónima de registo e actor Portal mantêm os caminhos separados existentes.
Este hook não substitui autorização de endpoints, nem protege updates; isso permanece para S5.

## H. Route Binding Changes

`customer`, `processo`, `licenciamento`, `produto`, `subscricao`: ID central validado;
contexto inválido/ausente dá 404. Recursos operacionais exigem empresa ativa.
Customer admite identidade partilhada por ownership direto ou pivot da empresa ativa.
Binding `empresa` continua membership-based para seleção; policy operacional verifica o contexto.

## I. Tenant Service Changes

Processo/Licenciamento delegam resolução ao contexto central e preservam filtros explícitos.
Customer devolve somente o ID ativo em `empresaIds()` e exige ownership direto/pivot ativo
em `canAccess()`. Administrador não amplia essa dimensão tenant. `isAdmin()` e o catálogo
de permissões continuam para as fases seguintes.
FileAccess/restore/upload SaaS de documentos usam empresa ativa; upload de Customer
partilhado utiliza a empresa operacional selecionada, sem escolher outra pelo DTO.
Fallback da nota de despesa e DTO/serviço de Customer/Produto foram centralizados.

## J. Controller Changes

Resolvers operacionais de Arquivo, AuthenticatedController, Processo, Iban, Migração,
CustomerAvenca, BillingPlan, ModuleSubscription, Pagamento e Otp usam TenantContext.
Show de subscrição legado com parâmetro Empresa exige igualdade com a empresa ativa.
Resolução de empresa em relatórios/XML foi centralizada sem alterar mapping/estrutura ASYCUDA.
Nenhuma regra comercial de facturação, subscrições/pagamentos ou integração foi reformada.

## K. Livewire Changes

Resolvers de Customers, Dashboard, Arquivo, Empresa, Onboarding, Menu, Subscription,
Checkout, forms rápidos, Licenciamento e tables foram centralizados.
`RequiresActiveEmpresa` guarda um snapshot com atributo `Locked` e rejeita hidratação
de componentes alterados quando o contexto mudou ou deixou de existir. É necessário
recarregar a página após trocar empresa noutra aba; componentes antigos devolvem 403.
IDs empresariais persistidos em ContaCorrente/Avencas/Checkout/SubscriptionWizard são Locked
e verificados. O render de CustomerShow exige associação à empresa ativa.
Cache de menu inclui User e empresa; caches de dashboard já incluíam empresa.
Dashboard/layouts/vistas de arquivos deixaram de apresentar a primeira membership.

## L. Policy Tenant-Only Changes

DocumentoPolicy exige empresa ativa em operações SaaS, mantendo `arquivo.*` intactas.
ProdutoPolicy usa o ID central, sem criar catálogo de permissões.
EmpresaPolicy distingue `select` (membership) de `view/update/delete` (empresa ativa).
Checks tenant em gestão de utilizadores/permissões da empresa e integração exigem contexto ativo;
as regras de roles/permissões existentes permanecem. Não houve Spatie Teams ou alteração de schema RBAC.

## M. Portal Compatibility

Guards `cliente_portal`/`cliente`, `cliente_portal_empresa_id`, controllers/middleware Portal
e upload Portal não foram alterados. ClientePortal não é aceite como actor SaaS.
Métodos Portal de DocumentoPolicy continuam a exigir o seu próprio par empresa/customer
e visibilidade; esta decisão foi validada em teste unitário sem sessão SaaS válida.

Limitação existente: relações de Customer/Processo/Licenciamento no Portal usam modelos com
TenantScope SaaS. Sem actor SaaS/contexto, esse scope pode filtrar resultados do Portal.
Por exemplo, `ClientePortalProcessoController` usa `$customer->processos()` sem remover esse scope.
S1 não fundiu os contextos nem introduziu um bypass global. Compatibilidade HTTP/DB do Portal
não está certificada: regressões integrais ficaram NOT RUN. Deve ser revista antes de aceitar S1.
S3PathBuilder conserva o fallback de ownership de Customer; SaaS upload fornece ID explícito.
O fallback separado do middleware Portal também foi preservado.

## N. Tests Created

`tests/Unit/ActiveTenantContextTest.php`: 14 testes sem DB, cobrindo contextos inválidos,
A↔B, C externo, actor mismatch, revogação, Customer partilhado/admin, policies de documentos,
Produto/Empresa, Portal, sessão, login único/múltiplo, snapshot Livewire, creating e
negação de bindings sem contexto válido.

`tests/Feature/ActiveTenantContextTest.php`: cinco testes com transação e guardas explícitas
de ambiente/base, sem migrations/seeds. Cobrem POST A↔B/rejeição C/redirecionamento seguro,
bindings dos cinco recursos, seleção Empresa por membership, contextos inválidos,
Customer partilhado e autenticação/CSRF.

## O. Tests Executed

Executados com `C:\php\php.exe` (PHP 8.5.10) e PHPUnit 11.4.3:

```text
php vendor/bin/phpunit tests/Unit/ActiveTenantContextTest.php tests/Unit/Billing tests/Unit/Integrations tests/Unit/ExampleTest.php
47 testes / 297 assertions / PASS / 1 deprecation vendor Sanctum

php vendor/bin/phpunit tests/Unit/Integracoes/IntegracoesDomainTest.php tests/Unit/FacturacaoIntegracao/HongayetuFacturacaoClientTest.php --stop-on-error
10 testes / 30 assertions / PASS / 33 deprecations vendor
```

Total das suites aprovadas: 57 testes, 327 assertions. Regressões executáveis incluem
AppyPay mapper/billing domain, ASYCUDA parser/mapper/financial policy, Mercadoria DTO,
integrações e cliente Hongayetu com HTTP fake. Deprecations não são falhas de assertion;
ficam registadas, sem alteração de vendor.

## P. Tests Not Executed

APP_ENV=testing e DB_DATABASE=logigate_testing foram confirmados em `.env` e phpunit.xml
antes da tentativa de integração. Também são verificados no setup antes de abrir conexão.
A tentativa `php vendor/bin/phpunit tests/Feature/ActiveTenantContextTest.php --stop-on-error`
falhou no início da transação por MySQL 1045 (autenticação recusada). Não chegou a fixtures
nem assertions funcionais. Os cinco cenários feature são NOT RUN, não aprovados.

Regressões HTTP/DB de Processo, Licenciamento, Mercadoria, Customer, ContaCorrente,
Avencas, Documento e Portal: NOT RUN pela indisponibilidade da base isolada.
Suites com RefreshDatabase não foram executadas; nenhum comando destrutivo, seed ou migration foi utilizado.
Testes antigos que dependem da primeira membership precisarão de sessão/actor explícitos,
em vez de reintroduzir fallback no código de produção.

Há ainda referências preexistentes em bootstrap a `routes/master.php` e `routes/test.php`
ausentes, e ausência de `app/Providers/LivewireServiceProvider.php`. São pendências de
bootstrap para revisão; não foram corrigidas no âmbito S1. Não se afirma que causaram
o erro de DB observado, nem que HTTP esteja validado.

## Q. Static Checks

`php -l`: PASS em todos os 72 ficheiros PHP/Blade alterados/criados.
`git diff --check`: PASS. Sem alteração de `.env`, migrations, vendor ou schemas RBAC.
Inventário em R inclui apenas alterações locais S1 e este relatório.

## R. Files Modified

- [app/Application/Arquivo/Actions/RestoreDocumentoAction.php](C:/Users/USER/Projetos/logigate/app/Application/Arquivo/Actions/RestoreDocumentoAction.php)
- [app/Application/Arquivo/Actions/UploadDocumentoAction.php](C:/Users/USER/Projetos/logigate/app/Application/Arquivo/Actions/UploadDocumentoAction.php)
- [app/Application/Arquivo/Policies/DocumentoPolicy.php](C:/Users/USER/Projetos/logigate/app/Application/Arquivo/Policies/DocumentoPolicy.php)
- [app/Application/Arquivo/Services/FileAccessService.php](C:/Users/USER/Projetos/logigate/app/Application/Arquivo/Services/FileAccessService.php)
- [app/Application/Customer/DTOs/CreateCustomerDTO.php](C:/Users/USER/Projetos/logigate/app/Application/Customer/DTOs/CreateCustomerDTO.php)
- [app/Application/Customer/Services/CustomerTenantAccessService.php](C:/Users/USER/Projetos/logigate/app/Application/Customer/Services/CustomerTenantAccessService.php)
- [app/Application/Empresa/Actions/EstabelecerEmpresaAposAutenticacaoAction.php](C:/Users/USER/Projetos/logigate/app/Application/Empresa/Actions/EstabelecerEmpresaAposAutenticacaoAction.php)
- [app/Application/Licenciamento/Services/LicenciamentoTenantAccessService.php](C:/Users/USER/Projetos/logigate/app/Application/Licenciamento/Services/LicenciamentoTenantAccessService.php)
- [app/Application/Processo/Actions/EmitirNotaDespesaProcessoAction.php](C:/Users/USER/Projetos/logigate/app/Application/Processo/Actions/EmitirNotaDespesaProcessoAction.php)
- [app/Application/Processo/Services/ProcessoTenantAccessService.php](C:/Users/USER/Projetos/logigate/app/Application/Processo/Services/ProcessoTenantAccessService.php)
- [app/Domains/Empresa/Policies/EmpresaPolicy.php](C:/Users/USER/Projetos/logigate/app/Domains/Empresa/Policies/EmpresaPolicy.php)
- [app/Domains/Usuarios/Policies/UsuarioEmpresaPolicy.php](C:/Users/USER/Projetos/logigate/app/Domains/Usuarios/Policies/UsuarioEmpresaPolicy.php)
- [app/Http/Controllers/ArquivoController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/ArquivoController.php)
- [app/Http/Controllers/AuthenticatedController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/AuthenticatedController.php)
- [app/Http/Controllers/BillingPlanController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/BillingPlanController.php)
- [app/Http/Controllers/CustomerAvencaController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/CustomerAvencaController.php)
- [app/Http/Controllers/EmpresaContextController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/EmpresaContextController.php)
- [app/Http/Controllers/IbanController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/IbanController.php)
- [app/Http/Controllers/MigracaoController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/MigracaoController.php)
- [app/Http/Controllers/ModuleSubscriptionController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/ModuleSubscriptionController.php)
- [app/Http/Controllers/OtpController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/OtpController.php)
- [app/Http/Controllers/PagamentoController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/PagamentoController.php)
- [app/Http/Controllers/ProcessoController.php](C:/Users/USER/Projetos/logigate/app/Http/Controllers/ProcessoController.php)
- [app/Http/Middleware/CheckSubscription.php](C:/Users/USER/Projetos/logigate/app/Http/Middleware/CheckSubscription.php)
- [app/Http/Middleware/EnsureActiveEmpresa.php](C:/Users/USER/Projetos/logigate/app/Http/Middleware/EnsureActiveEmpresa.php)
- [app/Http/Responses/LoginResponse.php](C:/Users/USER/Projetos/logigate/app/Http/Responses/LoginResponse.php)
- [app/Http/Responses/RegisterResponse.php](C:/Users/USER/Projetos/logigate/app/Http/Responses/RegisterResponse.php)
- [app/Livewire/Arquivo/ArquivoIndex.php](C:/Users/USER/Projetos/logigate/app/Livewire/Arquivo/ArquivoIndex.php)
- [app/Livewire/Arquivo/DocumentosManager.php](C:/Users/USER/Projetos/logigate/app/Livewire/Arquivo/DocumentosManager.php)
- [app/Livewire/CheckoutPayment.php](C:/Users/USER/Projetos/logigate/app/Livewire/CheckoutPayment.php)
- [app/Livewire/Concerns/RequiresActiveEmpresa.php](C:/Users/USER/Projetos/logigate/app/Livewire/Concerns/RequiresActiveEmpresa.php)
- [app/Livewire/Customers/Avencas.php](C:/Users/USER/Projetos/logigate/app/Livewire/Customers/Avencas.php)
- [app/Livewire/Customers/ContaCorrente.php](C:/Users/USER/Projetos/logigate/app/Livewire/Customers/ContaCorrente.php)
- [app/Livewire/Customers/CustomerActivityChart.php](C:/Users/USER/Projetos/logigate/app/Livewire/Customers/CustomerActivityChart.php)
- [app/Livewire/Customers/CustomerShow.php](C:/Users/USER/Projetos/logigate/app/Livewire/Customers/CustomerShow.php)
- [app/Livewire/Customers/Form.php](C:/Users/USER/Projetos/logigate/app/Livewire/Customers/Form.php)
- [app/Livewire/Dashboard/AlertasOperacionaisWidget.php](C:/Users/USER/Projetos/logigate/app/Livewire/Dashboard/AlertasOperacionaisWidget.php)
- [app/Livewire/Dashboard/DashboardKpis.php](C:/Users/USER/Projetos/logigate/app/Livewire/Dashboard/DashboardKpis.php)
- [app/Livewire/Dashboard/DireitosAduaneirosWidget.php](C:/Users/USER/Projetos/logigate/app/Livewire/Dashboard/DireitosAduaneirosWidget.php)
- [app/Livewire/Dashboard/FinanceChart.php](C:/Users/USER/Projetos/logigate/app/Livewire/Dashboard/FinanceChart.php)
- [app/Livewire/Dashboard/MercadoriasChart.php](C:/Users/USER/Projetos/logigate/app/Livewire/Dashboard/MercadoriasChart.php)
- [app/Livewire/Dashboard/PrevisaoReceitaWidget.php](C:/Users/USER/Projetos/logigate/app/Livewire/Dashboard/PrevisaoReceitaWidget.php)
- [app/Livewire/Dashboard/ProcessosChart.php](C:/Users/USER/Projetos/logigate/app/Livewire/Dashboard/ProcessosChart.php)
- [app/Livewire/Dashboard/TopClientesWidget.php](C:/Users/USER/Projetos/logigate/app/Livewire/Dashboard/TopClientesWidget.php)
- [app/Livewire/Empresa/EmpresaSubscricao.php](C:/Users/USER/Projetos/logigate/app/Livewire/Empresa/EmpresaSubscricao.php)
- [app/Livewire/Forms/ClienteQuickForm.php](C:/Users/USER/Projetos/logigate/app/Livewire/Forms/ClienteQuickForm.php)
- [app/Livewire/Forms/ExportadorQuickForm.php](C:/Users/USER/Projetos/logigate/app/Livewire/Forms/ExportadorQuickForm.php)
- [app/Livewire/Licenciamento/LiicenciamentoCreate.php](C:/Users/USER/Projetos/logigate/app/Livewire/Licenciamento/LiicenciamentoCreate.php)
- [app/Livewire/Licenciamento/LiicenciamentoEdit.php](C:/Users/USER/Projetos/logigate/app/Livewire/Licenciamento/LiicenciamentoEdit.php)
- [app/Livewire/MenuDinamico.php](C:/Users/USER/Projetos/logigate/app/Livewire/MenuDinamico.php)
- [app/Livewire/Onboarding/OnboardingWizard.php](C:/Users/USER/Projetos/logigate/app/Livewire/Onboarding/OnboardingWizard.php)
- [app/Livewire/SubscriptionWidget.php](C:/Users/USER/Projetos/logigate/app/Livewire/SubscriptionWidget.php)
- [app/Livewire/SubscriptionWizard.php](C:/Users/USER/Projetos/logigate/app/Livewire/SubscriptionWizard.php)
- [app/Livewire/Tables/ExportadorTable.php](C:/Users/USER/Projetos/logigate/app/Livewire/Tables/ExportadorTable.php)
- [app/Livewire/Tables/ServicosTable.php](C:/Users/USER/Projetos/logigate/app/Livewire/Tables/ServicosTable.php)
- [app/Models/Concerns/BelongsToTenant.php](C:/Users/USER/Projetos/logigate/app/Models/Concerns/BelongsToTenant.php)
- [app/Models/User.php](C:/Users/USER/Projetos/logigate/app/Models/User.php)
- [app/Policies/ProdutoPolicy.php](C:/Users/USER/Projetos/logigate/app/Policies/ProdutoPolicy.php)
- [app/Providers/AppServiceProvider.php](C:/Users/USER/Projetos/logigate/app/Providers/AppServiceProvider.php)
- [app/Services/ProdutoService.php](C:/Users/USER/Projetos/logigate/app/Services/ProdutoService.php)
- [app/Support/TenantContext.php](C:/Users/USER/Projetos/logigate/app/Support/TenantContext.php)
- [bootstrap/app.php](C:/Users/USER/Projetos/logigate/bootstrap/app.php)
- [docs/security/S1-active-tenant-context.md](C:/Users/USER/Projetos/logigate/docs/security/S1-active-tenant-context.md)
- [resources/views/arquivos/index.blade.php](C:/Users/USER/Projetos/logigate/resources/views/arquivos/index.blade.php)
- [resources/views/arquivos/show.blade.php](C:/Users/USER/Projetos/logigate/resources/views/arquivos/show.blade.php)
- [resources/views/arquivos/upload.blade.php](C:/Users/USER/Projetos/logigate/resources/views/arquivos/upload.blade.php)
- [resources/views/dashboard.blade.php](C:/Users/USER/Projetos/logigate/resources/views/dashboard.blade.php)
- [resources/views/empresa/selecionar.blade.php](C:/Users/USER/Projetos/logigate/resources/views/empresa/selecionar.blade.php)
- [resources/views/layouts/app.blade.php](C:/Users/USER/Projetos/logigate/resources/views/layouts/app.blade.php)
- [resources/views/layouts/partials/header.blade.php](C:/Users/USER/Projetos/logigate/resources/views/layouts/partials/header.blade.php)
- [routes/web.php](C:/Users/USER/Projetos/logigate/routes/web.php)
- [tests/Feature/ActiveTenantContextTest.php](C:/Users/USER/Projetos/logigate/tests/Feature/ActiveTenantContextTest.php)
- [tests/Unit/ActiveTenantContextTest.php](C:/Users/USER/Projetos/logigate/tests/Unit/ActiveTenantContextTest.php)

## S. Security Scenarios

| Cenário | Decisão tenant SaaS |
|---|---|
| U A+B / A active / resource A | Permitido na dimensão tenant; RBAC existente ainda se aplica |
| U A+B / A active / resource B | Negado |
| U A+B / B active / resource A | Negado |
| U A+B / B active / resource B | Permitido na dimensão tenant; RBAC existente ainda se aplica |
| invalid session empresa_id | Contexto null; bindings 404; policy nega; páginas exigem seleção |
| missing empresa_id | Contexto null, sem fallback; mesmos efeitos de negação/seleção |
| U A+B / session C sem membership | Negado; tentativa de troca preserva seleção anterior |
| Actor X diferente do User autenticado | Contexto null; não herda sessão de outro actor |
| Customer partilhado A+B | Identidade acessível no pivot ativo; ID operacional continua exclusivamente A ou B |
| Documento B de Customer partilhado / A ativa | Negado; partilha de Customer não partilha documento operacional |
| Componente montado em A / troca para B | Snapshot antigo rejeitado com 403 |

Estas decisões foram verificadas a nível unitário; respostas HTTP e bindings com dados
reais permanecem sem execução integrada. Criar novo componente após troca usa o contexto atual.

## T. Remaining S0 Findings

Continuam para fases seguintes: RBAC global; permissions globais; Processo fail-open;
Mercadoria fail-open; Exportador Policy; API NIF; Tarefas; ASYCUDA XML legado; logs globais;
blocked/inactive users. Permanecem também a revisão de relações/scopes do Portal,
ownership de updates e IDs filhos/payloads (S4/S5), bypass de console e coluna tenant ausente.
S1 não certifica isolamento de todo endpoint legado nem converte permissões globais em tenant RBAC.

## U. Ready for S2?

**NO.** Implementação S1 disponível para revisão, mas a aceitação integrada está bloqueada
pelo acesso à base isolada. É necessário executar/rever os cenários feature e regressões
HTTP/DB/Portal, além das referências de bootstrap ausentes. Não iniciar S2, activar Teams,
migrar roles/permissions ou fazer commit automaticamente.
