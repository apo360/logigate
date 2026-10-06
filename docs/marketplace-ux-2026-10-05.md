# Marketplace LogiGate — melhoria local

Branch: `codex/marketplace-ux`. Data: 5 de Outubro de 2026. Sem publicação, deploy, push ou merge. Trabalho anterior e remoção preexistente de `welcome_c.blade.php` preservados.

## Diagnóstico

O caminho real é `resources/views/WebSite/marketplace.blade.php`. É uma view autónoma, sem includes, componentes Livewire ou Alpine. A rota pública GET `/mercado`, nome `marketplace`, chama `WelcomeController::marketplace()` e apenas devolve a view, sem dados.

A implementação anterior carregava Tailwind 2, Font Awesome e AOS 2.3.1 via CDN. Renderizava cartões por interpolação de strings JavaScript e tentava usar os seguintes destinos:

| Destino declarado no JavaScript anterior | Constatação local |
| --- | --- |
| GET `/api/v1/marketplace/despachantes?page=…&especialidade=…` | Rota/controller inexistentes |
| GET `/api/v1/marketplace/despachantes/busca?q=…` | Rota/controller inexistentes |
| `/marketplace/despachante/{id}` | Rota e view pública de perfil não encontradas |
| POST `/api/v1/marketplace/despachantes/{id}/contactar`, JSON `{mensagem}` | Rota e validação de backend inexistentes |

`php artisan route:list --path=marketplace --except-vendor` confirmou ausência de rotas. Não foi encontrado modelo público `Despachante`, consulta de perfis publicados ou política de publicação nas migrations/modelos/controllers locais. O modelo interno `Empresa` tem contactos e dados operacionais, mas não estabelece autorização para publicação no marketplace; não foi usado como catálogo público.

As opções de ordenação e o botão “Carregar mais” não tinham listeners. O menu móvel também não tinha inicialização funcional. A abertura do modal de contacto adicionava `flex` mas mantinha `hidden`; o envio procurava uma meta CSRF que não existia. A pesquisa era por clique e ignorava termos com menos de dois caracteres, mas não possuía API efectiva. Os filtros visuais estavam associados a valores hardcoded sem fonte pública confirmada. Não havia contagem/paginação real, estado vazio ou estado de erro compreensível.

O rodapé incluía ligações `#`, ano fixo e NIF/registo comercial sem origem confirmada. A página apresentava classificações e selo de verificação sem fonte implementada. Não existia um fluxo de propostas funcional para preservar.

Stack confirmada: Laravel 11.32.0, Livewire 3.7.1 instalado no projecto, Blade, Tailwind 3.4.4, Vite 5.4.21 e Alpine no projecto. Esta página não precisa de Livewire/Alpine. Não foram encontrados AGENTS.md aplicáveis.

## Melhorias implementadas

- Identidade coerente com a landing aprovada: logótipo horizontal oficial e variante clara, azul institucional/profundo, ciano discreto, tipografia, largura máxima de 1240 px e botões de 48 px.
- Cabeçalho com destinos reais: início/plataforma/planos na landing, `/mercado`, consulta da pauta, aplicação empresarial e portal do cliente. A página inicial não foi modificada.
- Título solicitado “Encontre um despachante”. Texto de apoio adaptado aos acessos realmente disponíveis, sem prometer pesquisa ou perfis actualmente ausentes.
- Estado explícito **“O catálogo ainda não está disponível nesta página.”** Não foi confundido com zero perfis publicados ou pesquisa sem resultados: a ausência é da fonte/serviço, não uma consulta que devolveu zero.
- Ligações verdadeiras ao contacto geral da equipa na landing e à Pauta Aduaneira. O contacto geral está identificado como contacto com a equipa LogiGate, não pedido de proposta a um despachante.
- Sem campos de pesquisa, filtros, ordenação ou paginação decorativos; sem pedidos a APIs inexistentes. Não foram criados endpoints, regras de publicação ou nova fonte de dados privados.
- Orientação clara para pauta e portal. Consultar a página/contactar a equipa não cria conta. Sem novas exigências de autenticação.
- Newsletter existente preservada: mesmo endpoint POST `newsletter.subscribe`, campo `email`, token CSRF e validação nativa. Estados de envio pendente, sucesso confirmado, validação, falha de rede e bloqueio de submissões duplicadas. Valores mantidos após erro. O sucesso confirma recepção do pedido, sem garantir entrega de email.
- Menu compacto em linha, com foco inicial, Escape, devolução de foco, fecho por clique exterior/saída de foco e mudança para desktop. Links continuam disponíveis sem JavaScript.
- Labels visíveis, HTML semântico, skip link, foco visível, campos a 16 px e alvos de toque. Não há listagem ou contacto público com dados privados.
- Movimento da mesma linguagem da landing: apresentação em 450 ms/8 px, sem ocultar conteúdo; menu em 160 ms/4 px; controlos em 180 ms e botão com deslocamento de 1 px em hover. Movimento reduzido desactiva os efeitos. Sem AOS, bibliotecas duplicadas, polling ou animação contínua.
- Logo com dimensões declaradas; logo do rodapé em lazy loading. Sem novos assets raster ou dependências.

## Ficheiros desta tarefa

- `resources/views/WebSite/marketplace.blade.php`: página autónoma substituída pela apresentação coerente com o estado real.
- `resources/css/marketplace.css` e `resources/js/marketplace.js`: assets próprios e isolados.
- `vite.config.js`: duas entradas adicionais; entradas da landing mantidas.
- `tests/Feature/MarketplacePageTest.php`: rota pública, destinos, ausência de referências inseguras/fictícias, sem autenticação.
- `scripts/marketplace-preview.php`: prévia da view/controlador reais, sem base de dados ou endpoints activos.
- `scripts/verify-marketplace.cjs`: verificação no navegador e envios interceptados.
- Este relatório.

Backend, modelos, controllers, permissões, subscrições, pagamentos, S3, perfil e pauta não foram alterados. Os cinco ficheiros aprovados da landing (`welcome.blade.php`, CSS/JS e CSS/JS de movimento) foram comparados por SHA-256 antes/depois: **iguais**. Os hashes dos assets Vite da landing também permaneceram iguais no build.

## Validação executada

- `npm.cmd run build`: concluído; 63 módulos. Marketplace CSS 7,09 KB/2,13 KB gzip; JS 2,19 KB/1,03 KB gzip. Aviso preexistente de Browserslist desactualizado.
- `php artisan test --filter='MarketplacePageTest|LandingPageTest'`: **quatro testes passaram, 37 assertions**. Avisos de depreciação preexistentes de PHP 8.5/PDO e voku/ASCII; sem assertions falhadas.
- Lint PHP/JS e `git diff --check`: sem erros. Git apresenta apenas aviso de normalização CRLF/LF nas views.
- Chromium via Edge headless: **320, 360, 390, 768 e 1440 px**, sem overflow horizontal, imagens quebradas ou assets HTTP em erro. Capturas móveis e desktop inspeccionadas.
- Menu por teclado, foco visível, Escape/devolução de foco, clique exterior, destinos e distinção dos acessos: passaram.
- Newsletter com 422, loading, sucesso, falha de rede e tentativa de submissão duplicada: passaram com respostas interceptadas. Nenhum email ou subscritor real foi enviado/criado.
- Movimento reduzido, JavaScript desactivado e asset JavaScript bloqueado: conteúdo e navegação disponíveis.
- Sem erros JavaScript não tratados e sem pedidos à API inexistente do marketplace. Tráfego externo é bloqueado na prévia/testes, incluindo scripts injectados pelo antivírus local.
- Sem consultas à base de dados nesta página: portanto sem N+1 ou exposição de empresas/clientes. Não foram executadas migrations ou testes com escrita.

**Não foram declarados nem testados como funcionais** pesquisa, combinações/limpeza de filtros, ordenação, contagem, paginação, perfil ou envio de contacto a despachante: as respectivas dependências não existem no projecto inspeccionado. Não foi publicada uma listagem fictícia para simular conclusão dessas funcionalidades.

## Capturas e relatórios

`storage/app/marketplace-qa/marketplace-320.png`, `marketplace-360.png`, `marketplace-390.png`, `marketplace-768.png`, `marketplace-1440.png` e `browser-results.json`. São outputs locais. A prévia pode ser aberta em `http://127.0.0.1:8124/mercado`; endpoints da aplicação estão desactivados nesse servidor isolado.

## Comandos Windows

Na raiz do projecto:

```powershell
npm.cmd run build
php artisan test --filter='MarketplacePageTest|LandingPageTest'
php scripts/marketplace-preview.php
php -S 127.0.0.1:8124 -t public scripts/marketplace-preview.php
```

Em outro terminal:

```powershell
$env:PLAYWRIGHT_MODULE='C:\Users\USER\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\node_modules\playwright'
$env:LANDING_BROWSER='C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
node scripts/verify-marketplace.cjs
```

Para a aplicação completa: `php artisan serve --host=127.0.0.1 --port=8000`, depois `/mercado`. Não enviar newsletters reais durante revisão; usar a prévia isolada e testes interceptados.

## Dependências para tornar o catálogo operacional

É necessária uma fonte pública autorizada de perfis e uma regra explícita de publicação/visibilidade, seguida das rotas de consulta, pesquisa/paginação, perfil e contacto com respectivas validações. Os destinos de perfil/contacto não foram reconstruídos neste âmbito. Não é seguro inferir essa autorização a partir dos dados internos de `Empresa`.
