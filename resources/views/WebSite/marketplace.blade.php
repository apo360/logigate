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
    @include('WebSite.partials.menu_website')
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
    @include('WebSite.partials.footer_website')
</body>
</html>
