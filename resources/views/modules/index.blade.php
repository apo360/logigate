<x-app-layout>
<div class="container">
    <h1>Módulos Disponíveis</h1>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Preço</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            @foreach($modules as $module)
                <tr>
                    <td>{{ $module->id }}</td>
                    <td>{{ $module->module_name }}</td>
                    <td>{{ $module->description }}</td>
                    <td>{{ $module->price }}</td>
                    <td>
                        <form action="{{ route('modules.subscribe') }}" method="POST">
                            @csrf
                            <input type="hidden" name="module_id" value="{{ $module->id }}">
                            <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">
                            <button type="submit" class="btn btn-primary">Subscrever</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</x-app-layout>