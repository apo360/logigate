# Papéis, módulos e menu dinâmico

Avaliação em 10/10/2026. Leitura do código, migrações e esquema real da ligação local configurada. Nenhum registo de negócio foi alterado. As contagens descrevem esta base local, não necessariamente produção.

## Decisão recomendada

Os papéis existentes podem representar os perfis de utilizador da proposta. Não é necessário criar outra tabela de tipos de utilizador. `roles` define os papéis; `model_has_roles` é a associação polimórfica entre actor, papel e empresa, não um catálogo de grupos de navegação.

Manter duas dimensões: perfil funcional (Gestor Despachante, Operador, Gestor Financeiro, etc.) e área de trabalho (Operação aduaneira, Clientes e gestão, Administração). O perfil determina autorizações e pode determinar prioridades visuais; a área agrupa tarefas relacionadas. Um utilizador pode ter vários papéis e precisa de um único menu, sem itens repetidos.

Os grupos podem apresentar as responsabilidades do perfil, mas não devem ser implementados como uma cópia dos IDs ou nomes dos papéis em `menus.parent_id`. Esse campo já representa a hierarquia dos menus. Um `role_id` único em `menus` também seria insuficiente para vários papéis, permissões directas e papéis distintos por empresa.

## Estado confirmado

| Estrutura | Situação local | Responsabilidade |
|---|---|---|
| roles / model_has_roles | 12 papéis; 3 associações, 2 com empresa e 1 legada com empresa 0 ou ausente | Função do utilizador na empresa activa |
| permissions / role_has_permissions | 57 permissões e 311 associações | Acções autorizadas pelos papéis |
| modules | 8 módulos; hierarquia por parent_id | Catálogo funcional/comercial |
| menus | 33 registos; todos sem permission; já existe position | Navegação e hierarquia de itens |
| subscricoes | 2 registos, ambos com status activa | Direito comercial da empresa, plano e validade |
| plano_modulos | 8 associações, campos plano_id/modulo_id | Módulos incluídos no plano |
| activated_modules | 16 registos; todos com subscricao_id nulo | Activação de módulos por empresa; vínculo com subscrição incompleto |

Papéis presentes incluem Administrador, Gestor, Gestor Financeiro, Gestor Auditor, Gestor Despachante, Transitário, Praticante, Operador, Customer Manager e customer-manager. Os dois últimos nomes coexistem: confirmar equivalência funcional antes de consolidar, sem eliminar atribuições automaticamente.

`config/permission.php` activa teams com empresa_id. `HasCompanyRoles` e `CompanyRbac` resolvem papéis/permissões no âmbito da empresa activa. Reutilizar esses mecanismos; não consultar model_has_roles apenas por model_id nem tratar uma atribuição legada sem empresa como autoridade global.

## Diferenças que afectam o merge

1. **Menu e middleware usam fontes diferentes.** MenuDinamico obtém módulos por subscricoes activas e plano_modulos. O ramo de módulo específico de CheckSubscription usa activatedModules da subscrição e procura module.codigo. O menu não verifica active/data_expiracao de activated_modules. Portanto, activação individual e menu podem divergir.
2. **A activação principal não liga a subscrição.** ActivateSubscriptionAction chama ModuloAtivacaoService com empresa e plano. Esse serviço grava empresa/module/menu/active/activation_date, mas não subscricao_id nem data_expiracao. O esquema possui ambos os campos; ActivatedModule não os inclui em fillable. O método alternativo Subscricao::ativarModulos também não pode preencher correctamente esses campos via mass assignment com o modelo actual.
3. **Relação Module::planos divergente.** Refere plano_modulo e module_id. A base real tem plano_modulos e modulo_id; plano_modulo não existe. Plano::modulos e PlanoModulo usam o nome real.
4. **Campos esperados inexistentes.** O modelo Module menciona codigo, tipo, icone, order_priority e ativo, ausentes na tabela real. A condição de módulo específico no middleware não encontrará um código persistido neste esquema. Não significa que todos os acessos actuais falhem: esse ramo depende de receber um argumento de módulo.
5. **Fluxo alternativo de activação desactualizado.** Subscricao::ativarModulos consulta incluido e limite, ausentes em plano_modulos, e acede a menu_id, ausente em subscricoes. Plano::modulos também não carrega esses campos pivot. Esse método necessita de alinhamento antes de ser usado como fonte de activação.
6. **Papéis ainda não filtram o conteúdo dinâmico na prática.** O filtro usa menus.permission, mas os 33 campos estão vazios. Assim, passa todos os itens dos módulos seleccionados nessa etapa. Isto não demonstra que as páginas aceitam acções sem autorização; os controlos de backend devem ser avaliados separadamente.
7. **Menus administrativos fixos.** Alguns itens permanecem no Blade fora da árvore dinâmica. Adaptar apenas menus.permission não basta para adaptar toda a barra lateral aos papéis.
8. **Hierarquia de módulos não define activação automaticamente.** Os IDs 2–5 estão sob Gestão Aduaneira e Gestão de Clientes sob Facturação. O menu usa IDs explicitamente associados ao plano; não expande todos os descendentes do módulo pai. É preciso definir essa regra comercial, sem inferir automaticamente que activar o pai concede todos os filhos.

## Aplicação dos perfis

| Perfil existente | Prioridade de navegação proposta |
|---|---|
| Gestor Despachante / Operador | Processos, Licenciamentos, Declarações, Pauta e documentos |
| Praticante | Áreas operacionais com as permissões de consulta/criação efectivamente concedidas; sem presumir poderes de aprovação |
| Gestor Financeiro | Conta corrente, serviços, facturação autorizada e relatórios financeiros existentes |
| Gestor Auditor | Relatórios e auditoria com as permissões efectivas; não presumir edição |
| Gestor / Administrador | Supervisão e administração conforme permissões e módulos contratados |
| Transitário | Priorizar os processos e documentos pertinentes; confirmar necessidades logísticas antes de acrescentar módulos novos |
| Customer Manager / customer-manager | Clientes e acompanhamento conforme os acessos reais |

Estas prioridades são propostas de apresentação, não concessões de acesso. Unir permissões dos papéis válidos da empresa activa e permissões directas; deduplicar os itens. Não usar apenas roleNaEmpresa(), que devolve o primeiro papel, para decidir todo o menu.

## Caminho seguro de integração

1. Definir uma única regra de módulos efectivos utilizada pelo menu e pelas rotas. Subscrição válida e empresa activa continuam obrigatórias para funcionalidades contratadas; papéis administrativos não devem conceder automaticamente módulos não contratados. Configuração de conta, subscrição e recuperação de acesso precisam das excepções já previstas.
2. Alinhar os modelos ao esquema real e reconciliar activaçōes legadas com subscrições. Não aplicar imediatamente uma intersecção obrigatória com activated_modules: os 16 registos actuais não têm ligação e isso pode ocultar funcionalidades actualmente acessíveis.
3. Fazer resolução comparativa sem alterar a navegação primeiro. Distinguir falta de vínculo legado de revogação explícita. Uma alternativa temporária pelo plano deve ser delimitada, monitorizada e removida depois da reconciliação; não deve ignorar desactivação conhecida.
4. Preencher menus.permission com permissões existentes e verificar o mesmo controlo no destino. Inventariar lacunas no catálogo: módulos presentes como Contabilidade/Pauta nem sempre têm prefixos correspondentes em config/rbac.php. Não inventar nomes nem reduzir a visibilidade sem comparar os acessos de cada perfil.
5. Reutilizar menus.position apenas depois de verificar os seus valores e usos actuais; o campo existe, mas não foi demonstrado que represente grupos. Caso inadequado, acrescentar group_code nullable ou uma tabela de grupos, como metadados de apresentação. Os papéis existentes dispensam uma nova tabela de perfis.
6. Manter os IDs, rotas, módulos e árvore existentes; corrigir separadamente a Pauta sob Contabilidade. Grupos vazios desaparecem depois de aplicar o acesso. Novos itens só entram com destino funcional.
7. Verificar vários papéis, troca de empresa, revogação de permissão, expiração, suspensão, módulos adicionais e reconciliação de dados legados antes de activar o novo menu. A organização visual pode ter uma opção de retorno; as regras de acesso não devem depender da apresentação.

Ferramenta de inspecção agregada: `php scripts/audit-menu-access.php`. A avaliação não executou activaçōes, migrações, alterações de papéis ou pagamentos.
