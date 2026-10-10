# Compatibilidade do layout — 10/10/2026

Validado localmente com Laravel 12.69.3, Livewire 3.8.10, Carbon 3.14.2 e PHP 8.5.10.

## Reparações

- MenuBuilder: substituição de `dispatchBrowserEvent` por `dispatch`, inicialização com os mecanismos do Livewire 3 e chamada de gravação dirigida à instância correcta. As listas principais e os submenus vazios aceitam arrastar itens; os handlers e as instâncias Sortable são limpos para evitar duplicação.
- Validação do módulo corrigida de `modulos` para `modules`. Autorizações verificadas em cada acção. A reordenação valida referências e ciclos antes de gravar numa transacção.
- MenuDinamico: árvore construída sem referências PHP; agrupamento por módulo definido no componente e consulta ao modelo `Module`. Rotas ausentes usam um destino seguro. Os pais de uma rota activa abrem automaticamente.
- `MenuTree` partilha a construção da árvore. O menu público não promove filhos cujo pai foi filtrado por permissões; o editor permite visualizar raízes órfãs. Ciclos existentes não provocam recursão infinita; os dados históricos não são alterados automaticamente.
- SubscriptionWidget: conversões explícitas das diferenças de datas do Carbon 3, sem truncamento implícito de float para int. Dias positivos parciais arredondam para cima; negativos para baixo. A expiração verifica directamente a data e a percentagem usa segundos, limitada a 0–100. Datas ausentes e intervalos inválidos são tratados.
- Propriedades da subscrição e da conta bloqueadas contra alterações do cliente. O checkout resolve a empresa activa novamente no servidor. O widget cabe na barra de 64 px e distingue estados pendente, cancelado, suspenso e expirado.
- O layout padrão dos componentes usa o layout administrativo existente. A inicialização Alpine redundante foi removida e o listener global de notificações evita registos repetidos.

O Sortable continua a usar o CDN já existente. A edição da prioridade fica disponível no formulário. Não foram alterados contratos de pagamento, migrações ou dados da aplicação.

## Validação

- `php tests/run-layout-compatibility-isolated.php`: 8 testes, 36 asserções, aprovados. O executor exige ambiente local de testes, cria uma base temporária própria e elimina-a ao terminar. Inclui gravação Livewire, autorizações, reordenação atómica, ciclos, agrupamento e estados da subscrição.
- `npm.cmd run build`: aprovado. Avisos não bloqueantes sobre Browserslist desactualizado e uma classe existente do DaisyUI.
- Verificação Playwright/Edge: inicialização real do Livewire e Sortable, arrastar itens, serialização da ordem, ausência de handlers duplicados, ausência de erros JavaScript e widget dentro da barra em 768 e 1440 px.
- A verificação visual usa componentes com dados fictícios; a chamada de gravação é interceptada. Não valida uma sessão autenticada completa nem o processamento de pagamentos.
- `git diff --check`: aprovado.

Pré-visualização isolada: `scripts/layout-compatibility-preview.php`. Verificação visual: `scripts/verify-layout-compatibility.cjs`. Capturas e relatório ficam em `storage/app/layout-compatibility-qa` e não constituem publicação da aplicação.
