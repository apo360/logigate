<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Usuários', 'url' => route('usuarios.index')],
        ['name' => 'Detalhe do Usuário', 'url' => '']
    ]" separator="/" />

    <div class="container-fluid mt-4">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="rounded-circle mr-2" width="40" height="40">
                <strong>{{ $user->name }}</strong>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Funções:</strong> {{ $user->roles->pluck('name')->implode(', ') ?: 'Sem função' }}</p>
                <p><strong>Última sessão:</strong> {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}</p>
                <p><strong>Criado em:</strong> {{ $user->created_at?->format('d/m/Y H:i') }}</p>
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
