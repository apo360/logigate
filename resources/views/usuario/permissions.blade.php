<x-app-layout>
    <div class="container">
        <h1>Gerir Permissões para {{ $user->name }}</h1>

        <!-- Formulário para atribuir permissões ao usuário -->
        <form action="{{ route('usuarios.permissions.store', $user->id) }}" method="POST">
            @csrf
            
            <div class="mb-3">
                @foreach ($permissions as $permission)
                    <div class="form-check">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input"
                        {{ $user->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ $permission->name }}</label>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-success">Salvar Permissões</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
</x-app-layout>