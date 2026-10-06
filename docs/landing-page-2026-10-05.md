# Landing page LogiGate — implementação local

Data: 5 de Outubro de 2026. Branch: `codex/landing-page-references`.

## Diagnóstico e âmbito

- Não foram encontrados ficheiros AGENTS.md aplicáveis na árvore inspeccionada ou nos directórios ascendentes.
- Laravel 11.32.0, Livewire 3.7.1, Blade, Tailwind 3.4.4, Vite 5.4.21 e Alpine já existem no projecto. O PHP local é 8.5.10; a referência do Composer é PHP 8.2.
- `resources/views/welcome.blade.php` concentra a página inicial. Antes usava Tailwind 2 via CDN, fontes e ícones externos, uma imagem antiga, promessas de activação não sustentadas e uma ligação sem destino no rodapé.
- `app/Http/Controllers/WebPage/WelcomeController.php` fornece os planos do modelo `Plano`; a relação `itemplano` fornece as funcionalidades. Agora carrega a relação antecipadamente para evitar uma consulta por cartão.
- `resources/views/WebSite/marketplace.blade.php` pesquisa por nome, especialidade e localização dentro do destino. `consultar_pauta.blade.php` pesquisa por descrição/código. As views não inicializam essas pesquisas a partir dos parâmetros da landing page; por isso a landing oferece ligações directas, sem campos de pesquisa inoperantes.
- O cadastro efectivamente utilizado é `resources/views/auth/register.blade.php`, via Fortify. `WebSite/MinCadastro.blade.php` e o método `CheckoutPlan` também existem, mas não substituem esse contrato.
- As tabelas internas `livewire/tables/processos-table`, `licenciamento-table` e `cliente-table`, e o enum `EstadoProcessoEnum`, foram consultados em leitura para criar uma prévia HTML coerente. Os dados da demonstração são explicitamente fictícios.
- `resources/js/app.js` indica que Livewire já fornece Alpine. A landing usa JavaScript nativo e entradas próprias Vite, sem carregar outra instância de Alpine, Livewire ou Tailwind.
- A remoção preexistente de `resources/views/welcome_c.blade.php` e os PNG fornecidos foram preservados. Sem migrações, alterações a autenticação/pagamentos, push, merge ou deploy.

## Contratos de navegação preservados

| Percurso | Destino |
| --- | --- |
| Página inicial | `home`, `/` |
| Plataforma | `#plataforma`, demonstração na própria página |
| Planos | `#planos` |
| Marketplace | `marketplace`, `/mercado` |
| Pauta Aduaneira | `consultar.pauta`, `/consultar-pauta-aduaneira` |
| Aplicação empresarial | `login`, `/login` |
| Portal do cliente | `cliente.portal.login`, `/portal-cliente/Acesso` |
| Escolher plano | GET `register`, parâmetros `plano` e `modalidade` |
| Contacto | POST `contact.send`, `/api/v1/contact/send` |
| Newsletter | POST `newsletter.subscribe`, `/api/v1/newsletter/subscribe` |

Modalidades da landing: `monthly`, `semestral`, `annual`, conforme `Plano::MODALIDADES_PAGAMENTO`. Os preços são os totais reais de cada período, com duas casas decimais. Um preço nulo é apresentado como indisponível e não como zero. Funcionalidades e limites são lidos do catálogo; não foram inventados benefícios ou destaques comerciais. A grelha adapta-se até quatro colunas no desktop. Sem JavaScript, cada cartão oferece ligações verdadeiras às modalidades disponíveis.

## Decisões de UX/UI

Hero azul profundo com texto em HTML, ciano discreto e imagem conceptual portuária. A variante vertical é usada abaixo de 900 px, mantendo o navio e as gruas. Logo horizontal com serifa no cabeçalho e variante clara no rodapé, sem redesenho da marca.

Três percursos explícitos: empresa/despachante, visitante e cliente. Sem cadastro público para o portal. Pesquisa e contacto não criam uma conta. A demonstração possui separadores com setas, Home/End, estados ARIA e painéis disponíveis sem JavaScript. FAQ usa `details/summary` nativo. O menu móvel gere o foco inicial e fecha com Escape, clique exterior, saída de foco e navegação.

Os formulários conservam validação nativa e endpoints existentes. O JavaScript mostra envio pendente, erros de validação junto dos campos, falha de ligação e sucesso apenas perante resposta HTTP válida com `success: true`. Mantém os dados após erro. O texto de sucesso confirma a recepção do pedido; não promete entrega de email, pois os controladores podem devolver sucesso de persistência mesmo se o email falhar.

CSS e JavaScript carregam apenas na landing. Não há animações contínuas nem fontes ou bibliotecas via CDN. Os logótipos têm dimensões declaradas; o rodapé usa lazy loading e a imagem principal tem prioridade de carregamento. Foco visível, alvos de toque, âncoras com compensação do cabeçalho e preferência por movimento reduzido estão contemplados.

## Assets

Originais preservados em `public/dist/img/LandingPage`. Inventário completo de formato, dimensões, modo e bytes: `asset-inventory.json`.

| Asset | Dimensões | Peso aproximado | Uso |
| --- | --- | --- | --- |
| `Luanda Bay port skyline.png` | 1672 × 941 | 1,98 MB | Original horizontal conceptual |
| `exec-f3ab1d43-e4ec-4cab-a083-999dbf975b96.png` | 1024 × 1536 | 2,14 MB | Original vertical conceptual |
| `Interlocking blue ribbon symbol.png` | 1254 × 1254 | 901 KB | Referência do símbolo, preservada |
| `LOGIGATE logo with interlocking ribbon emblem.png` | 2172 × 724 | 1 MB | Referência sobre fundo azul, preservada |
| `Refined horizontal LogiGate ribbon logo.png` | 2172 × 724 | 390 KB | Origem do logo do cabeçalho |
| `LOGIGATE dark-background logo variant.png` | 2172 × 724 | 388 KB | Origem do logo claro |
| `port-desktop-960.webp` / `1600.webp` | 960 × 540 / 1600 × 900 | 75 / 162 KB | Hero desktop, srcset |
| `port-mobile-480.webp` / `800.webp` | 480 × 720 / 800 × 1200 | 64 / 142 KB | Hero móvel, srcset |
| `logo-horizontal.webp` | 720 × 201 | 53 KB | Cabeçalho, sem perdas |
| `logo-light.webp` | 720 × 182 | 53 KB | Rodapé, sem perdas |

Os derivados dos logótipos apenas removem margens exteriores transparentes e redimensionam a imagem. Não corrigem nem redesenham a arte fornecida. Não foi encontrado SVG original com as três partes separadas. **A animação do símbolo não foi implementada**; necessita de vectorização fiel/asset original. Nenhum PNG foi apresentado como SVG vectorial.

As imagens não são fotografias documentais do Porto de Luanda e não representam acompanhamento AIS. Não foi fornecido um asset separado de tráfego marítimo; a página não depende desse elemento.

## Ficheiros alterados/adicionados

- `resources/views/welcome.blade.php`: página inicial.
- `resources/css/landing.css` e `resources/js/landing.js`: estilos e interacções isolados.
- `vite.config.js`: entradas específicas da landing.
- `app/Http/Controllers/WebPage/WelcomeController.php`: eager loading de `itemplano`.
- `public/dist/img/LandingPage/*.webp` e `asset-inventory.json`: derivados e inventário.
- `scripts/optimize-landing-assets.py`: reprodução da optimização com Pillow.
- `scripts/render-landing-preview.php`, `landing-preview-router.php`, `check-landing-local.php`, `verify-landing.cjs`: validação local isolada e verificações de leitura.
- `tests/Feature/LandingPageTest.php` e `tests/Support/LandingFixtures.php`: renderização, destinos, dados/escaping e catálogo vazio, sem base de dados.
- Este relatório. O build em `public/build` e as capturas em `storage/app/landing-qa` são outputs locais.

## Validação executada

- `npm.cmd run build`: concluído. Landing CSS ~14,52 KB (4,01 KB gzip); JavaScript ~4,28 KB (1,71 KB gzip). Aviso existente: base Browserslist desactualizada.
- `php artisan test --filter=LandingPageTest`: três testes passaram, 22 assertions. O runner mostra depreciações de PHP 8.5/PDO e voku/ASCII existentes; não houve falhas de assertions.
- Lint PHP dos scripts e do controlador: sem erros. `git diff --check`: sem erros de whitespace.
- `php scripts/check-landing-local.php`: o WelcomeController real renderizou com **quatro planos da base de dados local**. O script exige host local sem URL de conexão alternativa e bloqueia consultas diferentes de SELECT/SHOW. Sem alterações na base de dados, emails ou pagamentos.
- Chromium via Microsoft Edge, modo headless: 320, 360, 390, 768 e 1440 px, sem overflow horizontal ou imagens quebradas.
- Menu móvel, foco, Escape, navegação por âncora; demonstração, setas/Home; alterações de preços/modalidades, preço nulo, preservação de plano/modalidade no URL de cadastro interceptado; FAQ; contacto com validação 422, loading, sucesso e falha de rede; newsletter com sucesso e validação; distinção dos acessos; movimento reduzido; planos vazios e funcionamento sem JavaScript: passaram.
- Nenhum erro JavaScript não tratado nem asset HTTP em erro. O antivírus local injectou pedidos a um domínio Kaspersky, bloqueados pela política de rede da prévia e registados separadamente. A falha de rede do formulário é intencional.
- Capturas completas e da primeira dobra foram inspeccionadas em móvel e desktop. Relatório verificável: `storage/app/landing-qa/browser-results.json`.

Capturas: `landing-320.png`, `landing-360.png`, `landing-390.png`, `landing-768.png`, `landing-1440.png`, `hero-390.png`, `hero-1440.png`. A prévia com catálogo local usa `landing-local-plans-1440.png` e `plans-local-1440.png`. As cinco primeiras capturas usam planos de teste identificados para permitir verificações reproduzíveis; a demonstração do produto usa sempre dados fictícios.

## Comandos Windows

Na raiz `C:\Users\USER\Projetos\logigate`:

```powershell
npm.cmd run build
php artisan test --filter=LandingPageTest
php scripts/check-landing-local.php
php scripts/render-landing-preview.php
php -S 127.0.0.1:8123 -t public scripts/landing-preview-router.php
```

Aceder à prévia em `http://127.0.0.1:8123/`; `/empty` mostra catálogo vazio e `/live` o catálogo local renderizado pelo smoke check. O servidor da prévia não despacha rotas da aplicação nem aceita envios efectivos.

Em outro terminal, usando os runtimes já disponíveis neste computador:

```powershell
$env:PLAYWRIGHT_MODULE='C:\Users\USER\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\node_modules\playwright'
$env:LANDING_BROWSER='C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
node scripts/verify-landing.cjs
```

Opcionalmente regenerar imagens:

```powershell
& 'C:\Users\USER\.cache\codex-runtimes\codex-primary-runtime\dependencies\python\python.exe' scripts/optimize-landing-assets.py
```

Para ver a aplicação local completa, com a configuração local previamente confirmada: `php artisan serve --host=127.0.0.1 --port=8000`. Não enviar pedidos de contacto/cadastro/pagamento reais durante revisão visual; a prévia isolada é a opção usada nesta validação.

## Limitações fora do âmbito

1. O cadastro recebe/preserva `monthly`, `semestral` e `annual` na view, mas `CreateNewUser` valida `monthly,yearly`. **O encaminhamento da landing funciona; o cadastro efectivo nas modalidades semestral/anual depende da correcção deste contrato existente.** Não foram alteradas regras de autenticação, subscrição ou pagamento.
2. A activação gratuita existente consulta `is_free` em `RegisterResponse`; não foi confundida com preço igual a zero nem foi executada. Os textos evitam prazos garantidos.
3. Não foi realizado cadastro efectivo, confirmação de pagamento, envio de emails ou persistência de contactos/newsletter. Foram usados envios interceptados. Não foi executada a suite global de testes, que inclui escrita em base de dados e integrações fora deste âmbito.
4. A animação do símbolo aguarda SVG fiel com as partes separadas. A marca actual permanece estática e imediatamente visível.
5. Termos e privacidade não foram adicionados sem documentos/destinos confirmados. Os contactos e a identidade Hongayetu LDA foram reutilizados da página anterior, sem inventar novas entidades ou contactos.
6. Os quatro planos locais têm preços e limites disponíveis, mas não apresentaram itens na relação `itemplano`. Nesse caso, os cartões mostram os limites reais e uma indicação para consultar o apoio sobre funcionalidades; não foram preenchidos com benefícios inventados.
