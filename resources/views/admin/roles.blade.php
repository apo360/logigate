<x-app-layout>
<h1>Lista de Papéis</h1>

<ul>
    @foreach($roles as $role)
        <li>{{ $role->name }}</li>
    @endforeach
</ul>
</x-app-layout>