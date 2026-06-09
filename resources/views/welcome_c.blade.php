<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0047AB">
    <title>Logigate | Gestão Aduaneira e Comércio Externo em Angola</title>
    <meta name="description" content="Plataforma digital que centraliza processos aduaneiros, documentos, clientes, pagamentos e acompanhamento operacional em Angola. Modernize já!">
    <meta property="og:title" content="Logigate | Plataforma Digital para Gestão Aduaneira">
    <meta property="og:description" content="Digitalize processos, centralize documentos e acompanhe operações com total transparência em Angola.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_AO">
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%230047AB'%3E%3Cpath d='M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #FFFFFF;
            overflow-x: hidden;
        }
        html {
            scroll-behavior: smooth;
        }
        /* Custom animations & utilities */
        @keyframes fadeUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(0, 71, 171, 0.4); }
            70% { box-shadow: 0 0 0 12px rgba(0, 71, 171, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 71, 171, 0); }
        }
        .animate-fade-up {
            animation: fadeUp 0.7s ease forwards;
        }
        .animate-fade-left {
            animation: fadeInLeft 0.6s ease forwards;
        }
        .float-animation {
            animation: float 4s ease-in-out infinite;
        }
        .scroll-reveal {
            opacity: 0;
            transform: translateY(35px);
            transition: opacity 0.7s cubic-bezier(0.2, 0.9, 0.4, 1.1), transform 0.7s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(0px);
            border: 1px solid rgba(0, 71, 171, 0.12);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.12);
            border-color: rgba(0, 71, 171, 0.3);
        }
        .hover-lift {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 28px -12px rgba(0, 0, 0, 0.15);
        }
        .btn-modern {
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-modern:active {
            transform: scale(0.97);
        }
        section[id] {
            scroll-margin-top: 85px;
        }
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
        }
        .mobile-menu.is-open {
            max-height: 380px;
        }
        .faq-question .fa-chevron-down {
            transition: transform 0.3s ease;
        }
        .faq-question[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }
        .plan-price {
            transition: all 0.2s ease;
        }
        .hero-bg-enhanced {
            background-image: linear-gradient(105deg, rgba(0, 25, 60, 0.92) 0%, rgba(0, 45, 90, 0.75) 50%, rgba(0, 71, 171, 0.65) 100%), url('https://images.pexels.com/photos/163016/container-ship-cargo-ship-port-163016.jpeg?auto=compress&cs=tinysrgb&w=1600');
            background-size: cover;
            background-position: center 30%;
        }
        .badge-popular {
            background: linear-gradient(135deg, #0047AB 0%, #0a5bcf 100%);
        }
        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 4px;
            background: linear-gradient(90deg, #0047AB, #3b82f6);
            z-index: 1000;
            transition: width 0.1s;
        }
        .back-to-top {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        input, select, textarea {
            transition: all 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #0047AB;
            box-shadow: 0 0 0 3px rgba(0, 71, 171, 0.2);
            outline: none;
        }
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #0047AB;
            border-radius: 6px;
        }
    </style>
</head>
<body class="antialiased">
    <div class="progress-bar" id="progressBar"></div>
    <a href="#conteudo" class="skip-link fixed left-4 top-4 z-[60] -translate-y-full rounded-lg bg-white px-4 py-2 font-bold text-blue-800 shadow-md focus:translate-y-0 focus:outline-none focus:ring-2 focus:ring-blue-500">Saltar para o conteúdo</a>

    <!-- Sticky Header Modern -->
    <header class="fixed inset-x-0 top-0 z-40 bg-white/90 backdrop-blur-md shadow-sm border-b border-gray-200/60 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-5 sm:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="#inicio" class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg group">
                    <div class="h-9 w-9 bg-gradient-to-br from-blue-700 to-blue-500 rounded-xl flex items-center justify-center shadow-md">
                        <i class="fas fa-gate text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-gray-900">Logi<span class="text-blue-700">Gate</span></span>
                </a>

                <nav class="hidden lg:flex items-center gap-6 text-sm font-semibold text-gray-700">
                    <a href="#funcionalidades" class="hover:text-blue-700 transition-colors">Funcionalidades</a>
                    <a href="#pauta" class="hover:text-blue-700 transition-colors">Pauta Aduaneira</a>
                    <a href="#planos" class="hover:text-blue-700 transition-colors">Planos</a>
                    <a href="#faq" class="hover:text-blue-700 transition-colors">FAQ</a>
                    <a href="#" class="hover:text-blue-700 transition-colors" id="demoLoginBtn">Login</a>
                    <a href="#planos" class="bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-blue-800 transition-all shadow-md hover:shadow-lg btn-modern">Começar Gratuitamente</a>
                </nav>

                <button id="menuButton" class="lg:hidden p-2 rounded-lg focus:ring-2 focus:ring-blue-500" aria-label="Menu">
                    <i class="fas fa-bars text-2xl text-gray-800"></i>
                </button>
            </div>

            <nav id="mobileMenu" class="mobile-menu lg:hidden border-t border-gray-100 bg-white">
                <div class="py-4 flex flex-col gap-3 text-sm font-semibold">
                    <a href="#funcionalidades" class="py-2 px-2 hover:bg-gray-50 rounded-lg">Funcionalidades</a>
                    <a href="#pauta" class="py-2 px-2 hover:bg-gray-50 rounded-lg">Pauta Aduaneira</a>
                    <a href="#planos" class="py-2 px-2 hover:bg-gray-50 rounded-lg">Planos</a>
                    <a href="#faq" class="py-2 px-2 hover:bg-gray-50 rounded-lg">FAQ</a>
                    <a href="#" class="py-2 px-2 hover:bg-gray-50 rounded-lg">Login</a>
                    <a href="#planos" class="bg-blue-700 text-white text-center py-2.5 rounded-lg mt-2">Começar Gratuitamente</a>
                </div>
            </nav>
        </div>
    </header>

    <main id="conteudo">
        <!-- Hero Section Melhorada -->
        <section id="inicio" class="hero-bg-enhanced relative pt-28 pb-20 md:pt-36 md:pb-28 overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.03"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
            <div class="relative max-w-7xl mx-auto px-5 sm:px-8">
                <div class="max-w-3xl animate-fade-up">
                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-1.5 border border-white/30 mb-6">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        <span class="text-xs font-bold text-white tracking-wide">LOGIGATE 2026 · ANGOLA</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold leading-tight text-white drop-shadow-md">
                        A Plataforma Digital para <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-200 to-blue-200">Gestão Aduaneira</span>
                    </h1>
                    <p class="mt-6 text-lg md:text-xl text-blue-50 max-w-2xl leading-relaxed">
                        Digitalize processos, centralize documentos e ofereça aos seus clientes total transparência e agilidade no comércio externo angolano.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="#planos" class="bg-white text-blue-800 font-bold px-7 py-3.5 rounded-xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all flex items-center gap-2 btn-modern">Começar Gratuitamente <i class="fas fa-arrow-right"></i></a>
                        <a href="#contactos" class="border border-white text-white px-7 py-3.5 rounded-xl font-semibold hover:bg-white/10 backdrop-blur-sm transition-all">Solicitar Demonstração</a>
                    </div>
                </div>
                <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    @php $highlights = ['Plataforma 100% Online', 'Portal Cliente Integrado', 'Pagamentos Digitais', 'Gestão Documental', 'Consulta Pauta Aduaneira']; @endphp
                    @foreach($highlights as $item)
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl px-4 py-3 text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-check-circle text-cyan-300 text-sm"></i> {{ $item }}
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Stats Section (nova interatividade) -->
        <div class="bg-white py-8 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="scroll-reveal"><div class="text-3xl font-black text-blue-700">+500</div><div class="text-sm text-gray-600">Operadores activos</div></div>
                <div class="scroll-reveal"><div class="text-3xl font-black text-blue-700">+10k</div><div class="text-sm text-gray-600">Processos geridos</div></div>
                <div class="scroll-reveal"><div class="text-3xl font-black text-blue-700">98%</div><div class="text-sm text-gray-600">Satisfação</div></div>
                <div class="scroll-reveal"><div class="text-3xl font-black text-blue-700">24/7</div><div class="text-sm text-gray-600">Acesso na cloud</div></div>
            </div>
        </div>

        <!-- O Problema / Solução integrada com melhorias -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-5 sm:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div class="scroll-reveal">
                        <span class="text-blue-700 font-bold text-sm uppercase tracking-wider">O problema</span>
                        <h2 class="text-3xl md:text-4xl font-extrabold mt-3 text-gray-900">Burocracia que atrasa o comércio externo</h2>
                        <p class="mt-5 text-gray-600 text-lg leading-relaxed">Documentos físicos, e-mails dispersos e pouca visibilidade travam a produtividade. A Logigate elimina estas barreiras.</p>
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach(['Falta de visibilidade', 'Atrasos operacionais', 'Documentação descentralizada', 'Custos administrativos'] as $prob)
                            <div class="flex items-center gap-2 text-gray-700"><i class="fas fa-times-circle text-red-500"></i> {{ $prob }}</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 scroll-reveal">
                        @foreach(['Centralização total', 'Redução de prazos', 'Comunicação integrada', 'Total transparência'] as $sol)
                        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all"><i class="fas fa-check-circle text-green-500 mb-2 text-xl"></i><p class="font-bold">{{ $sol }}</p></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- Funcionalidades cards com hover e animação -->
        <section id="funcionalidades" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-5 sm:px-8">
                <div class="text-center max-w-2xl mx-auto scroll-reveal">
                    <span class="text-blue-700 font-bold uppercase tracking-wide">Funcionalidades</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold mt-2">Tudo que precisa num só ecossistema</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
                    @php $features = [['ship','Gestão Aduaneira','Processos de importação/exportação'],['folder-open','Gestão Documental','Armazenamento seguro na nuvem'],['file-invoice','Facturação','Emissão e integração financeira'],['wallet','Conta Corrente','Saldos e cobranças'],['user-shield','Portal Cliente','Acompanhamento 24h'],['chart-line','Relatórios','Insights estratégicos'],['layer-group','Subscrições','Planos flexíveis'],['credit-card','Pagamentos','Integração digital']]; @endphp
                    @foreach($features as $f)
                    <div class="glass-card rounded-2xl p-6 hover-lift scroll-reveal">
                        <i class="fas fa-{{ $f[0] }} text-3xl text-blue-700 mb-4"></i>
                        <h3 class="text-xl font-extrabold">{{ $f[1] }}</h3>
                        <p class="text-gray-600 mt-2 text-sm">{{ $f[2] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Planos (com dados mock e toggles interativos) -->
        <section id="planos" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-5 sm:px-8">
                <div class="text-center scroll-reveal">
                    <span class="text-blue-700 font-bold uppercase">Planos</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold">Adaptado ao seu volume operacional</h2>
                </div>
                <div class="flex justify-center mt-8 mb-10">
                    <div class="inline-flex bg-white rounded-full p-1 shadow-md border border-gray-200">
                        <button data-cycle="monthly" class="cycle-tab px-6 py-2 rounded-full font-bold text-sm transition-all bg-blue-700 text-white">Mensal</button>
                        <button data-cycle="semestral" class="cycle-tab px-6 py-2 rounded-full font-bold text-sm text-gray-700">Semestral</button>
                        <button data-cycle="annual" class="cycle-tab px-6 py-2 rounded-full font-bold text-sm text-gray-700">Anual</button>
                    </div>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @php
                        $planosMock = [
                            ['nome'=>'Gratuito','descricao'=>'Ideal para pequenos volumes e teste','preco_m'=>0,'preco_s'=>0,'preco_a'=>0,'is_popular'=>false,'free'=>true,'features'=>['Processos até 5/mês','Documentos ilimitados','Suporte básico']],
                            ['nome'=>'Essencial','descricao'=>'Para despachantes individuais','preco_m'=>29900,'preco_s'=>159900,'preco_a'=>299900,'is_popular'=>false,'features'=>['Processos ilimitados','Portal cliente','Facturação']],
                            ['nome'=>'Profissional','descricao'=>'Gestão completa + financeiro','preco_m'=>54900,'preco_s'=>299900,'preco_a'=>549900,'is_popular'=>true,'features'=>['Conta corrente','Pagamentos integrados','Relatórios avançados']],
                            ['nome'=>'Enterprise','descricao'=>'Equipas grandes e transitários','preco_m'=>89900,'preco_s'=>489900,'preco_a'=>899900,'is_popular'=>false,'features'=>['Multi-utilizador','API integração','Suporte prioritário']]
                        ];
                    @endphp
                    @foreach($planosMock as $plano)
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 flex flex-col hover:shadow-xl transition-all scroll-reveal relative {{ $plano['is_popular'] ? 'ring-2 ring-blue-700' : '' }}">
                        @if($plano['is_popular']) <span class="absolute -top-3 left-6 badge-popular text-white text-xs font-bold px-3 py-1 rounded-full">Mais Popular</span> @endif
                        <h3 class="text-2xl font-extrabold">{{ $plano['nome'] }}</h3>
                        <p class="text-gray-500 text-sm mt-1">{{ $plano['descricao'] }}</p>
                        <div class="mt-5">
                            <div class="plan-price text-4xl font-black text-gray-900" data-monthly="{{ $plano['preco_m'] }}" data-semestral="{{ $plano['preco_s'] }}" data-annual="{{ $plano['preco_a'] }}">{{ number_format($plano['preco_m'],0,',','.') }} AOA</div>
                            <p class="cycle-label text-gray-500 text-sm">por mês</p>
                        </div>
                        <ul class="mt-6 space-y-3 flex-1">
                            @foreach($plano['features'] as $feat)
                            <li class="flex items-center gap-2 text-gray-700"><i class="fas fa-check-circle text-blue-600 text-sm"></i> {{ $feat }}</li>
                            @endforeach
                        </ul>
                        <form class="mt-8 plan-form" data-plan="{{ $plano['nome'] }}">
                            <input type="hidden" class="billing-cycle" value="monthly">
                            <button type="button" class="w-full bg-blue-700 text-white font-bold py-3 rounded-xl hover:bg-blue-800 transition-all btn-modern plan-choose-btn">{{ $plano['is_free'] ? 'Começar Gratuitamente' : 'Escolher Plano' }}</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Pauta Aduaneira consulta rápida -->
        <section id="pauta" class="py-20 bg-gradient-to-br from-blue-50 to-white">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-12 items-center">
                <div class="scroll-reveal">
                    <span class="text-blue-700 font-bold uppercase">Consulta Pautal</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold mt-2">Pesquise códigos, taxas e direitos aduaneiros</h2>
                    <p class="text-gray-600 mt-4 text-lg">Acesso rápido e gratuito à pauta aduaneira angolana com informações actualizadas.</p>
                    <button onclick="alertDemo('Consulta de pauta disponível em breve na área pública')" class="mt-6 bg-blue-700 text-white px-6 py-3 rounded-xl font-bold inline-flex items-center gap-2 shadow-md hover:shadow-xl transition-all">Consultar Pauta <i class="fas fa-arrow-right"></i></button>
                </div>
                <div class="grid grid-cols-2 gap-4 scroll-reveal">
                    @foreach(['Códigos pautais','Mercadorias','Taxas aplicáveis','Direitos aduaneiros','IVA','Impostos'] as $item)
                    <div class="bg-white rounded-xl p-4 shadow-sm flex items-center justify-between"><span>{{ $item }}</span><i class="fas fa-search text-blue-600"></i></div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- FAQ Melhorada -->
        <section id="faq" class="py-20 bg-gray-50">
            <div class="max-w-4xl mx-auto px-5 sm:px-8">
                <div class="text-center scroll-reveal"><span class="text-blue-700 font-bold uppercase">FAQ</span><h2 class="text-3xl font-extrabold mt-2">Perguntas frequentes</h2></div>
                <div class="mt-10 space-y-4">
                    @php $faqs = [['Pergunta: A Logigate funciona na cloud?','Sim, 100% cloud, acessível de qualquer dispositivo com internet.'],['O Portal Cliente está disponível?','Sim, clientes podem acompanhar processos e documentos em tempo real.'],['Transitários já podem usar?','Área especializada em breve, beta em breve.'],['Quanto tempo para activar?','Menos de 10 minutos após escolha do plano.']]; @endphp
                    @foreach($faqs as $idx=>$faq)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <button class="faq-question w-full flex justify-between items-center p-5 text-left font-bold text-gray-800 hover:bg-gray-50 transition" aria-expanded="false" data-faq="{{ $idx }}">
                            {{ $faq[0] }} <i class="fas fa-chevron-down text-blue-600 transition-transform"></i>
                        </button>
                        <div class="faq-answer hidden px-5 pb-5 text-gray-600 border-t border-gray-100">{{ $faq[1] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Contactos e demonstração -->
        <section id="contactos" class="py-20 bg-white">
            <div class="max-w-6xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-12">
                <div class="scroll-reveal"><span class="text-blue-700 font-bold uppercase">Demonstração</span><h2 class="text-3xl font-extrabold mt-3">Solicite uma demonstração gratuita</h2><p class="mt-4 text-gray-600">Conte-nos sobre a sua operação, e a nossa equipa mostrará como a Logigate pode transformar a sua gestão aduaneira.</p>
                <div class="mt-8 space-y-4"><p><i class="fas fa-phone text-blue-700 w-6"></i> +244 948 242 262</p><p><i class="fas fa-envelope text-blue-700 w-6"></i> geral@logigate.co.ao</p><p><i class="fas fa-map-marker-alt text-blue-700 w-6"></i> Luanda, Angola</p></div>
                </div>
                <form id="contactFormDemo" class="bg-gray-50 p-6 rounded-2xl shadow-lg scroll-reveal">
                    <div class="grid sm:grid-cols-2 gap-4"><input type="text" placeholder="Nome completo" required class="p-3 rounded-xl border-gray-200"><input type="text" placeholder="Empresa" class="p-3 rounded-xl border-gray-200"></div>
                    <div class="mt-4"><input type="email" placeholder="Email" required class="w-full p-3 rounded-xl border-gray-200"></div>
                    <div class="mt-4"><input type="tel" placeholder="Telefone" required class="w-full p-3 rounded-xl border-gray-200"></div>
                    <div class="mt-4"><select class="w-full p-3 rounded-xl border-gray-200"><option>Seleccione assunto</option><option>Demonstração</option><option>Planos</option></select></div>
                    <div class="mt-4"><textarea rows="3" placeholder="Mensagem" class="w-full p-3 rounded-xl border-gray-200"></textarea></div>
                    <button type="submit" class="mt-5 w-full bg-blue-700 text-white font-bold py-3 rounded-xl hover:bg-blue-800 transition-all">Enviar Pedido</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="bg-gray-900 text-white pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 grid md:grid-cols-4 gap-8"><div><div class="flex items-center gap-2"><div class="bg-blue-600 w-8 h-8 rounded-lg flex items-center justify-center"><i class="fas fa-gate"></i></div><span class="font-extrabold text-xl">Logigate</span></div><p class="mt-3 text-gray-400 text-sm">Plataforma aduaneira inteligente para Angola.</p></div>
        <div><h4 class="font-bold">Acesso</h4><ul class="mt-3 space-y-2 text-gray-400"><li><a href="#" class="hover:text-white">Login</a></li><li><a href="#" class="hover:text-white">Portal Cliente</a></li></ul></div>
        <div><h4 class="font-bold">Legal</h4><ul class="mt-3 space-y-2 text-gray-400"><li><a href="#">Termos</a></li><li><a href="#">Privacidade</a></li></ul></div>
        <div><h4 class="font-bold">Newsletter</h4><form id="newsletterDemo"><div class="flex mt-3"><input type="email" placeholder="Seu email" class="flex-1 p-2 rounded-l-lg text-gray-800"><button class="bg-blue-700 px-4 rounded-r-lg"><i class="fas fa-paper-plane"></i></button></div></form></div></div>
        <div class="text-center text-gray-500 text-sm mt-10 border-t border-gray-800 pt-6">© 2026 Logigate by Hongayetu LDA. Todos os direitos reservados.</div>
    </footer>
    <button id="backToTop" class="back-to-top fixed bottom-6 right-6 bg-blue-700 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:bg-blue-800 transition-all z-40"><i class="fas fa-arrow-up"></i></button>

    <div id="toastMsg" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-6 py-3 rounded-full shadow-lg z-50 opacity-0 transition-all pointer-events-none text-sm font-medium"></div>

    <script>
        // Improvements: scroll reveal, pricing toggle, back to top, mobile menu, faq, forms simulation, progress bar
        const scrollElements = document.querySelectorAll('.scroll-reveal');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('revealed'); });
        }, { threshold: 0.1, rootMargin: "0px 0px -20px 0px" });
        scrollElements.forEach(el => observer.observe(el));

        // Progress bar
        window.addEventListener('scroll', () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById('progressBar').style.width = scrolled + '%';
            const backBtn = document.getElementById('backToTop');
            if (winScroll > 400) backBtn.classList.add('visible'); else backBtn.classList.remove('visible');
        });
        document.getElementById('backToTop')?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

        // Mobile menu
        const menuBtn = document.getElementById('menuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        if(menuBtn) menuBtn.addEventListener('click', () => { mobileMenu.classList.toggle('is-open'); });

        // Pricing toggle
        const cycleBtns = document.querySelectorAll('.cycle-tab');
        const prices = document.querySelectorAll('.plan-price');
        const cycleLabels = document.querySelectorAll('.cycle-label');
        const billingInputs = document.querySelectorAll('.billing-cycle');
        function updatePrices(cycle) {
            prices.forEach(price => {
                const val = parseFloat(price.dataset[cycle] || 0);
                price.textContent = new Intl.NumberFormat('pt-AO').format(val) + ' AOA';
            });
            cycleLabels.forEach(lbl => { lbl.textContent = cycle === 'monthly' ? 'por mês' : (cycle === 'semestral' ? 'por semestre' : 'por ano'); });
            billingInputs.forEach(inp => inp.value = cycle);
        }
        cycleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const cycle = btn.dataset.cycle;
                cycleBtns.forEach(b => { b.classList.remove('bg-blue-700', 'text-white'); b.classList.add('text-gray-700'); b.style.background = ''; });
                btn.classList.add('bg-blue-700', 'text-white');
                btn.classList.remove('text-gray-700');
                updatePrices(cycle);
            });
        });

        // FAQ toggle
        document.querySelectorAll('.faq-question').forEach(btn => {
            btn.addEventListener('click', () => {
                const expanded = btn.getAttribute('aria-expanded') === 'true';
                btn.setAttribute('aria-expanded', !expanded);
                const answer = btn.nextElementSibling;
                if(answer) answer.classList.toggle('hidden');
            });
        });

        // Simulate Toasts
        function showToast(msg, isError = false) { const toast = document.getElementById('toastMsg'); toast.textContent = msg; toast.classList.remove('opacity-0'); toast.classList.add('opacity-100'); if(!isError) toast.style.background = '#0F3B3B'; else toast.style.background = '#B91C1C'; setTimeout(() => toast.classList.remove('opacity-100'), 3500); }
        function alertDemo(msg) { showToast(msg || 'Funcionalidade em demonstração'); }

        document.querySelectorAll('.plan-choose-btn').forEach(btn => {
            btn.addEventListener('click', (e) => { e.preventDefault(); showToast('Redirecionamento para plano disponível na versão comercial', false); });
        });
        const contactForm = document.getElementById('contactFormDemo');
        if(contactForm) contactForm.addEventListener('submit', (e) => { e.preventDefault(); showToast('Pedido de demonstração recebido! Entraremos em contacto.', false); contactForm.reset(); });
        const newsletterDemo = document.getElementById('newsletterDemo');
        if(newsletterDemo) newsletterDemo.addEventListener('submit', (e) => { e.preventDefault(); showToast('Inscrição confirmada! Obrigado.', false); newsletterDemo.querySelector('input').value = ''; });
        document.getElementById('demoLoginBtn')?.addEventListener('click', (e) => { e.preventDefault(); showToast('Área de login em construção. Será lançada brevemente.'); });
        document.querySelectorAll('a[href^="#"]').forEach(anchor => { anchor.addEventListener('click', function(e) { const hash = this.getAttribute('href'); if(hash && hash !== '#' && hash.startsWith('#')) { e.preventDefault(); const target = document.querySelector(hash); if(target) target.scrollIntoView({ behavior: 'smooth' }); } }); });
        // slight style for hero animations
        document.querySelector('.animate-fade-up')?.classList.add('animate-fade-up');
    </script>
</body>
</html>
