# Integração implementada — 10/10/2026

Implementação autorizada depois da proposta de análise. Aplicada apenas ao projecto e à base local `logigate_testing`, em loopback. Não representa migração ou validação de produção. As alterações locais anteriores do layout foram preservadas.

## Navegação

- Os 33 registos continuam na tabela `menus`, com os mesmos IDs, rotas, módulos e pais. Não houve atribuição ou consolidação de papéis, nem escrita em tabelas de permissões.
- `NavigationResolver` combina a empresa activa, as permissões RBAC existentes e os módulos efectivos. `NavigationTree` conserva ancestrais necessários como contentores sem acesso próprio, elimina áreas vazias e separa descendentes de outra área apenas na apresentação.
- Áreas: Operação aduaneira, Clientes e gestão, Financeiro, Administração e auditoria. Pauta aparece em Operação aduaneira; a relação comercial e o pai na base ficam preservados.
- A migração específica acrescentou somente `menus.group_code`, nullable. O editor permite escolher uma área ou usar a área automática do módulo; valida os códigos no servidor. `position` continua com a sua semântica original.
- Todos os papéis efectivos contribuem para uma prioridade determinística; permissões directas continuam válidas. Os dois nomes de Customer Manager são tratados separadamente.
- Painel geral e Tarefas antecedem os restantes itens operacionais. Arquivos respeita `documents.view`; atalhos de equipa e integrações respeitam os Gates existentes. Pesquisa e menu de conta passaram a filtrar os atalhos com autorização confirmada.
- Botões de expansão têm `aria-expanded`; os links activos têm `aria-current`. Ícones decorativos não entram no nome acessível.

## Módulos e activação

`EffectiveModuleResolver` é a leitura comum de navegação e middleware. A decisão pura está em `ModuleEntitlements` e não recebe papéis nem concede descendentes.

Uma subscrição só fornece direitos quando está activa, não eliminada, já iniciada e ainda válida. Uma activação explícita tem de pertencer à mesma empresa e a uma subscrição válida, respeitando a sua própria validade. Activações vinculadas permitem extras efectivamente provisionados; `dados_personalizados` não concede extras automaticamente. Uma desactivação explícita do módulo na empresa bloqueia o direito, incluindo renovação, até tratamento comercial explícito.

O fallback legado exige um único contrato elegível, um único registo activo não vinculado, inclusão explícita no plano e ausência de validade conhecida incompatível. Termina em **09/11/2026 às 23:59:59**, configurável por `MODULE_LEGACY_UNTIL`. Deve ser acompanhado semanalmente pelo comparador; não prolongar automaticamente. Na base local, a reconciliação eliminou a necessidade de fallback dos 16 registos analisados.

`CheckSubscription` mantém os redireccionamentos de pagamento e recuperação. `EnsureModuleEntitlement` cobre apenas os padrões explícitos de destinos operacionais, clientes, produtos, pauta e contabilidade em `config/navigation.php`. Conta, selecção de empresa e billing ficam fora desse mapa. As Policies existentes continuam a decidir a permissão funcional. O middleware também é persistente no Livewire, reavaliando a rota original nas actualizações.

`ModuloAtivacaoService` recebe a subscrição concreta, bloqueia o registo na transacção e provisiona por subscrição/módulo. Repetições não criam duplicados nem reactivam revogações. Registos legados não são substituídos durante activação. A assinatura antiga de empresa/plano só aceita uma origem inequívoca. O método alternativo do modelo delega neste serviço.

Foram corrigidos a relação inversa `plano_modulos/modulo_id`, a ordenação dos filhos de módulos e os campos/casts de activação. Não foram inventadas colunas de pivot, tipos de addons ou códigos inexistentes no catálogo.

## Reconciliação local e comparação

`navigation:compare EMPRESA UTILIZADOR` é de leitura, valida pertença à empresa, usa sessão/cache em memória e apresenta IDs acrescentados/removidos, decisões comerciais, permissões ausentes e atalhos fixos visíveis. Não cria grants.

`modules:reconcile EMPRESA` simula a correspondência com TODOS os contratos históricos da empresa. O instante de activação deve caber num único intervalo de contrato que inclua o módulo; datas desconhecidas, contratos sobrepostos, duplicados, desactivação e validade divergente ficam em revisão. Contratos eliminados participam na detecção de ambiguidade e não são candidatos aplicáveis.

`modules:reconcile EMPRESA --apply=MANIFEST.json` revalida a origem, bloqueia os registos, compara fingerprints de activação/subscrição e aborta a transacção em conflito. A repetição do manifest aplicado não altera o resultado. Manifests novos incluem os valores anteriores dos dois campos para retorno controlado.

O script local `scripts/implement-navigation-local.php` recusa qualquer ligação fora de `testing`/`logigate_testing` em loopback e não aceita DB_URL. Sem `--apply`, só compara e simula. Com `--apply`, arquiva o manifest, aplica apenas os vínculos comprovados e executa exclusivamente a nova migração pelo seu caminho.

Execução local: 2 cenários de utilizador/empresa; 16 vínculos comprovados aplicados, distribuídos por 2 empresas. A simulação posterior devolveu zero mudanças e zero ambiguidades. Foram preenchidos `subscricao_id` e `data_expiracao`, sem mudar `active`, módulos, valores, pagamentos ou estados de subscrição.

Nos dois cenários, desapareceram 10 IDs da árvore anterior: placeholders sem destino, rotas inexistentes, contentores sem filhos acessíveis e Serviços sem a permissão efectiva requerida. Esses registos não foram apagados nem substituídos por páginas fictícias. O relatório distingue permissão ausente no catálogo de permissão não concedida.

Relatórios locais em `storage/app/navigation-rollout/` (ignorados pelo Git). O relatório `report-applied.json` conserva o resultado da execução inicial; `report-preview.json` mostra a leitura posterior. As pré-visualizações são estáticas, com os destinos interceptados, e não simulam operações de negócio.

## Validação e limites

- `php tests/run-navigation-isolated.php`: 14 testes, 60 asserções. Clona apenas o esquema numa base aleatória descartável e remove exclusivamente essa base ao terminar. Não executa a suite geral na ligação da aplicação. HTTP externo bloqueado e storage em fixtures.
- `php tests/run-layout-compatibility-isolated.php`: 8 testes, 36 asserções numa base descartável separada.
- Cobertura: múltiplos papéis, permissões directas e revogação, troca de empresa, associação com empresa 0, suspensão, início futuro, expiração no limite, extras provisionados, revogação comercial, idempotência de activação/callback, ambiguidade histórica, origem sem datas, fingerprint alterado, replay, grupos vazios, pais neutros e Pauta separada visualmente.
- Protocolo HTTP real do Livewire: uma actualização permitida passa; depois de revogar o módulo, a actualização seguinte devolve 403.
- Menu Builder: rejeita uma área inválida e guarda uma área válida sem alterar o módulo comercial.
- `scripts/verify-navigation.cjs`: Blade real de um cenário local em 1440/768/390 px, expansão dos grupos e selecção de Pauta, sem overflow horizontal ou erros JavaScript. As imagens e o relatório estão na pasta de rollout.

Continuam lacunas funcionais anteriores: destinos inexistentes de facturação/avenças; permissões não definidas para alguns destinos; páginas placeholder de configuração/auditoria e importação de Pauta. A integração não transforma esses placeholders em funcionalidades nem atribui permissões novas. Políticas/gates, gateways, integrações externas e serviços de armazenamento mantêm a sua lógica de negócio.

## Retorno e acompanhamento

`NAVIGATION_GROUPED=false` oculta os títulos das áreas sem desligar autorizações. Para outra ordenação, editar apenas o catálogo de grupos/prioridades ou `group_code` pelo editor.

Não recuperar atalhos revogados desligando o resolvedor ou o middleware. Um vínculo reconciliado só deve regressar aos campos anteriores mediante comparação do estado actual com o manifest e revisão do impacto comercial. Na execução inicial, os 16 vínculos tinham ambos os campos nulos; preencher a validade corrigiu também a divergência da leitura comercial. A migração `down` elimina apenas a nova coluna e as personalizações visuais nela guardadas, pelo que exige exportar esses valores previamente.

Não houve commit, publicação, merge ou acesso a produção. Antes de transportar a alteração para outra base, correr comparação e simulação nessa base, rever reduções de acesso, guardar manifests e aplicar apenas a migração específica.

Auditoria posterior: 33 menus, 12 papéis, 3 associações de papéis, 311 vínculos papel/permissão e 57 permissões, com as contagens anteriores preservadas. As 16 activações estão vinculadas e não há divergências entre empresas de activação/subscrição.
