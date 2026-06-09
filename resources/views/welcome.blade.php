<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0047AB">
    <title>Logigate | Gestão Aduaneira e Comércio Externo em Angola</title>
    <meta name="description" content="A Logigate centraliza processos aduaneiros, documentos, clientes, pagamentos e acompanhamento operacional numa plataforma digital para Angola.">
    <meta property="og:title" content="Logigate | Plataforma Digital para Gestão Aduaneira">
    <meta property="og:description" content="Digitalize processos, centralize documentos e ofereça acompanhamento moderno aos seus clientes.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_AO">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand: #0047AB;
            --brand-dark: #073B83;
            --ink: #0F172A;
            --muted: #475569;
            --line: #D7DEE8;
            --soft: #F7FAFC;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: #FFFFFF;
        }

        .focus-ring:focus-visible {
            outline: 3px solid #38BDF8;
            outline-offset: 3px;
        }

        .site-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .btn-primary,
        .btn-secondary,
        .btn-light {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-weight: 700;
            transition: background-color .2s ease, color .2s ease, border-color .2s ease, transform .2s ease;
        }

        .btn-primary {
            background: var(--brand);
            color: #FFFFFF;
        }

        .btn-primary:hover {
            background: var(--brand-dark);
        }

        .btn-secondary {
            color: var(--brand);
            border: 1px solid var(--brand);
            background: #FFFFFF;
        }

        .btn-secondary:hover {
            background: #EFF6FF;
        }

        .btn-light {
            background: #FFFFFF;
            color: var(--brand);
        }

        .section {
            padding: 4rem 0;
        }

        .section-muted {
            background: var(--soft);
        }

        .eyebrow {
            color: var(--brand);
            font-weight: 800;
            font-size: .78rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #FFFFFF;
        }

        .hero-bg {
            min-height: 88vh;
            background-image:
                linear-gradient(90deg, rgba(3, 22, 51, .88), rgba(3, 22, 51, .66), rgba(3, 22, 51, .25)),
                url('{{ asset('dist/img/logistic_bg_login.jpg') }}');
            background-size: cover;
            background-position: center;
        }

        .hero-panel {
            border: 1px solid rgba(255, 255, 255, .28);
            background: rgba(255, 255, 255, .08);
            border-radius: 8px;
        }

        .mobile-menu {
            display: none;
        }

        .mobile-menu.is-open {
            display: block;
        }

        .skip-link {
            position: fixed;
            left: 1rem;
            top: 1rem;
            z-index: 60;
            transform: translateY(-140%);
            border-radius: 8px;
            background: #FFFFFF;
            padding: .65rem 1rem;
            color: var(--brand);
            font-weight: 700;
        }

        .skip-link:focus {
            transform: translateY(0);
        }

        @media (min-width: 768px) {
            .section {
                padding: 5rem 0;
            }

            .site-shell {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <a href="#conteudo" class="skip-link focus-ring text-sm">
        Saltar para o conteúdo
    </a>

    <header class="fixed inset-x-0 top-0 z-40 border-b border-gray-200 bg-white">
        <div class="site-shell">
            <div class="flex h-16 items-center justify-between">
                <a href="#inicio" class="focus-ring flex items-center gap-3" aria-label="Logigate">
                    <img src="{{ asset('dist/img/LOGIGATE.png') }}" alt="" class="h-10 w-auto">
                    <span class="text-lg font-extrabold tracking-tight text-gray-900">Logi<span class="text-blue-700">Gate</span></span>
                </a>

                <nav class="hidden items-center gap-6 text-sm font-semibold text-gray-700 lg:flex" aria-label="Navegação principal">
                    <a href="#funcionalidades" class="focus-ring hover:text-blue-700">Funcionalidades</a>
                    <a href="{{ route('consultar.pauta') }}" class="focus-ring hover:text-blue-700">Pauta Aduaneira</a>
                    <a href="#planos" class="focus-ring hover:text-blue-700">Planos</a>
                    <a href="#faq" class="focus-ring hover:text-blue-700">FAQ</a>
                    <a href="{{ route('login') }}" class="focus-ring hover:text-blue-700">Login</a>
                    <a href="#planos" class="btn-primary focus-ring">Começar Gratuitamente</a>
                </nav>

                <button id="menuButton" type="button" class="focus-ring rounded p-2 text-gray-800 lg:hidden" aria-expanded="false" aria-controls="mobileMenu" aria-label="Abrir menu">
                    <i class="fas fa-bars text-xl" aria-hidden="true"></i>
                </button>
            </div>

            <nav id="mobileMenu" class="mobile-menu border-t border-gray-200 py-4 lg:hidden" aria-label="Menu móvel">
                <div class="grid gap-3 text-sm font-semibold text-gray-800">
                    <a href="#funcionalidades" class="focus-ring rounded px-2 py-2">Funcionalidades</a>
                    <a href="{{ route('consultar.pauta') }}" class="focus-ring rounded px-2 py-2">Pauta Aduaneira</a>
                    <a href="#planos" class="focus-ring rounded px-2 py-2">Planos</a>
                    <a href="#faq" class="focus-ring rounded px-2 py-2">FAQ</a>
                    <a href="{{ route('login') }}" class="focus-ring rounded px-2 py-2">Login</a>
                    <a href="#planos" class="btn-primary focus-ring mt-2">Começar Gratuitamente</a>
                </div>
            </nav>
        </div>
    </header>

    <main id="conteudo">
        <section id="inicio" class="hero-bg flex items-center pt-20 text-white">
            <div class="site-shell w-full py-12 md:py-20">
                <div class="max-w-3xl">
                    <p class="mb-5 inline-flex rounded bg-white px-3 py-2 text-sm font-bold text-blue-800">
                        LOGIGATE 2026 · Angola
                    </p>
                    <h1 class="text-4xl font-extrabold leading-tight md:text-5xl lg:text-6xl">
                        A Plataforma Digital para Gestão Aduaneira e Comércio Externo em Angola
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-blue-50">
                        Digitalize os seus processos, centralize documentos, acompanhe operações e ofereça aos seus clientes uma experiência moderna e transparente.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="#planos" class="btn-light focus-ring">Começar Gratuitamente</a>
                        <a href="#contactos" class="btn-secondary focus-ring border-white text-white" style="background: transparent; color: #FFFFFF; border-color: #FFFFFF;">Solicitar Demonstração</a>
                    </div>
                </div>

                <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach(['Plataforma 100% Online', 'Portal Cliente Integrado', 'Pagamentos Digitais', 'Gestão Documental', 'Consulta da Pauta Aduaneira'] as $highlight)
                        <div class="hero-panel px-4 py-3 text-sm font-semibold text-white">
                            <i class="fas fa-check mr-2 text-green-300" aria-hidden="true"></i>{{ $highlight }}
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section section-muted" aria-labelledby="problema-heading">
            <div class="site-shell grid gap-10 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="eyebrow">O problema</p>
                    <h2 id="problema-heading" class="mt-3 text-3xl font-extrabold md:text-4xl">O comércio externo ainda enfrenta demasiada burocracia</h2>
                    <p class="mt-5 text-lg leading-8 text-gray-700">
                        Muitas operações continuam dependentes de documentos físicos, trocas de e-mails dispersas e processos difíceis de acompanhar.
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach(['Falta de visibilidade dos processos', 'Atrasos operacionais', 'Comunicação difícil com clientes', 'Documentação descentralizada', 'Custos administrativos elevados', 'Perda de produtividade'] as $problem)
                        <div class="card p-4">
                            <i class="fas fa-minus-circle mb-3 text-amber-600" aria-hidden="true"></i>
                            <p class="font-semibold text-gray-900">{{ $problem }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section bg-white" aria-labelledby="solucao-heading">
            <div class="site-shell">
                <div class="max-w-3xl">
                    <p class="eyebrow">A solução Logigate</p>
                    <h2 id="solucao-heading" class="mt-3 text-3xl font-extrabold md:text-4xl">Uma única plataforma para gerir toda a operação</h2>
                    <p class="mt-5 text-lg leading-8 text-gray-700">
                        A Logigate moderniza a forma como processos aduaneiros são geridos, com documentos, clientes, pagamentos e acompanhamento operacional num único lugar.
                    </p>
                </div>

                <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    @foreach(['Gerir processos', 'Centralizar documentos', 'Controlar clientes', 'Emitir facturas', 'Gerir conta corrente', 'Receber pagamentos', 'Acompanhar em tempo real', 'Comunicar com clientes'] as $solution)
                        <div class="card p-5">
                            <i class="fas fa-check-circle mb-3 text-green-600" aria-hidden="true"></i>
                            <h3 class="font-bold text-gray-900">{{ $solution }}</h3>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 border-l-4 border-blue-700 bg-blue-50 p-6">
                    <p class="text-2xl font-extrabold leading-9 text-blue-950">Menos burocracia. Mais controlo. Mais produtividade.</p>
                </div>
            </div>
        </section>

        <section class="section section-muted" aria-labelledby="perfis-heading">
            <div class="site-shell">
                <div class="mb-10 max-w-3xl">
                    <p class="eyebrow">Quem utiliza</p>
                    <h2 id="perfis-heading" class="mt-3 text-3xl font-extrabold md:text-4xl">Criada para quem vive a operação aduaneira</h2>
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    <article class="card p-6">
                        <span class="rounded bg-green-100 px-3 py-1 text-xs font-bold text-green-800">Disponível agora</span>
                        <h3 class="mt-5 text-xl font-extrabold">Despachante Oficial</h3>
                        <p class="mt-3 text-gray-700">Ferramentas para gestão completa dos processos aduaneiros.</p>
                        <ul class="mt-5 space-y-2 text-sm text-gray-700">
                            @foreach(['Processos Aduaneiros', 'Gestão de Clientes', 'Facturação', 'Conta Corrente', 'Relatórios', 'Gestão Documental'] as $item)
                                <li><i class="fas fa-check mr-2 text-blue-700" aria-hidden="true"></i>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </article>

                    <article class="card p-6">
                        <span class="rounded bg-green-100 px-3 py-1 text-xs font-bold text-green-800">Disponível agora</span>
                        <h3 class="mt-5 text-xl font-extrabold">Cliente</h3>
                        <p class="mt-3 text-gray-700">Acompanhe processos sem depender de chamadas ou e-mails.</p>
                        <ul class="mt-5 space-y-2 text-sm text-gray-700">
                            @foreach(['Consulta de Processos', 'Consulta de Documentos', 'Consulta de Pagamentos', 'Comunicação com o Operador'] as $item)
                                <li><i class="fas fa-check mr-2 text-blue-700" aria-hidden="true"></i>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </article>

                    <article class="card p-6">
                        <span class="rounded bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Brevemente</span>
                        <h3 class="mt-5 text-xl font-extrabold">Transitário</h3>
                        <p class="mt-3 text-gray-700">Área especializada para operações logísticas e acompanhamento de cargas.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="funcionalidades" class="section bg-white" aria-labelledby="features-heading">
            <div class="site-shell">
                <div class="mb-10 max-w-3xl">
                    <p class="eyebrow">Funcionalidades</p>
                    <h2 id="features-heading" class="mt-3 text-3xl font-extrabold md:text-4xl">As principais áreas da operação num só sistema</h2>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @php
                        $features = [
                            ['icon' => 'fa-ship', 'title' => 'Gestão Aduaneira', 'text' => 'Controlo completo dos processos de importação e exportação.'],
                            ['icon' => 'fa-folder-open', 'title' => 'Gestão Documental', 'text' => 'Organização digital de todos os documentos.'],
                            ['icon' => 'fa-file-invoice', 'title' => 'Facturação', 'text' => 'Emissão e gestão financeira integrada.'],
                            ['icon' => 'fa-wallet', 'title' => 'Conta Corrente', 'text' => 'Controlo de saldos e cobranças.'],
                            ['icon' => 'fa-user-shield', 'title' => 'Portal Cliente', 'text' => 'Acompanhamento em tempo real.'],
                            ['icon' => 'fa-chart-line', 'title' => 'Relatórios', 'text' => 'Informação estratégica para apoio à decisão.'],
                            ['icon' => 'fa-layer-group', 'title' => 'Subscrições', 'text' => 'Planos adaptados ao tamanho da sua operação.'],
                            ['icon' => 'fa-credit-card', 'title' => 'Pagamentos Integrados', 'text' => 'Integração com soluções de pagamento digitais.'],
                        ];
                    @endphp

                    @foreach($features as $feature)
                        <article class="card p-5">
                            <i class="fas {{ $feature['icon'] }} mb-4 text-2xl text-blue-700" aria-hidden="true"></i>
                            <h3 class="font-extrabold text-gray-900">{{ $feature['title'] }}</h3>
                            <p class="mt-3 text-sm leading-6 text-gray-700">{{ $feature['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section section-muted" aria-labelledby="marketplace-heading">
            <div class="site-shell grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="eyebrow">Marketplace de serviços</p>
                    <h2 id="marketplace-heading" class="mt-3 text-3xl font-extrabold md:text-4xl">Em breve: uma rede especializada para o sector</h2>
                    <p class="mt-5 text-lg leading-8 text-gray-700">
                        Estamos a desenvolver um marketplace para conectar despachantes, transitários, transportadores e prestadores de serviços logísticos.
                    </p>
                </div>
                <div class="card p-6">
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach(['Despachantes', 'Transitários', 'Transportadores', 'Prestadores Logísticos'] as $item)
                            <div class="rounded border border-gray-200 p-4 font-semibold text-gray-800">{{ $item }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="section bg-white" aria-labelledby="pauta-heading">
            <div class="site-shell grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="eyebrow">Consulta da Pauta Aduaneira</p>
                    <h2 id="pauta-heading" class="mt-3 text-3xl font-extrabold md:text-4xl">Consulte rapidamente informação pautal</h2>
                    <p class="mt-5 text-lg leading-8 text-gray-700">
                        Pesquise códigos pautais, mercadorias, taxas, direitos aduaneiros, IVA e impostos aplicáveis.
                    </p>
                    <a href="{{ route('consultar.pauta') }}" class="btn-primary focus-ring mt-6">Consultar Pauta Aduaneira</a>
                </div>
                <div class="card p-6">
                    <div class="grid gap-3">
                        @foreach(['Códigos pautais', 'Mercadorias', 'Taxas', 'Direitos Aduaneiros', 'IVA', 'Impostos Aplicáveis'] as $item)
                            <div class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-b-0 last:pb-0">
                                <span class="font-semibold text-gray-800">{{ $item }}</span>
                                <i class="fas fa-search text-blue-700" aria-hidden="true"></i>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="section section-muted" aria-labelledby="como-heading">
            <div class="site-shell">
                <div class="mb-10 max-w-3xl">
                    <p class="eyebrow">Como funciona</p>
                    <h2 id="como-heading" class="mt-3 text-3xl font-extrabold md:text-4xl">Da escolha do plano à operação em menos de 10 minutos</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-5">
                    @foreach(['Escolha o plano ideal', 'Crie a sua conta', 'Configure a empresa', 'Convide colaboradores', 'Comece a gerir processos'] as $index => $step)
                        <div class="card p-5">
                            <div class="flex h-10 w-10 items-center justify-center rounded bg-blue-700 font-extrabold text-white">{{ $index + 1 }}</div>
                            <h3 class="mt-4 font-bold text-gray-900">{{ $step }}</h3>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="planos" class="section bg-white" aria-labelledby="planos-heading">
            <div class="site-shell">
                <div class="mb-10 max-w-3xl">
                    <p class="eyebrow">Planos</p>
                    <h2 id="planos-heading" class="mt-3 text-3xl font-extrabold md:text-4xl">Planos adaptados ao tamanho da sua operação</h2>
                    <p class="mt-5 text-lg leading-8 text-gray-700">Todos os planos incluem actualizações automáticas, segurança dos dados, backups automáticos e suporte técnico.</p>
                </div>

                <div class="mb-8 inline-flex rounded border border-gray-300 p-1" role="tablist" aria-label="Modalidade de pagamento">
                    <button type="button" class="cycle-tab rounded px-4 py-2 text-sm font-bold text-white" style="background: var(--brand);" data-cycle="monthly" aria-selected="true">Mensal</button>
                    <button type="button" class="cycle-tab rounded px-4 py-2 text-sm font-bold text-gray-700" data-cycle="semestral" aria-selected="false">Semestral</button>
                    <button type="button" class="cycle-tab rounded px-4 py-2 text-sm font-bold text-gray-700" data-cycle="annual" aria-selected="false">Anual</button>
                </div>

                @if($planos->count())
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        @foreach($planos as $plano)
                            <article class="card flex flex-col p-6 {{ $plano->is_popular ? 'border-blue-700' : '' }}">
                                @if($plano->is_popular)
                                    <span class="mb-4 w-max rounded bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800">Mais Popular</span>
                                @endif

                                <h3 class="text-xl font-extrabold">{{ $plano->nome }}</h3>
                                <p class="mt-3 text-sm leading-6 text-gray-700" style="min-height: 48px;">{{ \Illuminate\Support\Str::limit($plano->descricao, 90) }}</p>

                                <div class="mt-6">
                                    <div class="plan-price text-3xl font-extrabold" data-monthly="{{ (float) $plano->preco_mensal }}" data-semestral="{{ (float) $plano->preco_semestral }}" data-annual="{{ (float) $plano->preco_anual }}">
                                        {{ number_format((float) $plano->preco_mensal, 0, ',', '.') }} AOA
                                    </div>
                                    <p class="cycle-label mt-1 text-sm text-gray-600">por mês</p>
                                </div>

                                <ul class="mt-6 flex-1 space-y-3 text-sm text-gray-700">
                                    @forelse($plano->itemplano as $item)
                                        <li class="flex gap-2">
                                            <i class="fas {{ $item->icon ?: 'fa-check' }} mt-1 text-blue-700" aria-hidden="true"></i>
                                            <span>{{ $item->item }}</span>
                                        </li>
                                    @empty
                                        <li class="flex gap-2"><i class="fas fa-check mt-1 text-blue-700" aria-hidden="true"></i><span>Gestão base de processos e documentos</span></li>
                                        <li class="flex gap-2"><i class="fas fa-check mt-1 text-blue-700" aria-hidden="true"></i><span>Suporte técnico incluído</span></li>
                                    @endforelse
                                </ul>

                                <form method="GET" action="{{ route('register') }}" class="mt-6">
                                    <input type="hidden" name="plano" value="{{ $plano->id }}">
                                    <input type="hidden" name="modalidade" class="billing-cycle" value="monthly">
                                    <button type="submit" class="btn-primary focus-ring w-full">
                                        {{ $plano->is_free ? 'Começar Gratuitamente' : 'Escolher Plano' }}
                                    </button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="card p-8 text-center">
                        <h3 class="text-xl font-extrabold">Planos em actualização</h3>
                        <p class="mt-3 text-gray-700">Fale connosco para escolher a configuração ideal para a sua operação.</p>
                        <a href="#contactos" class="btn-primary focus-ring mt-6">Solicitar Demonstração</a>
                    </div>
                @endif
            </div>
        </section>

        <section id="faq" class="section section-muted" aria-labelledby="faq-heading">
            <div class="site-shell">
                <div class="mb-10 max-w-3xl">
                    <p class="eyebrow">FAQ</p>
                    <h2 id="faq-heading" class="mt-3 text-3xl font-extrabold md:text-4xl">Perguntas frequentes</h2>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    @php
                        $faqs = [
                            ['q' => 'A Logigate funciona na cloud?', 'a' => 'Sim. Pode aceder a partir de qualquer local, usando uma ligação à internet.'],
                            ['q' => 'O Portal Cliente já está disponível?', 'a' => 'Sim. Os clientes podem acompanhar processos, documentos, pagamentos e comunicação com o operador.'],
                            ['q' => 'O Transitário já pode usar a plataforma?', 'a' => 'A área especializada para transitários está em desenvolvimento e será disponibilizada brevemente.'],
                            ['q' => 'Como a subscrição é activada?', 'a' => 'Depois da escolha do plano e confirmação do pagamento, a subscrição é activada para a empresa.'],
                            ['q' => 'Posso consultar a Pauta Aduaneira sem conta?', 'a' => 'Sim. A consulta pública da pauta está acessível a partir da landing page.'],
                            ['q' => 'Quanto tempo demora a activação?', 'a' => 'O fluxo foi desenhado para demorar menos de 10 minutos quando os dados e pagamento estão concluídos.'],
                        ];
                    @endphp

                    @foreach($faqs as $index => $faq)
                        <div class="card">
                            <button type="button" class="faq-toggle focus-ring flex w-full items-center justify-between gap-4 p-5 text-left" aria-expanded="false" aria-controls="faq-{{ $index }}">
                                <span class="font-bold text-gray-900">{{ $faq['q'] }}</span>
                                <i class="fas fa-chevron-down text-blue-700" aria-hidden="true"></i>
                            </button>
                            <div id="faq-{{ $index }}" class="hidden px-5 pb-5 text-gray-700">
                                {{ $faq['a'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="contactos" class="section bg-white" aria-labelledby="contactos-heading">
            <div class="site-shell grid gap-10 lg:grid-cols-2">
                <div>
                    <p class="eyebrow">Demonstração</p>
                    <h2 id="contactos-heading" class="mt-3 text-3xl font-extrabold md:text-4xl">Solicite uma demonstração da Logigate</h2>
                    <p class="mt-5 text-lg leading-8 text-gray-700">Conte-nos sobre a sua operação e a nossa equipa ajuda a escolher o melhor caminho.</p>

                    <div class="mt-8 space-y-4 text-gray-700">
                        <p><i class="fas fa-phone mr-3 text-blue-700" aria-hidden="true"></i><a href="tel:+244948242262" class="focus-ring hover:text-blue-700">+244 948 242 262</a></p>
                        <p><i class="fas fa-envelope mr-3 text-blue-700" aria-hidden="true"></i><a href="mailto:geral@hongayetu.com" class="focus-ring hover:text-blue-700">geral@hongayetu.com</a></p>
                        <p><i class="fas fa-map-marker-alt mr-3 text-blue-700" aria-hidden="true"></i>Luanda, Angola</p>
                    </div>
                </div>

                <form id="contactForm" action="{{ route('contact.send') }}" method="POST" class="card p-6">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="nome" class="block text-sm font-bold text-gray-800">Nome completo</label>
                            <input id="nome" name="nome" type="text" required class="focus-ring mt-2 w-full rounded border border-gray-300 px-3 py-3">
                        </div>
                        <div>
                            <label for="empresa" class="block text-sm font-bold text-gray-800">Empresa</label>
                            <input id="empresa" name="empresa" type="text" class="focus-ring mt-2 w-full rounded border border-gray-300 px-3 py-3">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="email" class="block text-sm font-bold text-gray-800">Email</label>
                        <input id="email" name="email" type="email" required class="focus-ring mt-2 w-full rounded border border-gray-300 px-3 py-3">
                    </div>
                    <div class="mt-4">
                        <label for="telefone" class="block text-sm font-bold text-gray-800">Telefone</label>
                        <input id="telefone" name="telefone" type="tel" required class="focus-ring mt-2 w-full rounded border border-gray-300 px-3 py-3">
                    </div>
                    <div class="mt-4">
                        <label for="assunto" class="block text-sm font-bold text-gray-800">Assunto</label>
                        <select id="assunto" name="assunto" required class="focus-ring mt-2 w-full rounded border border-gray-300 px-3 py-3">
                            <option value="">Seleccione</option>
                            <option value="demonstracao">Solicitar demonstração</option>
                            <option value="planos">Informação sobre planos</option>
                            <option value="pauta">Consulta da pauta</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <label for="mensagem" class="block text-sm font-bold text-gray-800">Mensagem</label>
                        <textarea id="mensagem" name="mensagem" rows="4" required class="focus-ring mt-2 w-full rounded border border-gray-300 px-3 py-3"></textarea>
                    </div>
                    <button type="submit" class="btn-primary focus-ring mt-5 w-full">Enviar pedido</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="bg-gray-900 py-10 text-white">
        <div class="site-shell">
            <div class="grid gap-8 md:grid-cols-4">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('dist/img/LOGIGATE.png') }}" alt="" class="h-10 w-auto">
                        <span class="text-lg font-extrabold">Logigate</span>
                    </div>
                    <p class="mt-4 max-w-md text-gray-300">Plataforma digital para gestão aduaneira, comércio externo e acompanhamento operacional em Angola.</p>
                </div>
                <div>
                    <h3 class="font-bold">Acesso</h3>
                    <ul class="mt-4 space-y-2 text-gray-300">
                        <li><a href="{{ route('login') }}" class="focus-ring hover:text-white">Login</a></li>
                        <li><a href="{{ route('portal-cliente.login') }}" class="focus-ring hover:text-white">Portal Cliente</a></li>
                        <li><a href="{{ route('portal.transitarios') }}" class="focus-ring hover:text-white">Transitário</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold">Newsletter</h3>
                    <form id="newsletterForm" action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-4 flex">
                        @csrf
                        <label for="newsletter-email" class="sr-only">Email</label>
                        <input id="newsletter-email" name="email" type="email" required placeholder="O seu email" class="focus-ring min-w-0 flex-1 rounded-l border-0 px-3 py-3 text-gray-900">
                        <button type="submit" class="focus-ring rounded-r bg-blue-700 px-4 font-bold hover:bg-blue-800" aria-label="Subscrever">
                            <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-800 pt-6 text-sm text-gray-400">
                &copy; {{ date('Y') }} Logigate by Hongayetu LDA. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <div id="pageNotice" class="fixed bottom-4 left-4 right-4 z-50 hidden rounded border bg-white p-4 text-sm font-semibold shadow-lg md:left-auto md:w-96" role="status" aria-live="polite"></div>

    <script>
        const menuButton = document.getElementById('menuButton');
        const mobileMenu = document.getElementById('mobileMenu');

        if (menuButton && mobileMenu) {
            menuButton.addEventListener('click', () => {
                const isOpen = mobileMenu.classList.toggle('is-open');
                menuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            mobileMenu.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    mobileMenu.classList.remove('is-open');
                    menuButton.setAttribute('aria-expanded', 'false');
                });
            });
        }

        const formatter = new Intl.NumberFormat('pt-AO', { maximumFractionDigits: 0 });
        const cycleLabels = {
            monthly: 'por mês',
            semestral: 'por semestre',
            annual: 'por ano'
        };

        document.querySelectorAll('.cycle-tab').forEach((tab) => {
            tab.addEventListener('click', () => {
                const cycle = tab.dataset.cycle;

                document.querySelectorAll('.cycle-tab').forEach((item) => {
                    item.setAttribute('aria-selected', 'false');
                    item.classList.remove('text-white');
                    item.classList.add('text-gray-700');
                    item.style.background = 'transparent';
                });

                tab.setAttribute('aria-selected', 'true');
                tab.classList.add('text-white');
                tab.classList.remove('text-gray-700');
                tab.style.background = 'var(--brand)';

                document.querySelectorAll('.billing-cycle').forEach((input) => {
                    input.value = cycle;
                });

                document.querySelectorAll('.plan-price').forEach((price) => {
                    const value = Number(price.dataset[cycle] || 0);
                    price.textContent = formatter.format(value) + ' AOA';
                    const label = price.parentElement.querySelector('.cycle-label');
                    if (label) {
                        label.textContent = cycleLabels[cycle];
                    }
                });
            });
        });

        document.querySelectorAll('.faq-toggle').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.getAttribute('aria-controls'));
                const isExpanded = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                if (target) {
                    target.classList.toggle('hidden');
                }
            });
        });

        const notice = document.getElementById('pageNotice');

        function showNotice(type, message) {
            if (!notice) return;

            notice.classList.remove('hidden', 'border-green-200', 'border-red-200', 'text-green-800', 'text-red-800');
            notice.classList.add(type === 'success' ? 'border-green-200' : 'border-red-200');
            notice.classList.add(type === 'success' ? 'text-green-800' : 'text-red-800');
            notice.textContent = message;

            window.setTimeout(() => {
                notice.classList.add('hidden');
            }, 6000);
        }

        async function submitJsonForm(form) {
            const button = form.querySelector('button[type="submit"]');
            const originalHtml = button ? button.innerHTML : '';

            if (button) {
                button.disabled = true;
                button.textContent = 'A enviar...';
            }

            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
                    showNotice('error', firstError || data.message || 'Não foi possível enviar. Verifique os dados e tente novamente.');
                    return;
                }

                showNotice('success', data.message || 'Pedido enviado com sucesso.');
                form.reset();
            } catch (error) {
                showNotice('error', 'Erro de ligação. Tente novamente dentro de instantes.');
            } finally {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
            }
        }

        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', (event) => {
                event.preventDefault();
                submitJsonForm(contactForm);
            });
        }

        const newsletterForm = document.getElementById('newsletterForm');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', (event) => {
                event.preventDefault();
                submitJsonForm(newsletterForm);
            });
        }
    </script>
</body>
</html>
