# Consulta pública da Pauta — 6 de Outubro de 2026

Evolução local da consulta e continuidade com o marketplace. Sem publicação, deploy, push ou merge. Alterações locais anteriores preservadas; `welcome.blade.php` não foi alterada nesta tarefa. Cabeçalho, rodapé, destinos de apoio, navegação e contactos da consulta foram mantidos. Não foram alterados modelos/regras operacionais, autenticação, pagamentos, integrações ou S3. Nenhuma migration nova foi necessária ou aplicada nesta evolução.

## Inspecção e diagnóstico

Stack confirmada: Laravel 11.32.0, Livewire 3.7.1, PHP 8.5.10, Vite 5.4.21 e Tailwind instalado 3.4.4. A página pública existente usa Tailwind 2.2.19/AOS/particles via CDN; nenhuma biblioteca nova foi acrescentada. Não se encontrou AGENTS.md no projecto ou nos pais inspeccionados.

View real: `resources/views/WebSite/consultar_pauta.blade.php`; rota pública `consultar.pauta`, `/consultar-pauta-aduaneira`. Marketplace confirmado em `resources/views/WebSite/marketplace.blade.php`, preservado. A rota da consulta usa agora `WebPage/PublicPautaController` para renderizar dados reais no servidor; antes, `WelcomeController::consultarPauta` devolvia apenas o HTML sem dados.

`PautaSearchService` delega no `EloquentPautaAduaneiraRepository`, com SQL parametrizado. Antes, a API `/pauta/busca` descartava o paginator, devolvia quantidade na página como total e omitia paginação; o cache também ignorava tamanho da página. Sugestões por descrição podiam corresponder a todos os códigos quando a normalização ficava vazia. Estes problemas foram corrigidos; a ordenação acrescenta ID para estabilidade em códigos repetidos. `%`, `_` e barras na descrição são pesquisados literalmente. Não se carregou todo o catálogo no browser.

`app/Models/PautaAduaneira.php` e migration original: código textual, descrição, uq, rg/sadc/ua, iva/ieq, requisitos/observacao e timestamps. Não há versão normativa, fonte legal, vigência ou hierarquia formal. `updated_at` é actualização técnica do registo, não vigência nem prova de revisão da legislação. Não se criaram categorias a partir de comprimento/pontos/prefixos. Os antigos cartões de capítulos/posições e atalhos com categorias presumidas deixaram de ser apresentados na página; endpoints legados de estatísticas continuam disponíveis.

Os casts numéricos do modelo podiam converter informação textual de taxa em zero. A apresentação pública agora lê os valores originais: null/vazio aparece como ausência; zero continua zero; texto como `N/A` é conservado sem interpretar legalmente o seu significado. Nenhum valor foi gravado ou recalculado. A migration original tinha defaults `'0'` em vários campos: a interface não consegue demonstrar se estes defaults foram revistos; não os reclassifica automaticamente como ausência/isencão.

Não há simulador na consulta pública; simuladores internos existentes em Livewire e seus cálculos permaneceram intactos.

## Implementado

Título e apresentação pedidos, pesquisa com label e explicação, modo descrição/código/automático, quantidade por página, limpeza, resultados reais, total distinto de quantidade da página e paginação. Sem resultados, base sem dados, página fora do intervalo, loading, erro de ligação e parâmetros inválidos são estados distintos. Filtros reiniciam página e mantêm valores/URL.

Sugestões reais, debounce de 300 ms, até oito entradas, IDs explícitos, combobox/listbox, setas, Enter, Escape e `aria-activedescendant`. Seleccionar uma sugestão confirma a pesquisa actual antes de abrir a identidade escolhida; não é uma classificação automática.

Selecção por links reais (também funcionam sem JS), detalhe por ID e código, marcos de fonte honestos, textos técnicos escapados, foco inicial, Escape, contenção do Tab e retorno do foco. Modal com scroll para conteúdo longo; cartões no móvel, sem tabela larga. Os alvos novos têm cerca de 44 px, labels e foco visível. Estilos isolados na página; contraste do texto sobre o fundo existente reforçado. Cabeçalho/rodapé não foram reconstruídos.

Renderização no servidor mantém pesquisa, paginação, detalhe e guia disponíveis sem JavaScript. JS apenas acrescenta actualizações sem recarregar, URL, sugestões e protecção por AbortController/sequências contra respostas atrasadas. Não há Livewire/Alpine nesta view pública; nenhuma dependência/listener desses frameworks foi duplicada. AOS/partículas são opcionais e respeitam movimento reduzido, como já ajustado na evolução anterior. Resultados novos não são reanimados por tecla.

## Guia e critérios

Reutiliza `app/Application/Marketplace/MarketplaceExperience.php`, sem ranking paralelo. Mostra até três perfis públicos elegíveis depois de uma selecção concreta, lateral no desktop >=1000 px, abaixo no móvel. A página da pesquisa pautal não é a página do guia: o servidor mostra os primeiros três candidatos independentemente do número da página de mercadorias.

Mesmo contrato de publicação e autorizações anterior: perfis ocultos por defeito, prestador explicitamente aderente e empresa activa/elegível; nenhum perfil real foi publicado ou alterado. Só nome/localização autorizados e agregados do módulo próprio são lidos para o guia. Visibilidade é verificada em cada pedido e as respostas do guia/página com perfis têm `Cache-Control: no-store`.

Experiência exacta antecede especialidade declarada; depois meses activos, actividade recente, operações distintas e ID estável. Janela configurável inicial de 12 meses completos. Processos finalizados distintos com datas/snapshots elegíveis; linhas repetidas não duplicam operações. Nenhuma soma com licenciamentos. A configuração/revisão de identidade pautal e os comandos de adesão/actualização continuam os descritos em `docs/pauta-marketplace-2026-10-05.md`.

Sem histórico autorizado: explica a limitação e oferece marketplace. Declarações de especialidade são rotuladas separadamente. Falha de consulta do guia não bloqueia a pauta. Sem rota real de perfil detalhado, não há link fictício “Ver perfil”. Não há notas, estrelas, selos ou garantias. Sem hierarquia/versão confirmadas, não se amplia categoria. Tempos permanecem indisponíveis: faltam marcos/contextos históricos fiáveis; nenhuma mediana ou P25–P75 é inventada.

## Contratos URL/API

Página pública:

* `?q=arroz&tipo=descricao&per_page=20&page=2`: consulta por texto; q até 100 caracteres, mínimo 2 se preenchido; modo `auto`, `codigo`, `descricao` (API legada também aceita `ambos`). `per_page` 1–100; página positiva até 10000. A UI oferece 20/50/100 e conserva tamanhos válidos recebidos por URL.
* `?q=0203.11&tipo=codigo`: código como texto, dígitos e pontos entre grupos, incluindo zeros iniciais. Também se aceita código sem pontos. É pesquisa parcial, exige selecção explícita de um resultado.
* `?pauta_id=123&codigo=0203.11.00`: identidade seleccionada; ID e código devem coincidir, código sozinho apenas se unívoco. Pode coexistir com a pesquisa/página. `codigo` nesta página é contexto de selecção, `q` é pesquisa; não são misturados.
* `mercadoria_descricao`: contexto opcional até 1000 caracteres, comparado com a descrição real do ID. Valor obsoleto/tamperado é rejeitado; não altera resultados nem a classificação. Descrição vem sempre do catálogo.
* `months`, `location`, `recurrence`: filtros existentes do guia, preservados no retorno, validados pelo mesmo módulo.

APIs existentes:

* `/api/v1/pauta`: mantém código/descrição e filtros legados parametrizados; acrescenta q/tipo e paginação correcta. Defaults legados da API conservados em 50, enquanto a UI pede 20. Cache 5 minutos, chave com critérios, página e tamanho. Não é garantia de actualidade normativa.
* `/api/v1/pauta/busca`: `q`, `tipo`, `page`, `per_page` ou `limit` legado. Meta: `total`, `shown`, `per_page`, `current_page`, `last_page`, `catalogue_empty`; total é SQL, não quantidade da página.
* `/api/v1/pauta/sugestoes?termo=...&tipo=...`: no máximo oito sugestões reais da primeira página, com ID, código e descrição. Cache diferencia termo e tipo.
* `/api/v1/pauta/detalhes/{id}?codigo=...`: novo contrato mínimo; fresco/sem cache, com identidade, campos pautais originais e metadata de fonte. Código divergente 422, ID ausente 404.
* `/api/v1/pauta/{codigo}`: legado mantém código normalizado; não encontrado 404, ambiguidade 409 em vez de escolher silenciosamente uma identidade. O campo legado `nivel` continua por compatibilidade, identificado como `nivel_confirmado=false`; não representa hierarquia confirmada nem é apresentado como categoria.
* `iva` directo e `link` da listagem mantidos; taxas ausentes agora são null, valores textuais permanecem texto. Consumidores não devem coagir null a zero. `impostos` continua a conter iva/ieq. `fonte` distingue os campos sem suporte e a actualização técnica do registo.

Erros de validação são 422. Erros de base na consulta pública são 503 genéricos, sem SQL/stack/credenciais no conteúdo. Não se usa raw HTML das descrições em JS; Blade também escapa conteúdo e o bootstrap JSON usa escapes seguros.

Continuidade:

* `/mercado/guia` usa ID/código e descrição validada. Transmite ao marketplace `pauta_q`, `pauta_tipo`, `pauta_page`, `pauta_per_page` para o retorno, sem os confundir com pesquisa/página própria do marketplace.
* `/mercado?...&mercadoria_descricao=...&pauta_q=arroz&pauta_tipo=descricao&pauta_page=2&pauta_per_page=20`: descrição validada contra o ID, resumo real, link de retorno reconstrói q/tipo/page/per_page. Filtros avançados conservam este contexto; pesquisa por outra mercadoria começa nova selecção. Não foi redesenhado o marketplace.

## Ficheiros

Novo serviço público `PublicPautaCatalogue`, `WebPage/PublicPautaController`, `resources/js/pauta-consulta.js`, partials `pauta-content`, `pauta-results`, `pauta-detail`, teste `PublicPautaTest`, preview/QA `pauta-consulta-preview.php` / `verify-pauta-consulta.cjs` e este documento.

Actualizados view da consulta, `pauta-marketplace.js/css`, API pautal, repositório de pesquisa, rotas web/API, contexto mínimo de `MarketplaceController`, serviço partilhado (descrição e paginação explícita), partial de retorno do marketplace e runner/fixtures de testes. Helpers da revisão anterior foram adaptados ao novo contrato. Nenhuma view da landing foi editada.

## Comandos Windows e segurança dos testes

```powershell
php tests/run-marketplace-isolated.php
npm.cmd run build
php scripts/pauta-consulta-preview.php
php -S 127.0.0.1:8126 -t public scripts/pauta-consulta-preview.php
# Noutro terminal, usando runtime/dependências locais disponíveis:
$env:PLAYWRIGHT_MODULE='C:\Users\USER\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\node_modules\playwright'
$env:LANDING_BROWSER='C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
node scripts/verify-pauta-consulta.cjs
```

Antes de escrita, o runner confirma APP_ENV=testing, base local de testes preparada e host loopback, sem imprimir credenciais. Cria schema mínimo MySQL descartável com nome aleatório; transacções revertem fixtures. Não migra/apaga dados da aplicação. Emails/HTTP externos bloqueados e discos falsos pelo harness. Capturas não usam pessoas/clientes/operações reais; contactos da fixture foram substituídos. Browser não acede a endpoints operacionais nem submete pagamentos/contactos.

O QA usa uma cópia exacta da folha Tailwind 2.2.19 já usada pela página, guardada em `storage/app/pauta-marketplace-qa/tailwind-2.2.19.css`, interceptada só no teste. Se ausente, obtenha-a depois de renderizar o preview, com `Invoke-WebRequest -Uri 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' -OutFile 'storage/app/pauta-marketplace-qa/tailwind-2.2.19.css'`. Não foi acrescentada uma dependência de produção. AOS/partículas externos bloqueados deliberadamente para verificar fallback. Fontes/ícones usam fallback nos testes; saúde real dos CDNs não é homologada aqui.

## Resultados e limites

Suite SQL partilhada: 19 testes / 261 asserções sem falhas, com quatro deprecações preexistentes de PDO no PHP 8.5. Cobertura: total/páginas/cache, código/zeros, sugestões, null/zero/texto, identidade ambígua, metadata, falta de dados vs falta de resultados, validação, escaping/SQL parametrizado, erros sem detalhes internos, retorno ao marketplace, guia independente da paginação pautal e testes anteriores de estados/datas/deduplicação/publicação/privacidade/ranking.

Build Vite passou; mantém o aviso existente de Browserslist desactualizado. Pint e sintaxe passaram nos ficheiros PHP revistos. QA Playwright/Edge com fixtures anónimas passou em 320, 360, 390, 768 e 1440 px: paginação/limpeza, sugestões por teclado, selecção, taxas, estados vazios/erro, respostas antigas descartadas, contexto, Escape, movimento reduzido e consulta sem JavaScript. Sem erros JavaScript ou falhas nos assets Vite observados. O teste sem JavaScript usa movimento reduzido. São fixtures servidas localmente, com APIs interceptadas; não constituem homologação do catálogo real nem de CDNs externos.

Relatório em `storage/app/pauta-consulta-qa/report.json`. Capturas anónimas `consulta-{largura}.png` (página completa), `consulta-top-{largura}.png` (primeiro ecrã), `detalhe-{largura}.png` e `guia-{largura}.png` (guia abaixo do detalhe em ecrãs menores). Revisão visual confirmou blocos legíveis sem overflow, guia lateral no desktop e abaixo em móvel, e correcção do contraste do caminho da página isolada de cabeçalho/rodapé. O preview serve o logótipo original do cabeçalho, sem o alterar.

Pendente por falta de dados: fonte/versão normativa/vigência/hierarquia verificáveis; revisão da semântica dos defaults zero; duração de despacho com marcos/contextos comparáveis; perfis detalhados/contactos públicos e avaliações reais. Nenhuma dessas capacidades foi simulada na aplicação.

Não verificado: homogeneidade/actualidade do catálogo real, carga/EXPLAIN com volume real, importações/integrações externas, produção, envio de contactos ou contas reais. Não há novo ciclo Livewire nesta página a testar; os simuladores internos não foram alterados.
