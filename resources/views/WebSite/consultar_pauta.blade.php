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
  <header class="header"><div class="header-inner">
    <div class="brand-cluster"><img class="brand-logo" src="{{ asset('dist/img/LOGIGATE.png') }}" alt=""><a class="brand" href="{{ route('home') }}">Logi<span>Gate</span><small>SISTEMA ADUANEIRO INTELIGENTE</small></a></div>
    <nav id="nav" aria-label="Navegação principal">
      <a href="{{ route('home') }}">Início</a>
      <a class="active" aria-current="page" href="{{ route('consultar.pauta') }}">Pauta aduaneira</a>
      <a href="{{ route('marketplace') }}">Marketplace</a>
      <a href="{{ route('consultar.licenciamento') }}">Licenciamento</a>
      @if (Route::has('login'))
        @guest <a href="{{ route('register') }}">Registar</a> @endguest
      @endif
    </nav>
    @if (Route::has('login'))
      @auth <a class="access" href="{{ url('/dashboard') }}">Dashboard ↗</a>
      @else <a class="access" href="{{ route('login') }}">Acesso ↗</a> @endauth
    @endif
    <button class="menu-btn" id="mobile-menu" aria-label="Abrir menu" aria-expanded="false" aria-controls="nav">☰</button>
  </div></header>
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
  <footer class="site-footer"><div class="wrap"><div class="footer-grid">
    <div><a class="footer-brand" href="{{ route('home') }}">Logi<span>Gate</span></a><p>Consulta gratuita da pauta aduaneira angolana. Confirme o enquadramento da sua mercadoria com um despachante oficial.</p></div>
    <div><h3>Links rápidos</h3><ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('consultar.pauta') }}">Pauta aduaneira</a></li><li><a href="{{ route('marketplace') }}">Marketplace</a></li><li><a href="#contactos">Contactos</a></li></ul></div>
    <div><h3>Legal</h3><ul><li><a href="#">Termos de Uso</a></li><li><a href="#">Política de Privacidade</a></li><li><a href="#">Aviso Legal</a></li></ul></div>
    <div id="contactos"><h3>Suporte</h3><ul><li><a href="tel:+244948242262">+244 948 242 262</a></li><li><a href="mailto:geral@hongayetu.com">geral@hongayetu.com</a></li></ul></div>
  </div><p class="copyright">&copy; 2024 Logigate by Hongayetu LDA. Todos os direitos reservados.</p></div></footer>
  <noscript><p class="wrap">Ative o JavaScript para pesquisar a pauta e explorar os resultados.</p></noscript>
</body>
</html>
