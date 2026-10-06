<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#061f40">
    <title>Encontre um despachante | LogiGate</title>
    <meta name="description" content="Pesquise mercadorias na pauta e encontre despachantes aderentes por histórico público autorizado no LogiGate.">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/marketplace.css', 'resources/js/marketplace.js'])
</head>
<body class="marketplace-page">
    <a class="mp-skip" href="#conteudo">Saltar para o conteúdo</a>
    <header class="mp-header">
        <div class="mp-shell">
            <div class="mp-header-row">
                <a class="mp-logo" href="{{ route('home') }}" aria-label="LogiGate — página inicial"><img src="{{ asset('dist/img/LandingPage/logo-horizontal.webp') }}" width="720" height="201" alt="LOGIGATE"></a>
                <nav class="mp-desktop-nav" aria-label="Navegação principal">
                    <a href="{{ route('home') }}#plataforma">Plataforma</a>
                    <a href="{{ route('marketplace') }}" aria-current="page">Encontrar despachante</a>
                    <a href="{{ route('consultar.pauta') }}">Pauta Aduaneira</a>
                    <a href="{{ route('home') }}#planos">Planos</a>
                </nav>
                <div class="mp-access"><a href="{{ route('cliente.portal.login') }}">Portal do cliente</a><a class="mp-button" href="{{ route('login') }}">Entrar na aplicação <span aria-hidden="true">↗</span></a></div>
                <button class="mp-menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="mp-mobile-menu"><span aria-hidden="true">☰</span></button>
            </div>
            <nav id="mp-mobile-menu" class="mp-mobile-nav" aria-label="Menu móvel">
                <a href="{{ route('home') }}#plataforma">Plataforma</a>
                <a href="{{ route('marketplace') }}" aria-current="page">Encontrar despachante</a>
                <a href="{{ route('consultar.pauta') }}">Pauta Aduaneira</a>
                <a href="{{ route('home') }}#planos">Planos</a>
                <a class="mp-portal-link" href="{{ route('cliente.portal.login') }}">Portal do cliente</a>
                <a class="mp-button" href="{{ route('login') }}">Entrar na aplicação</a>
            </nav>
        </div>
    </header>
    <main id="conteudo" tabindex="-1">
        <section class="mp-hero" aria-labelledby="marketplace-title">
            <div class="mp-shell">
                <nav class="mp-breadcrumb" aria-label="Caminho da página"><a href="{{ route('home') }}">Início</a><span aria-hidden="true">/</span><span aria-current="page">Marketplace</span></nav>
                <div class="mp-hero-copy"><p class="mp-eyebrow">Marketplace LogiGate · Angola</p><h1 id="marketplace-title">Encontre um despachante</h1><p>Comece pela mercadoria. Consulte prestadores aderentes e o histórico público autorizado no LogiGate.</p></div>
            </div>
        </section>
        <section class="mp-directory" aria-labelledby="directory-title">
            <div class="mp-shell">
                <div class="mp-result-header"><h2 id="directory-title">Catálogo de despachantes</h2><span class="mp-public-label">Consulta pública</span></div>
                @include('WebSite.partials.marketplace-directory')
            </div>
        </section>
        <section class="mp-guidance" aria-labelledby="guidance-title">
            <div class="mp-shell"><h2 id="guidance-title">Encontre o acesso de que precisa.</h2><div class="mp-guidance-grid">
                <article><span>01 / FERRAMENTA PÚBLICA</span><h3>Conheça as mercadorias da sua operação</h3><p>A Pauta Aduaneira permite pesquisar por descrição ou código pautal.</p><a class="mp-link" href="{{ route('consultar.pauta') }}">Consultar pauta →</a></article>
                <article><span>02 / PORTAL DO CLIENTE</span><h3>Já tem uma operação em acompanhamento?</h3><p>Utilize o acesso concedido pela sua empresa ou despachante para consultar os processos e documentos disponibilizados.</p><a class="mp-link" href="{{ route('cliente.portal.login') }}">Aceder ao portal →</a></article>
            </div></div>
        </section>
    </main>
    <footer class="mp-footer"><div class="mp-shell"><div class="mp-footer-grid">
        <div><a class="mp-logo" href="{{ route('home') }}" aria-label="LogiGate — página inicial"><img src="{{ asset('dist/img/LandingPage/logo-light.webp') }}" width="720" height="182" loading="lazy" alt="LOGIGATE"></a><p>Gestão aduaneira para empresas e despachantes em Angola.</p></div>
        <div><h2>Encontre o seu caminho</h2><nav aria-label="Ligações do rodapé"><a href="{{ route('home') }}">Página inicial</a><a href="{{ route('login') }}">Entrar na aplicação</a><a href="{{ route('cliente.portal.login') }}">Portal do cliente</a><a href="{{ route('consultar.pauta') }}">Pauta Aduaneira</a></nav></div>
        <div><h2>Novidades do LogiGate</h2><form class="mp-newsletter" action="{{ route('newsletter.subscribe') }}" method="POST">@csrf<label for="newsletter-email">Email para a newsletter</label><div class="mp-newsletter-row"><input id="newsletter-email" name="email" type="email" autocomplete="email" maxlength="255" placeholder="O seu email" aria-describedby="newsletter-feedback" required><button type="submit" aria-label="Subscrever newsletter">→</button></div><p id="newsletter-feedback" class="mp-feedback" role="status" aria-live="polite" aria-atomic="true"></p></form></div>
    </div><div class="mp-footer-bottom"><span>&copy; {{ date('Y') }} LogiGate by Hongayetu LDA. Todos os direitos reservados.</span><span>Luanda, Angola</span></div></div></footer>
</body>
</html>
