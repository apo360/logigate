# Dinâmica visual da landing page LogiGate

Implementação local na branch existente `codex/landing-page-references`, em 5 de Outubro de 2026. As alterações anteriores do projecto foram preservadas. Sem deploy, push ou merge.

## Diagnóstico

Não foram encontrados AGENTS.md aplicáveis. A `welcome.blade.php` é uma página autónoma, com entradas próprias de CSS/JavaScript via Vite. Não usa o layout `layouts/website/app.blade.php`, Livewire, Alpine ou `wire:navigate`.

Existem referências externas a **AOS 2.3.1** em `layouts/website/app.blade.php`, `WebSite/marketplace.blade.php`, `WebSite/consultar_pauta.blade.php` e numa página antiga do cliente. A consulta da pauta e o marketplace inicializam AOS nos seus próprios scripts. O `aos.css` referido pertence à biblioteca publicada no CDN, não a uma personalização local. Não foi encontrado ficheiro local `aos.css`/`aos.js`, pacote `node_modules/aos` ou dependência AOS no package.json/lock, nem inicialização de AOS na landing actual.

Por isso foi utilizado CSS e IntersectionObserver nativo, conforme a alternativa autorizada no pedido, sem instalar bibliotecas nem alterar as outras páginas. Não foi necessário AOS.refresh()/refreshHard() ou associar eventos Livewire: o observer acompanha naturalmente as alterações de geometria e a navegação desta página é completa.

## Alterações de produção nesta tarefa

| Ficheiro | Alteração |
| --- | --- |
| `resources/css/landing-motion.css` | Novo ficheiro de efeitos limitado a `.lg-landing` |
| `resources/js/landing-motion.js` | Novo inicializador com observer, recuperação e protecção contra duplicação |
| `resources/css/landing.css` | Apenas import do CSS de movimento |
| `resources/js/landing.js` | Apenas import e chamada do inicializador, após os comportamentos existentes |

**A `welcome.blade.php`, o backend, o Vite config, os textos, imagens, cores, geometria, rotas, formulários, preços, limites e parâmetros de cadastro não foram alterados nesta tarefa.** Classes e atributos temporários de animação são aplicados apenas em runtime. O logótipo PNG permanece estático; não foi encontrado SVG fiel com três partes separadas.

Também foram adicionados `scripts/verify-landing-motion.cjs` e este relatório. `scripts/verify-landing.cjs` passou a permitir um directório alternativo para outputs e usa movimento reduzido na suite funcional, para verificar o layout concluído; os efeitos são exercitados separadamente na nova suite. Não foram alterados os testes funcionais nem as regras da aplicação.

## Efeitos e durações

| Elemento | Efeito |
| --- | --- |
| Texto principal do hero | TranslateY de 8 px para zero, 450 ms, uma vez; opacity sempre 1 |
| Imagem, título, acções e marca | Nunca escondidos nem atrasados pela inicialização |
| Blocos relevantes abaixo da dobra | Opacity e translateY de 16 px; 12 px no móvel; 500 ms, ease-out, uma vez |
| Grupos | Primeiros 2–4 blocos, intervalos de 70 ms; 60 ms no móvel; atraso máximo 210/180 ms |
| Botões e navegação | Transições de cor, fundo, borda e transform, 180 ms; botão desloca 1 px em hover/pressão quando apropriado |
| Menu móvel | Entrada curta de 4 px e opacity .92→1, 160 ms; fecho imediato pelo comportamento existente |
| Painel de separador e resposta da FAQ | Entrada curta de 4 px e opacity .92→1, 180 ms; conteúdo/estado muda imediatamente |
| Modalidades | Transição visual de 180 ms no controlo existente; preços e hidden inputs mantêm a lógica original |

Não há animação de altura, parallax, zoom, animação contínua, `transition: all`, sombras/filtros animados ou `will-change` permanente. O movimento ao scroll fica nos blocos exteriores; o feedback dos botões fica nos seus filhos, evitando conflito de transforms.

## Disponibilidade e ciclo de vida

- O HTML e os estilos base mantêm o conteúdo visível. Estilos de espera só são activados depois de criar o observer, observar os elementos e registar os mecanismos de recuperação.
- Um estado associado ao body através de Symbol impede outro observer ou listener de foco após chamadas repetidas ao inicializador.
- O observer deixa de observar cada bloco revelado. Não existe polling ou refresh contínuo.
- Foco dentro de um bloco cancela imediatamente a espera; CSS `:focus-within` também elimina a transição/atraso nesse estado.
- Erros na construção, no registo de observação ou no callback mostram todos os blocos e desligam os recursos de movimento. A lógica funcional já foi inicializada antes da chamada de movimento.
- Com movimento reduzido, o conteúdo aparece imediatamente, sem entradas/transições decorativas, e o scroll suave fica desactivado. Activar essa preferência durante a sessão termina os efeitos pendentes. Voltar a permitir movimento não reinicia entradas concluídas.
- Não foram adicionados listeners Livewire: esta landing não usa a navegação Livewire. Eventos Livewire/DOMContentLoaded repetidos foram simulados para confirmar que não reinicializam os efeitos.

## Verificações executadas

- **Build Vite concluído**: 61 módulos. CSS da landing 16,33 KB / 4,36 KB gzip; JS 6,11 KB / 2,44 KB gzip. Nenhuma dependência nova. Permanece o aviso existente de Browserslist desactualizado.
- **Três testes Blade passaram**, com 22 assertions. O runner continua a apresentar avisos existentes de depreciação de PHP 8.5/PDO e voku/ASCII.
- Chromium via Edge headless: **320, 360, 390, 768 e 1440 px**, sem overflow horizontal, com todos os blocos revelados através de scroll real antes das capturas.
- Estados intermédios reais medidos no navegador: por exemplo, opacity 0→0,0555→0,1087 e translateY 12→11,33→10,70 px. O relatório conserva as amostras com timestamps. Isto verifica a interpolação, para além das capturas estáticas.
- Ao voltar a um bloco já visto, não aparece outra entrada.
- Foco num campo de um bloco ainda pendente mostra-o imediatamente e preserva o outline.
- Duas chamadas extra ao inicializador e eventos de navegação simulados mantiveram **um observer e um listener de foco**.
- Movimento reduzido no carregamento e alterado durante a sessão: conteúdo visível, sem animação e com scroll normal.
- Falhas simuladas: IntersectionObserver ausente, construtor a lançar erro, observe() a lançar erro, unobserve() a lançar erro durante callback, AOS com init() a lançar erro, script da landing bloqueado e JavaScript desactivado. O conteúdo permaneceu visível; nenhum erro não tratado foi observado. O AOS defeituoso não é chamado, pois não é usado nesta página.
- Suite funcional existente passou novamente: menu/foco/Escape, separadores/teclado, modalidades/preços, preço indisponível, URL de cadastro preservando `plano`/`modalidade`, FAQ, validação/envio pendente/sucesso/falha de rede de contacto, newsletter e distinção entre os acessos.
- Envios de formulários e navegação ao cadastro foram interceptados. Nenhum envio real, cadastro, pagamento ou integração externa foi executado.
- Sem erros JavaScript não tratados nem erros HTTP nos assets da aplicação na suite funcional. Pedidos injectados pelo antivírus Kaspersky foram bloqueados e registados separadamente, como na validação anterior.
- Screenshots móveis e desktop foram inspeccionados, incluindo frames intermédios da revelação. O conteúdo e a composição aprovados mantêm-se.

## Artefactos locais

Em `storage/app/landing-qa/motion/`:

- `motion-results.json`: verificações de movimento, fallbacks, tamanhos e amostras.
- `motion-samples.json`: evolução temporal de opacity/transform.
- `motion-layout-320.png`, `360.png`, `390.png`, `768.png`, `1440.png`: página completa após os efeitos.
- `motion-hero-390.png`, `motion-hero-1440.png`: primeiras dobras.
- `frames/motion-00.png` a `motion-13.png`: frames capturados no navegador durante a revelação.
- `landing-motion.gif`: sequência desses frames reais, com intervalos normalizados para revisão. É uma demonstração visual em GIF, não um vídeo com tempos de captura exactos.
- `functional/browser-results.json` e respectivas capturas: regressão funcional com dados fictícios de teste.

## Repetir no Windows

Na raiz do projecto:

```powershell
npm.cmd run build
php scripts/render-landing-preview.php
php artisan test --filter=LandingPageTest
php -S 127.0.0.1:8123 -t public scripts/landing-preview-router.php
```

Em outro terminal:

```powershell
$env:PLAYWRIGHT_MODULE='C:\Users\USER\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\node_modules\playwright'
$env:LANDING_BROWSER='C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
node scripts/verify-landing-motion.cjs
$env:LANDING_QA_DIR='storage/app/landing-qa/motion/functional'
node scripts/verify-landing.cjs
```

A prévia isolada em `http://127.0.0.1:8123/` usa a view real com planos de teste identificados e não despacha endpoints da aplicação. Não foram reescritos o catálogo ou outras configurações locais.

## Pendências

A iluminação sequencial da marca depende de um SVG original fiel com partes separadas; o PNG ficou estático, como solicitado. As pendências anteriores de cadastro/subscrição continuam fora do âmbito desta tarefa. Não foi produzido MP4; estão disponíveis o GIF de frames reais e as amostras de movimento no navegador.
