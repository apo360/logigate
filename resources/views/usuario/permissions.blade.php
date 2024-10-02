<x-app-layout>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Gerir Permissões para {{ $user->name }}</h1>

        <!-- Formulário para atribuir permissões ao usuário -->
        <form action="{{ route('usuarios.permissions.store', $user->id) }}" method="POST" class="bg-light p-4 rounded shadow">
            @csrf
            
            <div class="mb-4">
                <h5>Permissões</h5>
                @foreach ($permissions as $permission)
                    <div class="form-check">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input"
                        {{ $user->hasPermissionTo($permission->name) ? 'checked' : '' }} id="permission_{{ $permission->name }}">
                        <label class="form-check-label" for="permission_{{ $permission->name }}">{{ $permission->name }}</label>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-success w-100 mb-2">Salvar Permissões</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary w-100">Voltar</a>
        </form>
    </div>

    <style>
        .container {
            max-width: 600px; /* Limitar a largura do formulário */
            margin: auto; /* Centralizar o formulário */
        }

        .bg-light {
            background-color: #f8f9fa; /* Fundo claro */
        }

        .form-check {
            margin-bottom: 10px; /* Espaçamento entre as permissões */
        }

        .btn {
            transition: background-color 0.3s, transform 0.3s; /* Transição suave para interações */
        }

        .btn:hover {
            background-color: #0056b3; /* Cor ao passar o mouse */
            transform: scale(1.05); /* Efeito de aumento */
        }
    </style>
</x-app-layout>
