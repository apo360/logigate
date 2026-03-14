<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')]
    ]" separator="/" />

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.18),_transparent_36%),linear-gradient(180deg,_#f8fbff_0%,_#eef6ff_55%,_#f7fafc_100%)] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-8">
            <section class="rounded-[2rem] border border-white/60 bg-white/80 p-8 shadow-xl shadow-slate-200/60 backdrop-blur">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div class="max-w-3xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-cyan-600">SaaS Control Center</p>
                        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Operação aduaneira, finanças e subscrição em um único cockpit</h1>
                        <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base">
                            O painel principal foi reorganizado em widgets modulares para suportar onboarding, execução operacional,
                            leitura estratégica da facturação, inteligência aduaneira e previsões de carga.
                        </p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('processos.estatistica') }}" class="rounded-2xl bg-slate-900 px-5 py-4 text-sm font-semibold text-white shadow-lg shadow-slate-300 transition hover:-translate-y-0.5 hover:bg-slate-800">
                            Ver estatísticas operacionais
                        </a>
                        <a href="{{ route('factura.estatistica') }}" class="rounded-2xl bg-cyan-500 px-5 py-4 text-sm font-semibold text-white shadow-lg shadow-cyan-200 transition hover:-translate-y-0.5 hover:bg-cyan-600">
                            Ver facturação detalhada
                        </a>
                    </div>
                </div>
            </section>

            <livewire:onboarding.onboarding-wizard />

            <section class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">1. KPIs</p>
                    <h2 class="text-2xl font-black text-slate-900">Visão executiva</h2>
                </div>
                <livewire:dashboard.dashboard-kpis />
            </section>

            <section class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">2. Operations</p>
                    <h2 class="text-2xl font-black text-slate-900">Dashboard operacional</h2>
                </div>
                <livewire:dashboard.processos-chart />
            </section>

            <section class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">3. Financial</p>
                    <h2 class="text-2xl font-black text-slate-900">Dashboard estratégico</h2>
                </div>
                <livewire:dashboard.finance-chart />
                <livewire:dashboard.top-clientes-widget />
            </section>

            <section class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">4. Customs</p>
                    <h2 class="text-2xl font-black text-slate-900">Inteligência aduaneira</h2>
                </div>
                <livewire:dashboard.mercadorias-chart />
                <livewire:dashboard.direitos-aduaneiros-widget />
            </section>

            <section class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">5. Forecast</p>
                    <h2 class="text-2xl font-black text-slate-900">Previsão e capacidade</h2>
                </div>
                <livewire:dashboard.previsao-receita-widget />
            </section>

            <section class="space-y-4 pb-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">6. Alerts</p>
                    <h2 class="text-2xl font-black text-slate-900">Alertas de operação</h2>
                </div>
                <livewire:dashboard.alertas-operacionais-widget />
            </section>
        </div>
    </div>
</x-app-layout>
