# Análise do menu lateral do LogiGate

Data: 10/10/2026. Público prioritário indicado pelo utilizador: despachantes e equipas de operações aduaneiras.

## Parecer

O menu contém as principais áreas necessárias, mas a organização actual deve melhorar antes de acrescentar muitos itens. O trabalho aduaneiro deve ter prioridade; gestão financeira e administração devem aparecer conforme o papel do utilizador. A quantidade de opções é menos problemática que a duplicação, os destinos vazios e a hierarquia que não corresponde à tarefa.

Foi criada uma proposta navegável em `docs/menu-lateral-proposta.html`, com os perfis Operações, Gestor e Financeiro. É uma simulação de organização, não uma alteração das permissões nem uma implementação das funcionalidades propostas.

## Evidência e limites

- Leitura dos 33 registos actuais de `menus`, dos módulos e das rotas registadas; nenhuma alteração à base de dados.
- Renderização do Blade real `livewire/menu-dinamico.blade.php` com todos os itens configurados. A sessão de cada cliente pode mostrar menos itens, conforme empresa, plano e permissões. A integração Hongayetu foi mantida desligada na pré-visualização.
- Navegação no Edge pelos grupos Processos, Licenciamentos, Gestão de Clientes, Faturação Aduaneira, Declarações Aduaneiras e Contabilidade; abertura da pauta aninhada e selecção de Consultar Pauta. Os links de destino foram interceptados: não representam páginas autenticadas carregadas.
- O acesso a `http://localhost/dashboard` falhou com ligação recusada na porta 80. Não foi possível avaliar a experiência completa dentro das páginas reais.
- A pré-visualização usa a cópia local de Font Awesome disponível, que pode diferir do CDN 6.5 utilizado pelo layout. Ausência de um ícone nesta captura não prova ausência no ambiente real.
- A proposta foi percorrida, incluindo troca de perfil e selecção de uma função nova. Nenhum erro JavaScript foi observado.

Capturas, inventário e resultado da navegação: `storage/app/menu-ux-review/`. Gerador: `scripts/menu-ux-review.php`; verificação: `scripts/verify-menu-ux-review.cjs`.

## Problemas prioritários

| Prioridade | Evidência | Efeito e recomendação |
|---|---|---|
| Alta | Avença e Contratos apontam para `avenca.index`, inexistente; Faturação aponta para `documentos.index`, inexistente. | Os itens terminam em `#` devido à protecção de rotas. Ocultar até existir destino ou apresentar uma indicação explícita de indisponibilidade. |
| Alta | Emitir Recibos, Requisição de Fundos, Relatórios financeiros e Emitir Faturas têm `#` na configuração actual. | Não devem parecer acções funcionais. A emissão integrada já tem rotas próprias quando Hongayetu está activo. Evitar duplicar essa navegação. |
| Alta | Pauta Aduaneira, id 30, está sob Contabilidade; Consultar/Importar Pauta estão sob esse id. Pauta principal, id 31, não tem filhos e aponta para `#`. | Mover a árvore da pauta para um único grupo independente. Um cliente com módulo Pauta e sem Contabilidade pode perder os filhos quando o pai é filtrado. A estrutura de dados tem de alinhar-se com os módulos contratados. |
| Alta | Importação e Exportação apontam ambas para `processos.create`, sem parâmetros diferentes no menu. | Usar Novo processo com selecção do regime no formulário, ou criar filtros/contexto distintos antes de manter dois links. |
| Média | Gestão Licenças (.txt) está sob Declarações Aduaneiras, mas aponta para a lista PICE de licenciamentos. | Colocar em Licenciamentos; confirmar o rótulo Lista PICE com a equipa. Evitar formatos de ficheiro como principal explicação da tarefa. |
| Média | Relatórios fixo abre apenas `relatorio.licenciamento`. | Chamar Relatórios de licenciamento até existir um centro de relatórios mais abrangente. |
| Média | Conta Corrente redirecciona para a listagem de Clientes. | Manter acesso contextual no cliente, ou criar uma página financeira dedicada; o rótulo deve corresponder ao destino. |
| Média | Configurações fixo aponta para `#`, apesar de existir `configuracoes.index`. | Ligar a página existente para perfis autorizados. |
| Média | O agrupamento visual muda quando há mais de 10 raízes e vários módulos. | Definir grupos estáveis orientados a tarefas; acrescentar um item ou mudar o plano não deveria reorganizar inesperadamente toda a navegação. |
| Média | Todos os 33 registos lidos têm `permission = null`; alguns links fixos ficam sempre visíveis. | Definir visibilidade por função, além do plano. Verificar as autorizações das rotas separadamente; esconder menus não substitui controlo de acesso. |

## Estrutura recomendada

| Área | Itens | Perfil principal |
|---|---|---|
| Início | Visão geral; Agenda e automações; Pendências e prazos, quando implementado | Operações e gestão |
| Operação aduaneira | Processos; Licenciamentos; Declarações aduaneiras; Pauta e cálculo aduaneiro; Documentos e arquivo | Despachantes e operadores |
| Clientes e gestão | Clientes e intervenientes; Financeiro; Relatórios de licenciamento | Operações conforme função, gestores e financeiro |
| Administração | Configurações da empresa; Integrações; Plano e subscrição; Segurança e auditoria; Contabilidade conforme função | Administradores, gestores e contabilistas autorizados |

Os títulos das áreas são separadores visuais, não mais um nível de acordeão. Os grupos abrem os seus itens; as páginas finais têm destinos reais. Evitar três níveis para operações frequentes.

## Melhorias que aproveitam páginas existentes

1. Tornar Visão geral o primeiro item e usar português consistente em vez de Dashboard.
2. Usar Todos os processos e Todas as licenças. Colocar Novo processo e Nova licença também como botões nas respectivas listagens, sem necessidade de multiplicar entradas principais.
3. Dar acesso ao Simulador aduaneiro (`pauta.simulador`) e Importar do ASYCUDA (`processos.importar-asycuda`) no contexto das operações.
4. Manter a importação/manutenção da pauta na administração para quem tem permissão; a consulta e a simulação ficam disponíveis para operadores autorizados.
5. Colocar serviços/preços e conta corrente no contexto financeiro; mostrar Facturas/Emitir factura apenas quando a integração está disponível e autorizada.
6. Rever a designação Tarefas: a implementação actual é de tarefas agendadas e automações, incluindo executor IA. Não chamar Minhas tarefas se não existir atribuição e filtro efectivos por utilizador.
7. Uniformizar ícones Font Awesome válidos, alinhamento, terminologia de facturação e destaque da página activa. Adicionar `aria-expanded`, `aria-controls` e `aria-current`; grupos devem usar botões quando apenas expandem conteúdo. Verificar foco por teclado e distinguir folhas indisponíveis.

## Funções novas com mais valor

| Proposta | Valor para o despachante | Implementação necessária |
|---|---|---|
| Pendências e prazos | Saber o que bloqueia processos e quais as próximas acções. | Centro com documentos em falta, responsáveis, vencimentos e filtros por empresa/utilizador. Não confundir com automações agendadas. |
| Requisições de fundos | Acompanhar pedidos por processo, aprovação e saldo disponível. | Há uma acção contextual de impressão, mas não foi verificada uma listagem dedicada com esse fluxo. |
| Colaboração com o cliente | Identificar documentos pedidos/recebidos e actualizações comunicadas ao cliente. | O portal do cliente já tem rotas; é necessário definir a experiência operacional de convite, pedidos e acompanhamento dentro da equipa. |
| Relatórios operacionais | Consultar tempos de tratamento, pendências e evolução dos processos. | O relatório fixo actual é de licenciamento. Definir métricas, filtros e limites por perfil antes de criar um centro geral. |

Uma pesquisa global de processos, licenças e clientes e atalhos recentes podem ter mais valor que acrescentar novas secções. Contadores de pendências devem usar dados reais, sem mostrar números artificiais ou informação de outras empresas.

## Como confirmar a proposta com clientes

Testar tarefas com representantes dos perfis: localizar um processo, abrir uma licença, consultar a pauta, importar uma declaração ASYCUDA, encontrar um documento e consultar a conta do cliente. Observar primeira escolha, conclusão, recuos e dúvidas sobre os nomes. Esta avaliação é uma hipótese de design apoiada na configuração e no código, não um teste de usabilidade realizado com clientes.

Labels claros, termos consistentes e indicação da localização são recomendados pela [checklist de menus da Nielsen Norman Group](https://www.nngroup.com/articles/menu-design/). A encontrabilidade da estrutura pode ser avaliada por [tree testing](https://www.nngroup.com/articles/tree-testing/).

Ordem de execução recomendada: corrigir destinos e hierarquia; estabilizar grupos e perfis; validar com utilizadores; implementar Pendências e prazos; desenvolver as restantes funções conforme procura demonstrada.
