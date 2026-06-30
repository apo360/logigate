<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Configurar', 'url' => '']
    ]" separator="/" />

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Empresa</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">{{ $empresa->Empresa }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Gerencie o perfil institucional, logotipo, contas bancárias e acessos ligados à empresa activa.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('empresas.show', $empresa->id) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        <i class="fas fa-eye"></i>
                        Ver resumo
                    </a>
                    <a href="{{ route('integracoes.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                        <i class="fas fa-plug"></i>
                        Integrações
                    </a>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 shadow-sm dark:border-green-900/60 dark:bg-green-950/40 dark:text-green-200">
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <details class="group rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900" open>
                    <summary class="flex cursor-pointer items-center justify-between p-5 text-base font-semibold text-slate-900 hover:bg-slate-50 dark:text-white dark:hover:bg-slate-800">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-image text-blue-600"></i>
                            Logotipo
                        </span>
                        <span class="text-slate-400 transition-transform group-open:rotate-180">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </summary>
                    <div class="border-t border-slate-100 p-5 dark:border-slate-800">
                        <livewire:empresa.empresa-logo :empresa="$empresa" />
                    </div>
                </details>
            </div>

            <div class="lg:col-span-2">
                <details class="group rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900" open>
                    <summary class="flex cursor-pointer items-center justify-between p-5 text-base font-semibold text-slate-900 hover:bg-slate-50 dark:text-white dark:hover:bg-slate-800">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-info-circle text-blue-600"></i>
                            Perfil e dados fiscais
                        </span>
                        <span class="text-slate-400 transition-transform group-open:rotate-180">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </summary>
                    <div class="border-t border-slate-100 p-5 dark:border-slate-800">
                        <livewire:empresa.empresa-profile :empresa="$empresa" />
                    </div>
                </details>
            </div>
        </div>

        <div>
            <details class="group rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900" open>
                <summary class="flex cursor-pointer items-center justify-between p-5 text-base font-semibold text-slate-900 hover:bg-slate-50 dark:text-white dark:hover:bg-slate-800">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-university text-blue-600"></i>
                        Contas bancárias
                    </span>
                    <span class="text-slate-400 transition-transform group-open:rotate-180">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </summary>
                <div class="border-t border-slate-100 p-5 dark:border-slate-800">
                    <livewire:empresa.empresa-contas-bancarias :empresa="$empresa" />
                </div>
            </details>
        </div>

        <div>
            <details class="group rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <summary class="flex cursor-pointer items-center justify-between p-5 text-base font-semibold text-slate-900 hover:bg-slate-50 dark:text-white dark:hover:bg-slate-800">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-users text-blue-600"></i>
                        Utilizadores da empresa
                    </span>
                    <span class="text-slate-400 transition-transform group-open:rotate-180">
                        <i class="fas fa-chevron-down"></i>
                    </span>
                </summary>
                <div class="border-t border-slate-100 p-5 dark:border-slate-800">
                    <livewire:empresa.empresa-users :empresa="$empresa" />
                </div>
            </details>
        </div>
    </div>
</x-app-layout>
