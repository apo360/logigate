<x-app-layout>
    <x-slot name="header">
        <span class="text-sm font-semibold text-slate-900">Dashboard</span>
    </x-slot>

    @php
        $user = auth()->user();
        $empresa = $user?->empresas->first();
        $sections = [
            ['id' => 'visao-geral', 'label' => 'Visão Geral'],
            ['id' => 'operacoes', 'label' => 'Operações'],
            ['id' => 'financeiro', 'label' => 'Financeiro'],
            ['id' => 'aduaneiro', 'label' => 'Aduaneiro'],
            ['id' => 'alertas', 'label' => 'Alertas'],
        ];
    @endphp

    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')]
    ]" separator="/" />

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-4 sm:px-6 lg:px-0">
        <!-- Painel de boas-vindas + ações -->
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ now()->translatedFormat('d \\d\\e F, Y') }}
                    </p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">
                        Olá, {{ $user?->name ?? 'utilizador' }}
                    </h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        {{ $empresa?->Empresa ?? 'Empresa não definida' }} · visão operacional dos processos, clientes, facturação e obrigações aduaneiras.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:flex sm:flex-wrap lg:justify-end">
                    <a href="{{ route('processos.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-plus mr-2 text-xs"></i> Novo processo
                    </a>
                    <a href="{{ route('customers.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-user-plus mr-2 text-xs"></i> Novo cliente
                    </a>
                </div>
            </div>

            <!-- Atalhos rápidos em grid melhorada -->
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('processos.index') }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-300 hover:bg-blue-50/30 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                        <i class="fas fa-tasks text-lg"></i>
                    </div>
                    <div>
                        <span class="block font-bold text-slate-950">Processos</span>
                        <span class="block text-xs text-slate-500">Pesquisar, editar e acompanhar</span>
                    </div>
                </a>
                <a href="{{ route('customers.index') }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-300 hover:bg-blue-50/30 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                    <div>
                        <span class="block font-bold text-slate-950">Clientes</span>
                        <span class="block text-xs text-slate-500">Carteira, conta corrente</span>
                    </div>
                </a>
                <a href="{{ route('factura.estatistica') }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-300 hover:bg-blue-50/30 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                        <i class="fas fa-chart-line text-lg"></i>
                    </div>
                    <div>
                        <span class="block font-bold text-slate-950">Facturação</span>
                        <span class="block text-xs text-slate-500">Receita, cobrança, desempenho</span>
                    </div>
                </a>
                <a href="{{ route('consultar.pauta') }}" class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-300 hover:bg-blue-50/30 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                        <i class="fas fa-book-open text-lg"></i>
                    </div>
                    <div>
                        <span class="block font-bold text-slate-950">Pauta Aduaneira</span>
                        <span class="block text-xs text-slate-500">Consulta de classificação pautal</span>
                    </div>
                </a>
            </div>
        </section>

        <!-- Navegação âncora (sticky, sem animações) -->
        <nav class="sticky top-0 z-10 -mx-4 border-y border-slate-200 bg-white/95 px-4 py-2 backdrop-blur-sm sm:-mx-6 sm:px-6 lg:mx-0 lg:rounded-xl lg:border lg:px-4" aria-label="Secções do dashboard">
            <div class="flex gap-1 overflow-x-auto">
                @foreach($sections as $section)
                    <a href="#{{ $section['id'] }}" class="section-nav-link whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ $section['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>

        <livewire:onboarding.onboarding-wizard />

        <!-- Secção: Visão Geral -->
        <section id="visao-geral" class="scroll-mt-24 space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Visão Geral</p>
                    <h2 class="text-2xl font-bold text-slate-950">Resumo executivo</h2>
                </div>
                <a href="{{ route('licenciamento.estatistica') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-700 hover:text-blue-800">
                    Ver estatísticas de licenciamento <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <livewire:dashboard.dashboard-kpis />
            </div>
        </section>

        <!-- Secção: Operações -->
        <section id="operacoes" class="scroll-mt-24 space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Operações</p>
                    <h2 class="text-2xl font-bold text-slate-950">Processos e actividade recente</h2>
                </div>
                <div class="flex gap-4 text-sm font-semibold">
                    <a href="{{ route('processos.estatistica') }}" class="text-blue-700 hover:text-blue-800">Estatísticas</a>
                    <a href="{{ route('processos.index') }}" class="text-slate-600 hover:text-slate-950">Listagem</a>
                </div>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <livewire:dashboard.processos-chart />
            </div>
        </section>

        <!-- Secção: Financeiro -->
        <section id="financeiro" class="scroll-mt-24 space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Financeiro</p>
                    <h2 class="text-2xl font-bold text-slate-950">Receita, clientes e cobrança</h2>
                </div>
                <a href="{{ route('factura.estatistica') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-700 hover:text-blue-800">
                    Ver facturação detalhada <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <livewire:dashboard.finance-chart />
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <livewire:dashboard.top-clientes-widget />
            </div>
        </section>

        <!-- Secção: Aduaneiro -->
        <section id="aduaneiro" class="scroll-mt-24 space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Aduaneiro</p>
                    <h2 class="text-2xl font-bold text-slate-950">Mercadorias, pauta e direitos</h2>
                </div>
                <a href="{{ route('consultar.pauta') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-700 hover:text-blue-800">
                    Consultar pauta <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <livewire:dashboard.mercadorias-chart />
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <livewire:dashboard.direitos-aduaneiros-widget />
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <livewire:dashboard.previsao-receita-widget />
            </div>
        </section>

        <!-- Secção: Alertas -->
        <section id="alertas" class="scroll-mt-24 space-y-4 pb-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Alertas</p>
                    <h2 class="text-2xl font-bold text-slate-950">Pendências que precisam de atenção</h2>
                </div>
                <a href="{{ route('licenciamentos.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-700 hover:text-blue-800">
                    Ver licenciamentos <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <livewire:dashboard.alertas-operacionais-widget />
            </div>
        </section>
    </div>

    <!-- Estilos exclusivos para dashboard (sem animações) -->
    <style>
        /* Base melhorada – apenas ajustes visuais estáticos */
        body {
            background: #f1f5f9;
        }
        /* Cards e containers com bordas suaves */
        .shadow-sm {
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.03), 0 1px 3px 0 rgb(0 0 0 / 0.08);
        }
        .rounded-xl {
            border-radius: 0.75rem;
        }
        /* Links da navegação ativa (só muda de cor instantaneamente) */
        .section-nav-link {
            transition: none;
        }
        /* Melhoria de espaçamento para livewire widgets */
        livewire\:dashboard\.dashboard-kpis,
        livewire\:dashboard\.processos-chart,
        livewire\:dashboard\.finance-chart,
        livewire\:dashboard\.top-clientes-widget,
        livewire\:dashboard\.mercadorias-chart,
        livewire\:dashboard\.direitos-aduaneiros-widget,
        livewire\:dashboard\.previsao-receita-widget,
        livewire\:dashboard\.alertas-operacionais-widget {
            display: block;
            width: 100%;
        }
        /* Botões e interações sem transição */
        a, button {
            transition: none;
        }
        /* Tooltips nativos mantidos */
        .hover\:bg-blue-800:hover {
            background-color: #1e3a8a;
        }
        /* Ícones nos atalhos */
        .group:hover .rounded-lg {
            background-color: #dbeafe;
        }
        /* Ajuste para breadcrumb */
        .breadcrumb {
            font-size: 0.875rem;
        }
        /* Espaçamento extra para o conteúdo principal */
        .max-w-7xl {
            max-width: 80rem;
        }
        /* Badge de notificações se existir, mantém estilo consistente */
        .ring-1 {
            ring-color: #e2e8f0;
        }
    </style>

    <!-- Script simples para destacar secção activa na navegação (sem animações, apenas troca de classe) -->
    <script>
        (function() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.section-nav-link');

            function setActiveLink() {
                let current = '';
                const scrollPos = window.scrollY + 130;
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionBottom = sectionTop + section.offsetHeight;
                    if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                        current = section.getAttribute('id');
                    }
                });
                navLinks.forEach(link => {
                    link.classList.remove('bg-slate-100', 'text-slate-950');
                    const href = link.getAttribute('href').substring(1);
                    if (href === current) {
                        link.classList.add('bg-slate-100', 'text-slate-950');
                    }
                });
            }
            window.addEventListener('scroll', setActiveLink);
            window.addEventListener('load', setActiveLink);
        })();
    </script>
</x-app-layout>
