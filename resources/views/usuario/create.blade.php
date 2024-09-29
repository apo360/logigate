<x-app-layout>
<div class="container">
    <h1>Adicionar Usuário</h1>

    <!-- Formulário para adicionar novo usuário -->
    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Senha</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Papel</label>
            <select name="role" class="form-control" required>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Voltar</a>
    </form>
</div>
</x-app-layout>