<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#061f40">
    <title>LogiGate | Gestão aduaneira em Angola</title>
    <meta name="description" content="Centralize processos, licenciamentos, documentos e clientes. Conheça o LogiGate, compare planos e descubra as ferramentas públicas de gestão aduaneira em Angola.">
    <meta property="og:title" content="LogiGate | A sua operação aduaneira, organizada num só lugar.">
    <meta property="og:description" content="Gestão aduaneira para empresas e despachantes em Angola.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_AO">
    <meta property="og:image" content="{{ asset('dist/img/LandingPage/port-desktop-1600.webp') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
</head>
<body class="lg-landing">
    <a class="skip-link" href="#conteudo">Saltar para o conteúdo</a>
    <header class="site-header">
        <div class="shell">
            <div class="header-row">
                <a class="brand" href="{{ route('home') }}" aria-label="LogiGate — página inicial"><img src="{{ asset('dist/img/LandingPage/logo-horizontal.webp') }}" width="720" height="201" alt="LOGIGATE"></a>
                <nav class="desktop-nav" aria-label="Navegação principal">
                    <a href="#plataforma">Plataforma</a>
                    <a href="{{ route('marketplace') }}">Encontrar despachante</a>
                    <a href="{{ route('consultar.pauta') }}">Pauta Aduaneira</a>
                    <a href="#planos">Planos</a>
                </nav>
                <div class="desktop-access">
                    <a class="portal-link" href="{{ route('cliente.portal.login') }}">Portal do cliente</a>
                    <a class="button primary" href="{{ route('login') }}">Entrar na aplicação <span aria-hidden="true">↗</span></a>
                </div>
                <button class="menu-toggle" type="button" aria-controls="mobile-menu" aria-expanded="false" aria-label="Abrir menu"><span aria-hidden="true">☰</span></button>
            </div>
            <nav id="mobile-menu" class="mobile-nav" aria-label="Menu móvel">
                <a href="#plataforma">Plataforma</a>
                <a href="{{ route('marketplace') }}">Encontrar despachante</a>
                <a href="{{ route('consultar.pauta') }}">Pauta Aduaneira</a>
                <a href="#planos">Planos</a>
                <a class="portal-link" href="{{ route('cliente.portal.login') }}">Portal do cliente</a>
                <a class="button primary" href="{{ route('login') }}">Entrar na aplicação</a>
            </nav>
        </div>
    </header>
    <main id="conteudo" tabindex="-1">
        <section id="inicio" class="hero" aria-labelledby="hero-title">
            <div class="shell hero-grid">
                <div class="hero-copy">
                    <span class="eyebrow">Gestão aduaneira para empresas e despachantes em Angola</span>
                    <h1 id="hero-title">A sua operação aduaneira, <span>organizada num só lugar.</span></h1>
                    <p>Centralize processos, licenciamentos, documentos e clientes com o LogiGate. Acompanhe o trabalho da sua equipa e mantenha a informação organizada ao longo de cada operação.</p>
                    <div class="hero-actions">
                        <a class="button primary" href="#planos">Ver planos <span aria-hidden="true">→</span></a>
                        <a class="button secondary" href="#plataforma">Explorar a plataforma</a>
                    </div>
                    <div class="hero-footnote">Da informação dispersa a uma operação organizada.</div>
                </div>
                <figure class="hero-art">
                    <picture>
                        <source media="(max-width: 899px)" srcset="{{ asset('dist/img/LandingPage/port-mobile-480.webp') }} 480w, {{ asset('dist/img/LandingPage/port-mobile-800.webp') }} 800w" sizes="100vw" width="800" height="1200">
                        <img src="{{ asset('dist/img/LandingPage/port-desktop-1600.webp') }}" srcset="{{ asset('dist/img/LandingPage/port-desktop-960.webp') }} 960w, {{ asset('dist/img/LandingPage/port-desktop-1600.webp') }} 1600w" sizes="(min-width: 900px) 52vw, 100vw" width="1672" height="941" fetchpriority="high" alt="Ilustração conceptual de um navio de carga, gruas e uma frente urbana costeira inspirada em Luanda.">
                    </picture>
                    <figcaption>Ilustração conceptual · contexto portuário</figcaption>
                </figure>
            </div>
        </section>
        <div class="hero-bottom"><div class="shell"><p><strong>Uma plataforma. Três percursos.</strong> Gestão empresarial, ferramentas públicas e acesso do cliente.</p><a class="text-link" href="#ferramentas">Conhecer as ferramentas públicas <span aria-hidden="true">↗</span></a></div></div>

        <section id="plataforma" class="section platform" aria-labelledby="platform-title">
            <div class="shell">
                <div class="platform-heading">
                    <div><span class="eyebrow">A plataforma, por dentro</span><h2 id="platform-title">Menos dispersão.<br>Mais clareza na operação.</h2></div>
                    <p>Dos processos aos clientes, encontre a informação de que precisa para acompanhar o trabalho diário. Explore as principais áreas do LogiGate.</p>
                </div>
                <nav class="demo-tabs" aria-label="Áreas da demonstração">
                    <a id="tab-processos" href="#demo-processos" aria-controls="demo-processos" data-demo="processos">Processos</a>
                    <a id="tab-licenciamentos" href="#demo-licenciamentos" aria-controls="demo-licenciamentos" data-demo="licenciamentos">Licenciamentos</a>
                    <a id="tab-clientes" href="#demo-clientes" aria-controls="demo-clientes" data-demo="clientes">Clientes</a>
                </nav>
                <div class="demo-frame">
                    <aside class="demo-sidebar" aria-hidden="true">
                        <div class="demo-logo">LOGIGATE</div><p>Área de trabalho</p><p data-demo-nav="processos" class="active">▤ &nbsp; Processos</p><p data-demo-nav="licenciamentos">▧ &nbsp; Licenciamentos</p><p data-demo-nav="clientes">♙ &nbsp; Clientes</p><p>▱ &nbsp; Documentos</p><small>Empresa de demonstração<br>Dados fictícios</small>
                    </aside>
                    <div class="demo-body">
                        <div class="demo-topbar"><strong>Gestão aduaneira</strong><span class="demo-label">Prévia · dados fictícios</span></div>
                        <section id="demo-processos" class="demo-panel" aria-labelledby="processos-title">
                            <h3 id="processos-title">Processos</h3><p>Consulte referências, tipos e estados para acompanhar cada operação.</p>
                            <div class="demo-metrics"><div><strong>03</strong><span>Processos na prévia</span></div><div><strong>02</strong><span>Em acompanhamento</span></div><div><strong>01</strong><span>Finalizado</span></div></div>
                            <table class="preview-table"><caption class="sr-only">Exemplos fictícios de processos</caption><thead><tr><th>Processo</th><th>Tipo</th><th>Estado</th><th class="optional-col">Origem</th><th class="optional-col">Abertura</th></tr></thead><tbody>
                                <tr><td>PR / 2026 / 001</td><td>Importação</td><td><span class="badge">Em análise</span></td><td class="optional-col">Portugal</td><td class="optional-col">01/10/2026</td></tr>
                                <tr><td>PR / 2026 / 002</td><td>Importação</td><td><span class="badge amber">Aguardando documentos</span></td><td class="optional-col">China</td><td class="optional-col">02/10/2026</td></tr>
                                <tr><td>PR / 2026 / 003</td><td>Exportação</td><td><span class="badge green">Finalizado</span></td><td class="optional-col">Angola</td><td class="optional-col">03/10/2026</td></tr>
                            </tbody></table>
                        </section>
                        <section id="demo-licenciamentos" class="demo-panel" aria-labelledby="licenciamentos-title">
                            <h3 id="licenciamentos-title">Licenciamentos</h3><p>Reúna referências, clientes e mercadorias e consulte o estado do licenciamento.</p>
                            <div class="demo-metrics"><div><strong>03</strong><span>Licenciamentos</span></div><div><strong>01</strong><span>Pendente</span></div><div><strong>02</strong><span>Gerados ou processados</span></div></div>
                            <table class="preview-table"><caption class="sr-only">Exemplos fictícios de licenciamentos</caption><thead><tr><th>Cliente / referência</th><th>Descrição</th><th>Estado</th><th class="optional-col">Origem</th></tr></thead><tbody>
                                <tr><td>Empresa Exemplo A<br>LIC / 001</td><td>Equipamento industrial</td><td><span class="badge amber">Pendente</span></td><td class="optional-col">Portugal</td></tr>
                                <tr><td>Empresa Exemplo B<br>LIC / 002</td><td>Material eléctrico</td><td><span class="badge">Gerado</span></td><td class="optional-col">China</td></tr>
                                <tr><td>Empresa Exemplo C<br>LIC / 003</td><td>Produtos alimentares</td><td><span class="badge green">Processado</span></td><td class="optional-col">Brasil</td></tr>
                            </tbody></table>
                        </section>
                        <section id="demo-clientes" class="demo-panel" aria-labelledby="clientes-title">
                            <h3 id="clientes-title">Clientes</h3><p>Consulte os clientes e os processos e licenciamentos associados a cada um.</p>
                            <div class="demo-metrics"><div><strong>03</strong><span>Clientes na prévia</span></div><div><strong>02</strong><span>Importadores</span></div><div><strong>01</strong><span>Exportador</span></div></div>
                            <table class="preview-table"><caption class="sr-only">Exemplos fictícios de clientes</caption><thead><tr><th>Cliente</th><th>Tipo</th><th>Processos</th><th class="optional-col">Licenciamentos</th></tr></thead><tbody>
                                <tr><td>Empresa Exemplo A</td><td>Importador</td><td>1</td><td class="optional-col">1</td></tr><tr><td>Empresa Exemplo B</td><td>Importador</td><td>1</td><td class="optional-col">1</td></tr><tr><td>Empresa Exemplo C</td><td>Exportador</td><td>1</td><td class="optional-col">1</td></tr>
                            </tbody></table>
                        </section>
                    </div>
                </div>
                <p class="demo-caption">Demonstração em HTML baseada nas áreas da aplicação. Todos os dados são fictícios; esta prévia não executa operações.</p>
                <p class="platform-support">Cada operação reúne documentos, intervenientes e etapas que precisam de acompanhamento. O LogiGate reúne essa informação numa plataforma de gestão aduaneira, para ajudar a sua equipa a consultar o que precisa, acompanhar o andamento dos processos e coordenar o trabalho diário.</p>
            </div>
        </section>

        <section id="ferramentas" class="section" aria-labelledby="tools-title">
            <div class="shell"><div class="intro"><span class="eyebrow">Ferramentas públicas</span><h2 id="tools-title">O seu próximo passo<br>pode começar aqui.</h2><p>Explore as ferramentas públicas, sem criar uma conta na aplicação.</p></div>
                <div class="public-tools" style="margin-top:40px">
                    <article class="tool"><span class="tool-number">01 / MARKETPLACE</span><h3>Encontre um despachante</h3><p>Pesquise por nome, especialidade ou localização no marketplace do LogiGate.</p><a class="text-link" href="{{ route('marketplace') }}">Encontrar despachante <span aria-hidden="true">↗</span></a></article>
                    <article class="tool"><span class="tool-number">02 / PAUTA ADUANEIRA</span><h3>Consulte a Pauta Aduaneira</h3><p>Pesquise mercadorias por descrição ou código pautal.</p><a class="text-link" href="{{ route('consultar.pauta') }}">Consultar pauta <span aria-hidden="true">↗</span></a></article>
                </div>
            </div>
        </section>
        <section class="section steps-section" aria-labelledby="steps-title">
            <div class="shell"><span class="eyebrow">Como começar</span><h2 id="steps-title">Da escolha do plano<br>ao acesso à sua operação.</h2>
                <ol class="steps"><li><span class="step-number">01</span><h3>Escolher plano</h3><p>Compare as funcionalidades, os limites e o período de cobrança para a sua empresa.</p></li><li><span class="step-number">02</span><h3>Cadastrar empresa</h3><p>Preencha os dados da empresa e do utilizador responsável no cadastro.</p></li><li><span class="step-number">03</span><h3>Activar acesso</h3><p>Nos planos pagos, conclua o pagamento e aguarde a confirmação para activar a subscrição. Nos planos identificados como gratuitos, a activação segue o fluxo gratuito disponível.</p></li></ol>
            </div>
        </section>
        <section id="planos" class="section plans-section" aria-labelledby="plans-title">
            <div class="shell"><div class="intro"><span class="eyebrow">Planos LogiGate</span><h2 id="plans-title">Escolha o plano para<br>a sua forma de trabalhar.</h2><p>Compare as condições disponíveis. Os valores correspondem ao período de cobrança seleccionado, em kwanzas.</p></div>
                <fieldset class="billing"><legend>Período de cobrança</legend>@foreach(\App\Models\Plano::MODALIDADES_PAGAMENTO as $cycle => $label)<label><input type="radio" name="billing" value="{{ $cycle }}" @checked($cycle === 'monthly')><span>{{ $label }}</span></label>@endforeach</fieldset>
                <div class="plan-grid" style="--plan-columns: {{ min(4, max(1, $planos->count())) }}">
                    @forelse($planos as $plano)
                        <article class="plan">
                            <h3>{{ $plano->nome }}</h3><p class="plan-description">{{ $plano->descricao }}</p>
                            <div class="plan-price" data-monthly="{{ $plano->preco_mensal }}" data-semestral="{{ $plano->preco_semestral }}" data-annual="{{ $plano->preco_anual }}">{{ $plano->preco_mensal === null ? 'Preço não disponível' : number_format((float) $plano->preco_mensal, 2, ',', '.') . ' AOA' }}</div><p class="cycle-label">por mês</p>
                            <ul>@forelse($plano->itemplano as $item)<li>{{ $item->item }}</li>@empty<li>Consulte o apoio para conhecer as funcionalidades deste plano.</li>@endforelse
                                @foreach(['limite_utilizadores' => 'Utilizadores', 'limite_processos' => 'Processos', 'limite_armazenamento_gb' => 'Armazenamento (GB)'] as $attribute => $label)
                                    @if($plano->{$attribute} !== null)<li>{{ $label }}: {{ $plano->{$attribute} }}</li>@endif
                                @endforeach
                            </ul>
                            <form method="GET" action="{{ route('register') }}"><input type="hidden" name="plano" value="{{ $plano->id }}"><input type="hidden" class="billing-cycle" name="modalidade" value="monthly"><button type="submit" class="button primary" @disabled($plano->preco_mensal === null)>Escolher plano <span aria-hidden="true">→</span></button></form>
                            <div class="plan-prices">Semestral: {{ $plano->preco_semestral === null ? 'não disponível' : number_format((float) $plano->preco_semestral, 2, ',', '.') . ' AOA' }} · Anual: {{ $plano->preco_anual === null ? 'não disponível' : number_format((float) $plano->preco_anual, 2, ',', '.') . ' AOA' }}. @if($plano->preco_semestral !== null)<a class="text-link" href="{{ route('register', ['plano' => $plano->id, 'modalidade' => 'semestral']) }}">Escolher semestral →</a>@endif @if($plano->preco_anual !== null)<a class="text-link" href="{{ route('register', ['plano' => $plano->id, 'modalidade' => 'annual']) }}">Escolher anual →</a>@endif</div>
                        </article>
                    @empty
                        <div class="empty-plans"><h3>Planos indisponíveis de momento</h3><p>Contacte a equipa para conhecer as condições de adesão.</p><a class="text-link" href="#contactos">Falar com a equipa →</a></div>
                    @endforelse
                </div><p class="plans-note">Tem dúvidas sobre o plano ou a activação? <a class="text-link" href="#contactos">Fale connosco →</a></p>
            </div>
        </section>
        <section class="section portal-section" aria-labelledby="portal-title">
            <div class="shell portal-grid"><div><span class="eyebrow">Portal do cliente</span><h2 id="portal-title">Acompanhe os seus processos</h2><p>Aceda ao portal para consultar os processos e documentos disponibilizados pela sua empresa ou despachante. O acesso é concedido por quem acompanha a sua operação.</p></div><div class="portal-aside"><a class="button" href="{{ route('cliente.portal.login') }}">Aceder ao portal <span aria-hidden="true">↗</span></a><small>Já é cliente de uma empresa ou despachante?<br>Utilize o acesso que lhe foi disponibilizado.</small></div></div>
        </section>
        <section id="faq" class="section" aria-labelledby="faq-title">
            <div class="shell faq-grid"><div><span class="eyebrow">Antes de começar</span><h2 id="faq-title">Perguntas frequentes</h2></div><div class="faq-list">
                <details><summary>Quem pode aderir ao LogiGate?</summary><p>Empresas e despachantes podem escolher um plano e cadastrar a empresa e o utilizador responsável. O portal do cliente tem um acesso separado.</p></details>
                <details><summary>Como escolho o plano e a modalidade?</summary><p>Na secção de planos, seleccione o período de cobrança e compare os preços, funcionalidades e limites. A escolha do plano encaminha para o cadastro com o plano e a modalidade seleccionados.</p></details>
                <details><summary>Como é activado o acesso?</summary><p>Nos planos pagos, o cadastro encaminha para o pagamento; a activação depende da confirmação. Quando o plano é identificado como gratuito pelo sistema, o fluxo permite a activação sem pagamento. Contacte o apoio se precisar de ajuda.</p></details>
                <details><summary>Preciso de criar uma conta para consultar a pauta?</summary><p>Não. A Pauta Aduaneira e o marketplace são ferramentas públicas. Pesquisar ou pedir uma proposta não cria automaticamente uma conta na aplicação.</p></details>
                <details><summary>Como obtenho acesso ao portal do cliente?</summary><p>O acesso é concedido pela empresa ou despachante que acompanha a sua operação. Utilize as credenciais disponibilizadas para entrar no portal do cliente; não existe cadastro público neste percurso.</p></details>
            </div></div>
        </section>
        <section id="contactos" class="section contact-section" aria-labelledby="contact-title">
            <div class="shell contact-grid"><div><span class="eyebrow">Vamos conversar</span><h2 id="contact-title">Conheça o LogiGate<br>com a nossa equipa.</h2><p style="margin-top:22px;color:var(--muted)">Conte-nos sobre a sua operação. Solicite uma demonstração ou esclareça dúvidas sobre planos e acesso.</p><div class="contact-info"><a href="tel:+244948242262">+244 948 242 262</a><a href="mailto:geral@hongayetu.com">geral@hongayetu.com</a><p>Luanda, Angola</p></div></div>
                <form id="contact-form" class="contact-form" action="{{ route('contact.send') }}" method="POST" data-json-form data-feedback="contact-feedback">
                    @csrf
                    <div class="form-grid"><div class="field"><label for="nome">Nome completo</label><input id="nome" name="nome" autocomplete="name" maxlength="255" required></div><div class="field"><label for="empresa">Empresa (opcional)</label><input id="empresa" name="empresa" autocomplete="organization" maxlength="255"></div></div>
                    <div class="form-grid"><div class="field"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" maxlength="255" required></div><div class="field"><label for="telefone">Telefone</label><input id="telefone" name="telefone" type="tel" autocomplete="tel" maxlength="20" required></div></div>
                    <div class="field"><label for="assunto">Como podemos ajudar?</label><select id="assunto" name="assunto" required><option value="">Seleccione um assunto</option><option value="demonstracao">Solicitar demonstração</option><option value="planos">Informação sobre planos</option><option value="pauta">Consulta da pauta</option><option value="outro">Outro</option></select></div>
                    <div class="field"><label for="mensagem">Mensagem</label><textarea id="mensagem" name="mensagem" rows="4" maxlength="5000" required></textarea></div><button class="button primary" type="submit">Enviar pedido <span aria-hidden="true">→</span></button><p class="form-hint">O pedido de contacto não cria uma conta.</p><p id="contact-feedback" class="form-feedback" role="status" aria-live="polite" aria-atomic="true"></p>
                </form>
            </div>
        </section>
    </main>
    <footer class="site-footer"><div class="shell"><div class="footer-grid"><div class="footer-brand"><a href="{{ route('home') }}" aria-label="LogiGate — página inicial"><img src="{{ asset('dist/img/LandingPage/logo-light.webp') }}" width="720" height="182" loading="lazy" alt="LOGIGATE"></a><p>Gestão aduaneira para empresas e despachantes em Angola.</p></div><div><h3>Encontre o seu caminho</h3><nav class="footer-links" aria-label="Ligações do rodapé"><a href="{{ route('login') }}">Entrar na aplicação</a><a href="{{ route('cliente.portal.login') }}">Portal do cliente</a><a href="{{ route('marketplace') }}">Encontrar despachante</a><a href="{{ route('consultar.pauta') }}">Pauta Aduaneira</a></nav></div><div><h3>Novidades do LogiGate</h3><form id="newsletter-form" action="{{ route('newsletter.subscribe') }}" method="POST" data-json-form data-feedback="newsletter-feedback">@csrf<label class="sr-only" for="newsletter-email">Email para a newsletter</label><div class="newsletter"><input id="newsletter-email" name="email" type="email" autocomplete="email" maxlength="255" placeholder="O seu email" required><button type="submit" aria-label="Subscrever newsletter">→</button></div><p id="newsletter-feedback" class="form-feedback" role="status" aria-live="polite" aria-atomic="true"></p></form></div></div><div class="footer-bottom"><span>&copy; {{ date('Y') }} LogiGate by Hongayetu LDA. Todos os direitos reservados.</span><span>Luanda, Angola</span></div></div></footer>
</body>
</html>
