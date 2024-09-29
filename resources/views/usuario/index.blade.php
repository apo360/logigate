<x-app-layout>
<div class="container">
    <h1>Lista de Usuários</h1>
    
    <!-- Botão para criar novo usuário -->
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary mb-3">Adicionar Usuário</a>

    <!-- Tabela de Usuários -->
    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Papel</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->roles->pluck('name')->first() }}</td>
                <td>
                    <a href="{{ route('usuarios.permissions', $user->id) }}" class="btn btn-secondary">Gerir Permissões</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</x-app-layout>