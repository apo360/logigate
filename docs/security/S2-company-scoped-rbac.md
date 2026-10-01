# LOGIGATE — SECURITY S2 — Company-Scoped RBAC

Data: 2026-10-01. Gate: **S2 RUNTIME APPROVED: YES**.

S2 usa Spatie Teams com Empresa como team. Não há segundo motor RBAC: os modelos, pivots, atribuições, sincronizações e avaliações continuam a ser os do Spatie. `empresa_users` é autoridade de membership; a coluna `role` foi preservada como metadado legado e deixou de ser fonte de autorização. Não houve conversão dos grants legados para empresas.

## Relatório A–AT

| Secção | Evidência e resultado |
|---|---|
| A — Ambiente | Windows; PHP 8.5.10; Laravel 11.32.0; APP_ENV=testing e DB_DATABASE=logigate_testing confirmados antes da migração e dos testes. Nenhuma operação na produção. |
| B — Spatie | 6.10.1, confirmado em composer.lock, InstalledVersions e vendor instalado. Traits HasRoles/HasPermissions, Role, PermissionRegistrar e stub add_teams_fields foram inspeccionados. |
| C — Schema anterior | roles: PK id e unique(name,guard_name); permissions: PK id e unique(name,guard_name). Pivots de modelos: PK(grant_id,model_id,model_type), FK para role/permission e índice(model_id,model_type). role_has_permissions: PK(permission_id,role_id), ambas FKs. Snapshot completo no inventário anterior. |
| D — Memberships | empresa_users: PK id; unique(user_id,empresa_id); FKs para users/empresas com cascade. Uma membership observada: User 18 → Empresa 18. `conta` é um código já existente, não um contrato de ownership/grupo de empresas. |
| E — Criação anterior | CriarEmpresaAction persistia empresa sem membership/Admin; Fortify criava membership separada, preenchia pivot.role e atribuía Administrador global. Ambos os callers reais foram adaptados. |
| F — Administrador anterior | HasRoles sem Teams; Administrador, Gestor e permissões eram globais. Policies de utilizadores tratavam Administrador como global; sincronizações substituíam grants sem empresa. |
| G — Plataforma | AdminAuthController verifica hash de PIN e escreve flag de sessão dedicada; AdminMasterMiddleware exige essa flag. MasterRouteServiceProvider tem map de rotas comentado e routes/master.php não existe. Não há contrato explícito de superadmin de plataforma por role User: **NO EXPLICIT PLATFORM SUPER ADMIN CONTRACT**. O PIN continua separado; não foi ligado a grants empresariais. manageGlobalPermissions e o viewer global de logs negam acesso empresarial. |
| H — Pivot role | Valor observado: NULL. Caller de autorização roleNaEmpresa agora lê Spatie exclusivamente na empresa activa; Fortify deixou de preencher role. Relações ainda expõem a coluna como metadado. |
| I — Roles globais | 10 definições existentes: Administrador, Gestor, Gestor Financeiro, Gestor Auditor, Gestor Despachante, Transitário, Praticante, Operador, Customer Manager, customer-manager. Um grant legado: User 18 → role 10. Definições globais permanecem apenas como catálogo/template; não autorizam Users na empresa activa. |
| J — Direct permissions | 36 grants directos legados do User 18; 53 definições no catálogo. IDs completos nos snapshots, sem nomes/emails/passwords de utilizadores. A contagem inicial comunicada de 37 foi corrigida para 36 após comparação dos snapshots. |
| K — Classificação | SAFE_SINGLE_MEMBERSHIP=1; MAPPABLE_FROM_MEMBERSHIP_ROLE=0; AMBIGUOUS_MULTI_MEMBERSHIP=0; EXPLICIT_PLATFORM_AUTH=0; UNRESOLVED=0. Não existem casos multiempresa legados nesta base; os cenários multiempresa foram criados apenas dentro de transacções de teste. |
| L — Arquitectura alvo | Empresa activa validada pelo TenantContext → Spatie team → role/permissão efectiva da empresa. Membership, ownership do recurso e RBAC são verificações cumulativas. ClientePortal conserva o seu guard e relação empresa+customer. |
| M — Schema novo | roles recebe empresa_id nullable e unique(empresa_id,name,guard_name). model_has_roles/model_has_permissions recebem empresa_id e PK(empresa_id,grant_id,model_id,model_type), preservando FKs e índices anteriores. role_has_permissions mantém schema: role_id identifica uma definição empresarial distinta. permissions mantém catálogo partilhado. |
| N — Teams | permission.teams=true; team_foreign_key=empresa_id. Uma migração aditiva revista foi executada exclusivamente com --env=testing --force --path para este ficheiro. Não foi executado o stub gerado cegamente nem outra migração pendente. |
| O — Resolução | SetCompanyPermissionTeam no grupo web, com prioridade anterior a SubstituteBindings. Origem HTTP: apenas TenantContext::empresaId(), nunca request empresa_id. HasCompanyRoles revalida contexto antes de helpers/relações, incluindo leituras fora do middleware. |
| P — Troca | TenantContext::setEmpresa/clear actualiza imediatamente o team. Mudança descarrega roles/permissions e wildcard index; cada User também verifica a empresa das suas relações carregadas. A→B funciona sem novo login. |
| Q — Roles | Queries e CRUD limitados à empresa activa; CRUD exige Administrador dessa empresa. Só nomes do catálogo existente; nenhuma criação de nomes arbitrários. IDs estrangeiros negados. Administrador não pode ser eliminado nem renomeado. Atribuição resolve objectos Role da empresa, evitando resolução por nome global. |
| R — Permissões | Catálogo partilhado; grants empresariais. Atribuição aceita só permissões efectivas do actor na empresa activa. permissions.manage, menus.manage e system.configure não são capacidades atribuíveis por estas acções empresariais. CRUD de definições globais permanece negado, incluindo o controller legado. |
| S — Admin multiempresa | O mesmo U possui Administrador em A e B; em A pode gerir A e não B; após selecção válida de B pode gerir B. Membership em C ou role global não dá autoridade automática sobre C. |
| T — Nova empresa | Actor existente exige Administrador activo e empresas.create, nome confirmado no catálogo. Criação inclui empresa, membership e Administrador da nova empresa na mesma transacção. Novo role recebe um snapshot das capacidades empresariais efectivas do criador; não recebe capacidades de plataforma. Contexto anterior é restaurado. |
| U — Novo/existente User | CriarUsuarioEmpresaAction cria novo User ou associa um existente por email sem substituir identidade/password existentes; grava membership e grants só em A. Se já é membro A, passa também pela policy manageUser. Controller/form permitem associação; grants B sobrevivem. |
| V — Policies | UsuarioEmpresaPolicy valida empresa activa, memberships de actor/target, anti-self e Administrador do target na mesma empresa activa. Gestor A pode gerir target Operador A mesmo que target seja Administrador B. EmpresaPolicy continua a validar empresa activa. |
| W — Actions | SincronizarRolesUsuarioAction e SincronizarPermissoesUsuarioAction autorizam actor/target e resolvem grants no contexto A antes de sync nativo Teams. Remoção de membership limpa apenas grants A para impedir revivê-los numa reassociação. Save conjunto de roles/permissões Livewire é transaccional. |
| X — HTTP/Livewire | Roles/Permissions controllers, UserController, EmpresaRequest e actions têm controlo no servidor. Payload empresa_id diferente da activa é 403; o endpoint explícito de selecção continua a validar membership pelo S1. Componentes de gestão recebem snapshot de empresa e modelos Locked. |
| Y — Cache | PermissionRegistrar mantém catálogo por IDs distintos; roles e permissões carregadas são limpas quando empresa muda; wildcard index invalidado. Cache de menus já inclui User+Empresa e é invalidado para o contexto afectado. Console/jobs dispõem de CompanyRbac::within(id, callback), sem fallback para primeira empresa. Jobs não foram redesenhados. |
| Z — Legado | Pivots existentes recebem marcador 0, nunca uma empresa válida. Isso é preservação de schema, não migração de autoridade para uma membership. roles legadas ficam empresa_id=NULL e são excluídas da relação empresarial do User. Nenhum grant legado foi convertido, apagado ou atribuído à empresa 18. |
| AA — Admin A/B | PASS: manageUser/Integrations e sincronizações para B são negadas enquanto A está activa; selecção B dá autoridade apenas B. |
| AB — Admin/Gestor | PASS: troca A→B remove Administrador e activa Gestor. Relações carregadas previamente não mantêm autoridade A. |
| AC — Gestor/Operador | PASS: Gestor A com users.update pode gerir A; Operador B não pode gerir B. |
| AD — Mesmo nome | PASS: Gestor A tem processos.update/view e Gestor B só view; em B update é negado. |
| AE — Isolamento de role | PASS: alterar permissões de Gestor A preserva Gestor B; role B não pode ser editado por action/HTTP em A. |
| AF — Direct permissions | PASS: users.update direct em A não funciona em B; permission ID que actor só possui B é negado em A. |
| AG — Troca | PASS: A→B→A no mesmo login altera decisões e restaura grants correspondentes; S1.1 testa POST real de selecção com CSRF. |
| AH — Criação empresa | PASS: empresa+membership+Administrador empresarial; falha controlada durante criação do role faz rollback de todos. POST /register real do Fortify cria só o Admin empresarial inicial e conserva subscrição pendente. |
| AI — Criação User | PASS: novo User só tem membership/grants A; User existente só B é associado a A sem alterar B/password; tentativa de Gestor demover Admin A por associação é negada. |
| AJ — Target A/B | PASS: comparação exacta dos tuples B antes/depois de syncRoles e syncPermissions em A; ao mudar para B o target conserva Operador e processos.view, sem processos.update de A. |
| AK — Foreign User | PASS: action e GET HTTP de formulário negam target que não pertence A. |
| AL — Foreign role | PASS: ID role B negado para atribuição A; PUT role B negado antes de validação. |
| AM — Payload empresa_id | PASS: POST de atribuição com empresa_id B e contexto A retorna 403. IDs de permissions passam por autoridade empresarial mesmo com catálogo comum. |
| AN — Sem contexto | PASS: team NULL, helpers negam roles/permissões; getAllPermissions vazio; management gates negados. Não existe fallback para primeira membership. |
| AO — Grants globais | PASS: Administrador e direct permission legados em marker 0 não autorizam operações A nem ausência de contexto. Desconhecimento de permission no ProcessoPolicy nega. |
| AP — Livewire | PASS: snapshot A antigo após troca B é 403; componente B novo lê role B e não Administrador A. Componentes reais de gestão usam o mesmo trait. |
| AQ — S1/S1.1 | PASS: suites completas de ActiveTenantContext, S11RuntimeGate, ProcessoTenantIsolation, TenantIsolation e LicenciamentoTenantIsolation. Ownership dos recursos continua independente de RBAC. |
| AR — Portal | PASS: ClientePortalSecurity e S11 runtime com guard/login real, empresa+customer fixos e rejeição de filhos/documentos estrangeiros. ClientePortal não usa HasRoles/HasCompanyRoles. Sem alteração de negócio Portal nesta fase. |
| AS — Estática | php -l aprovado nos 115 ficheiros PHP/Blade modificados/novos acumulados; git diff --check aprovado. Tests: 41 runtime/352 assertions e 47 unitários/297 assertions; total 88/649. Deprecations pré-existentes de vendor, sem erros/failures/skips nos runs finais. |
| AT — Git | HEAD bbb92244068ee5cb0237166a1fc4f1e4b2500bc7 preservado. Staging vazio. Sem commit/push. Alterações locais S1/S1.1 preservadas; S2 continua local para revisão. |

## Compatibilidade e limites revistos

- O stub Spatie usa default team 1. Isso atribuiria grants legados implicitamente a uma empresa; a migração S2 usa 0 como marcador não seleccionável. Não adiciona FK de team, tal como o schema Teams nativo; mantém as FKs existentes de grants.
- Spatie 6.10.1 Role::create considera uma definição global do mesmo nome um conflito. As novas definições empresariais usam Role::query()->create (persistência Eloquent no mesmo modelo Spatie), com unique empresarial no banco. Avaliação e sincronização continuam nativas Teams; nenhum vendor foi alterado. Esta solução foi verificada em runtime com dois Gestor de permissões diferentes.
- Schema tem DDL MySQL não transaccional. down recusa colapsar grants de empresas, pois poderia produzir colisões/perda de autoridade. Rollback/deployment de produção precisa de revisão própria; não foi executado nem solicitado nesta fase.
- O demo mantém exactamente os tuples de membership/roles/36 direct permissions, is_active=1 e is_blocked=0. Login e dashboard passam. Os grants globais preservados já não conferem direitos empresariais; isso é esperado até S2.1 aprovada. Só há um User nesta base isolada, logo o inventário não prova ausência de ambiguidades noutra base.
- Endurecimento mínimo antecipado de S3: ProcessoPolicy deixa de aceitar capability inexistente/exception; MercadoriaTenantAccessService nega permission solicitada inexistente ou catálogo indisponível. Operações sem permission solicitada conservam contrato actual. Não houve revisão ampla de policies S3.
- Helpers empresariais em Exportador passam a receber apenas grants activos pelo User comum; os ramos existentes com nomes update/delete-global-exportador são classificados UNKNOWN/PLATFORM sem definição no catálogo observado. Não foram redesenhados. PIN/flag de plataforma continua separado, sem novo role master/owner.
- ASYCUDA, AppyPay, Hongayetu, SAFT, billing, subscriptions e storage/S3 não sofreram alteração de regras/formato/integrações em S2. A criação de pasta continua afterCommit; testes transaccionais não efectuam chamadas S3. As alterações nesses domínios já presentes no working tree pertencem a S1/S1.1.
- Runtime significa requests pelo kernel HTTP Laravel/Fortify e mecanismos reais Livewire. Não foi feito walkthrough manual num browser nem envio de emails externos. Notificações usam mail array nos testes; downloads S1.1 assinam por fake local, sem aceder a S3.

## Inventário de callers

O ficheiro [S2-rbac-callers.txt](C:/Users/USER/Projetos/logigate/docs/security/S2-rbac-callers.txt) lista os callers auditados. Classificação:

| Callers | Classe | Tratamento |
|---|---|---|
| User hasRole/hasAnyRole/hasPermissionTo/can/getRoleNames/getAllPermissions/roles/permissions/roleNaEmpresa | EMPRESA | Contexto activo e relações Teams; roles globais excluídas. |
| UsuarioEmpresaPolicy, EmpresaPolicy, ProcessoPolicy, LicenciamentoPolicy, DocumentoPolicy, MercadoriaTenantAccessService | EMPRESA | Ownership/contexto cumulativo com RBAC empresarial. Correções mínimas de fail-open descritas acima. |
| Actions/Queries de Usuarios e CriarEmpresaAction; UserController, Roles, UserRoleController; componentes EmpresaUser* | EMPRESA | Autorização de actor/target, IDs locais e sincronização da empresa activa. |
| ActorContext, RoleMiddleware, CheckPermission, CheckPasswordChanged; EventServiceProvider/FortifyServiceProvider | EMPRESA/telemetria | Leituras do User comum agora limitadas ao contexto válido. Eventos antes de selecção não classificam User por role global. |
| CustomerTenantAccessService aliases admin/super-admin/Administrador/CEO | EMPRESA | Não bypassam ownership; hasRole comum é limitado à empresa activa. Não existem grants para aliases inventados no catálogo observado. |
| viewLogs, manageGlobalPermissions e controllers de catálogo Permissions | UNKNOWN/PLATFORM | Sem contrato User global explícito: DENY. Não são poderes do Administrador empresarial. |
| ExportadorController/DeleteExportadorAction capacidades *-global-exportador | UNKNOWN/PLATFORM | Sem definição observada e sem atribuição permitida por catálogo empresarial. Redesign fica para revisão específica/S3. |
| AdminAuthController/AdminMasterMiddleware/MasterRouteServiceProvider | PLATAFORMA | PIN verificado/flag separada; não usa roles empresariais. Mapeamento de rotas master inactivo não foi inventado. |
| ClientePortal e policies Portal | PORTAL separado | Credencial empresa+customer; sem Teams. |

Artefactos: [snapshot anterior](C:/Users/USER/Projetos/logigate/docs/security/S2-legacy-inventory.json), [snapshot após schema](C:/Users/USER/Projetos/logigate/docs/security/S2-post-schema-inventory.json), [inventário readonly reutilizável](C:/Users/USER/Projetos/logigate/tools/security-s2-inventory.php), [suite S2](C:/Users/USER/Projetos/logigate/tests/Feature/CompanyScopedRbacTest.php).

## Validação reproduzível

Após confirmar a base isolada, a migração revista foi aplicada uma única vez:

```text
C:\php\php.exe artisan migrate --env=testing --force --path=database/migrations/2026_10_01_000001_enable_company_permission_teams.php
```

Suite runtime: tests/run-isolated.php com CompanyScopedRbacTest, ActiveTenantContextTest, S11RuntimeGateTest, Processo/ProcessoTenantIsolationTest, TenantIsolationTest, Licenciamento/LicenciamentoTenantIsolationTest e Portal/ClientePortalSecurityTest. Credenciais do demo injectadas exclusivamente no ambiente do processo, não guardadas em ficheiros/relatório. Todas as fixtures são transaccionais, sem RefreshDatabase, seed ou reset.

Suite unitária: tests/run-isolated.php com Unit/ActiveTenantContextTest.php, Unit/Billing, Unit/Integrations e Unit/ExampleTest.php.

## Gate final

| Pergunta | Resposta |
|---|---|
| SPATIE VERSION VERIFIED? | YES |
| COMPANY-SCOPED RBAC IMPLEMENTED? | YES |
| ACTIVE SPATIE TEAM == ACTIVE EMPRESA? | YES |
| ADMINISTRADOR IS COMPANY-SCOPED? | YES |
| ADMINISTRADOR CAN MANAGE MULTIPLE OWN COMPANIES? | YES |
| ADMINISTRADOR A AUTOMATICALLY ADMINISTERS UNRELATED B? | NO |
| NEW COMPANY CREATOR BECOMES ITS ADMIN? | YES |
| COMPANY CREATION IS ATOMIC? | YES |
| GESTOR A LEAKS TO B? | NO |
| ROLE A LEAKS TO B? | NO |
| DIRECT PERMISSION A LEAKS TO B? | NO |
| SAME ROLE NAME CAN HAVE ISOLATED COMPANY GRANTS? | YES |
| A→B SWITCH CLEARS RBAC STATE? | YES |
| TARGET USER B GRANTS SURVIVE MANAGEMENT FROM A? | YES |
| FOREIGN USER MANAGEMENT DENIED? | YES |
| FOREIGN ROLE ASSIGNMENT DENIED? | YES |
| EMPRESA_ID PAYLOAD TAMPERING DENIED? | YES |
| NO ACTIVE EMPRESA FAILS CLOSED? | YES |
| PLATFORM ADMIN SEPARATED FROM EMPRESA ADMIN? | NO PLATFORM ADMIN CONTRACT FOUND — PIN/flag independente preservado; sem role User de plataforma inventado |
| EMPRESA_USERS.ROLE STILL AN AUTHORIZATION SOURCE? | NO |
| LEGACY GLOBAL GRANTS AUTO-MIGRATED? | NO |
| AMBIGUOUS LEGACY GRANTS IDENTIFIED? | YES — inventário classificado; 0 casos ambíguos nesta base isolada |
| CUSTOMER PORTAL UNAFFECTED? | YES |
| S1 TENANT ISOLATION STILL PASSES? | YES |
| S2 RUNTIME APPROVED? | YES |
| READY FOR S2.1 LEGACY GRANT MIGRATION? | YES — preparado para revisão explícita; nenhuma conversão iniciada |
| READY FOR S3 POLICY HARDENING? | YES — fora do escopo executado |

Trabalho interrompido no gate S2 por conclusão do escopo. S2.1 e S3 não foram iniciadas.
