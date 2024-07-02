<x-app-layout>
<h1>Criar Papel</h1>

<form method="POST" action="{{ route('roles.store') }}">
    @csrf
    <div>
        <label for="name">Nome do Papel:</label>
        <input type="text" name="name" id="name" required>
    </div>
    <div>
        <label>Permissões:</label>
        @foreach($permissions as $permission)
            <div>
                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}">
                <label>{{ $permission->name }}</label>
            </div>
        @endforeach
    </div>
    <button type="submit">Criar</button>
</form>
</x-app-layout>