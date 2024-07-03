<x-app-layout>
<h1>Editar Papel</h1>

<form method="POST" action="{{ route('roles.update', $role->id) }}">
    @csrf
    @method('PUT')
    <div>
        <label for="name">Nome do Papel:</label>
        <input type="text" name="name" id="name" value="{{ $role->name }}" required>
    </div>
    <div>
        <label>Permissões:</label>
        @foreach($permissions as $permission)
            <div>
                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                    {{ $role->permissions->contains($permission) ? 'checked' : '' }}>
                <label>{{ $permission->name }}</label>
            </div>
        @endforeach
    </div>
    <button type="submit">Atualizar</button>
</form>
</x-app-layout>