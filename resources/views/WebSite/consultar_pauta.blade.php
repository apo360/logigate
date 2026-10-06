<!doctype html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LogiGate | Consulta Pauta Aduaneira — Angola</title>
  <meta name="description" content="Consulte códigos NCM/SH, impostos e requisitos da pauta aduaneira angolana. Continue para o marketplace com a sua mercadoria selecionada.">
  <meta name="keywords" content="pauta aduaneira Angola, consulta NCM, classificação fiscal, impostos importação, código SH, despacho aduaneiro">
  <meta property="og:title" content="LogiGate — Consulta Pauta Aduaneira">
  <meta property="og:description" content="Consulte gratuitamente a pauta aduaneira angolana">
  <meta property="og:image" content="{{ url('/images/og-pauta.jpg') }}">
  <meta property="og:url" content="{{ route('consultar.pauta') }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="LogiGate — Pauta Aduaneira Angola">
  <meta name="twitter:description" content="Consulta gratuita da pauta aduaneira">
  <meta name="theme-color" content="#0047AB">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}">
  <link rel="stylesheet" href="{{ asset('css/website/pauta-consulta.css') }}">
  <script defer src="{{ asset('js/website/pauta-core.js') }}"></script>
  <script defer src="{{ asset('js/website/pauta-consulta.js') }}"></script>
</head>
<body data-pauta-api-base="{{ url('/api/v1') }}" data-consulta-url="{{ route('consultar.pauta') }}" data-marketplace-url="{{ route('marketplace') }}" data-simulador-url="{{ route('pauta.simulador') }}">
  <a class="skip" href="#main-content">Saltar para o conteúdo principal</a>
  @include('WebSite.partials.menu_website')
  <div class="source-strip"><div class="wrap"><span>Pauta aduaneira · Consulta de informações para Angola</span><a href="{{ route('marketplace') }}">Explorar o marketplace ↗</a></div></div>
  <main id="main-content">
    <section class="hero"><div class="wrap hero-inner">
      <div class="breadcrumb"><a href="{{ route('home') }}">Início</a><span aria-hidden="true">/</span><span>Consulta aduaneira</span></div>
      <div class="hero-title"><div><p class="eyebrow">PAUTA ADUANEIRA · ANGOLA</p><h1>A sua mercadoria.<br><span>O próximo passo, mais claro.</span></h1><p class="hero-copy">Consulte um código ou descrição. Explore o resultado<br class="desktop-br"> e encontre apoio para continuar a sua operação.</p></div><div class="hero-aside"><span class="tiny-label">DA CONSULTA À OPERAÇÃO</span><div><span class="step-circle">01</span><p>Consultar a pauta</p></div><div><span class="step-circle">02</span><p>Entender o resultado</p></div><div><span class="step-circle">03</span><p>Encontrar apoio</p></div></div></div>
      <div class="search-surface"><form id="search-form" autocomplete="off">
        <label for="searchInput">Descrição da mercadoria ou código pautal</label>
        <div class="search-row"><span class="search-icon" aria-hidden="true">⌕</span><input id="searchInput" type="text" name="termo" maxlength="100" placeholder="Ex.: 0203.11.00, carne, máquinas…" aria-describedby="search-hint" aria-controls="suggestions" aria-expanded="false"><button id="searchButton" type="submit" class="primary">Consultar <span aria-hidden="true">→</span></button></div>
        <div id="suggestions" class="suggestions" hidden aria-label="Sugestões de mercadorias"></div>
        <div class="search-bottom"><span id="search-hint">Pesquisa por código NCM/SH ou descrição</span><button id="clear-search" type="button">Limpar pesquisa</button></div>
      </form></div>
      <div class="quick-filter-row"><span>Por capítulo</span><div id="chapter-filters"></div></div>
    </div></section>
    <section class="wrap workspace" aria-label="Resultados e próximos passos">
      <div class="content-column"><div class="section-head"><div><p class="eyebrow">CONSULTA DA PAUTA</p><h2 id="results-heading">Encontre a sua mercadoria</h2></div><span id="results-count" class="count-tag"></span></div>
        <div id="results" aria-live="polite" aria-busy="false"></div>
        <div id="pagination" class="pagination" aria-label="Paginação dos resultados"></div>
        <div class="guidance-card"><span class="preserved-icon" aria-hidden="true">▤</span><div><strong>Prepare os próximos passos da sua operação</strong><p>Selecione uma mercadoria para consultar o detalhe e seguir com o contexto para o marketplace.</p></div></div>
        <details class="dataset-info"><summary>Estatísticas da pauta</summary><p id="statistics">A carregar estatísticas…</p></details>
      </div>
      <aside id="guide" class="guide" aria-label="Guia contextual da mercadoria"></aside>
    </section>
  </main>
  <dialog id="detailModal" aria-labelledby="modalTitle"><div class="dialog-top"><span class="eyebrow">DETALHE DA MERCADORIA</span><button data-close aria-label="Fechar detalhes">×</button></div><h2 id="modalTitle"></h2><div id="modalContent"></div></dialog>
  @include('WebSite.partials.footer_website')
  <noscript><p class="wrap">Ative o JavaScript para pesquisar a pauta e explorar os resultados.</p></noscript>
</body>
</html>
