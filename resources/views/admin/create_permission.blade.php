<x-app-layout>
<h1>Criar Permissão</h1>

<form method="POST" action="{{ route('permissions.store') }}">
    @csrf
    <div>
        <label for="name">Nome da Permissão:</label>
        <input type="text" name="name" id="name" required>
    </div>
    <button type="submit">Criar</button>
</form>
</x-app-layout>