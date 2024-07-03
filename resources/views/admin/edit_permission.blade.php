<x-app-layout>
<h1>Editar Permissão</h1>

<form method="POST" action="{{ route('permissions.update', $permission->id) }}">
    @csrf
    @method('PUT')
    <div>
        <label for="name">Nome da Permissão:</label>
        <input type="text" name="name" id="name" value="{{ $permission->name }}" required>
    </div>
    <button type="submit">Atualizar</button>
</form>
</x-app-layout>