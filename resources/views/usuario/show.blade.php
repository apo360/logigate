<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Usuários', 'url' => route('usuarios.index')],
        ['name' => 'Detalhe do Usuário', 'url' => '']
    ]" separator="/" />

    <div class="container-fluid mt-4">
        <div class="mb-4 rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-12 w-12 rounded-full object-cover">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $user->name }}</h1>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ $user->email }}</p>
                    </div>
                </div>

                @if($user->is_blocked)
                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-700 dark:bg-amber-950/50 dark:text-amber-200">Bloqueado</span>
                @elseif($user->is_active)
                    <span class="inline-flex rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700 dark:bg-green-950/50 dark:text-green-200">Activo</span>
                @else
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">Inactivo</span>
                @endif
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-3">
                <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                    <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Funções</p>
                    <p class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $user->roles->pluck('name')->implode(', ') ?: 'Sem função' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                    <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Última sessão</p>
                    <p class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                    <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Criado em</p>
                    <p class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $user->created_at?->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <livewire:empresa.empresa-user-permissions :empresa="$empresa" :user="$user" />
            </div>
            <div class="col-lg-4 mb-4">
                <livewire:empresa.empresa-user-security :empresa="$empresa" :user="$user" />
            </div>
        </div>
    </div>
</x-app-layout>
