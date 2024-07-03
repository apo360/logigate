
<h1>Lista de Permissões</h1>

<ul>
    @foreach($permissions as $permission)
        <li>{{ $permission->name }}</li>
    @endforeach
</ul>
