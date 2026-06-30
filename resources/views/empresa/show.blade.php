<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Empresa', 'url' => route('empresas.edit', $empresa->id)],
        ['name' => 'Resumo', 'url' => '']
    ]" separator="/" />

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Empresa</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">{{ $empresa->Empresa }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Resumo dos dados principais associados à empresa activa.
                    </p>
                </div>
                <a href="{{ route('empresas.edit', $empresa->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    <i class="fas fa-pen"></i>
                    Configurar empresa
                </a>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="flex items-center gap-4">
                    @if($empresa->Logotipo)
                        <img src="{{ $empresa->Logotipo }}" alt="Logotipo de {{ $empresa->Empresa }}" class="h-16 w-16 rounded-lg border border-slate-200 object-cover dark:border-slate-700">
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                            <i class="fas fa-building text-2xl"></i>
                        </div>
                    @endif
                    <div>
                        <h2 class="font-semibold text-slate-950 dark:text-white">{{ $empresa->Empresa }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $empresa->Designacao ?? 'Sem designação' }}</p>
                    </div>
                </div>
            </article>

            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">NIF</p>
                <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ $empresa->NIF ?? 'N/D' }}</p>
            </article>

            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Conta LogiGate</p>
                <p class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">{{ $empresa->conta ?? 'N/D' }}</p>
            </article>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h2 class="text-base font-semibold text-slate-950 dark:text-white">Dados de contacto</h2>
            <dl class="mt-4 grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="font-medium text-slate-500 dark:text-slate-400">Email</dt>
                    <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $empresa->Email ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-500 dark:text-slate-400">Contacto móvel</dt>
                    <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $empresa->Contacto_movel ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-500 dark:text-slate-400">Cédula</dt>
                    <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $empresa->Cedula ?? 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-slate-500 dark:text-slate-400">Endereço</dt>
                    <dd class="mt-1 text-slate-800 dark:text-slate-200">{{ $empresa->Endereco_completo ?? 'N/D' }}</dd>
                </div>
            </dl>
        </section>
    </div>
</x-app-layout>
