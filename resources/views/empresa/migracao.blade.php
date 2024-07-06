<!-- resources/views/imports.blade.php -->
<x-app-layout>
    <x-import-form route="{{ route('import.customers') }}" buttonText="Importar Clientes" texto="Importar dados de clientes para o sistema." />
    <x-import-form route="{{ route('import.exportadores') }}" buttonText="Importar Exportadores" texto="Importar dados dos Exportadores para o sistema." />
    <x-import-form route="{{ route('import.processos') }}" buttonText="Importar Processos" texto="Importar Processos para o Sistema." />

    @if(session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @if($errors->any())
        <div>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h2>Status das Importações</h2>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Status</th>
                <th>Criado em</th>
            </tr>
        </thead>
        <tbody>
            @foreach($imports as $import)
                <tr>
                    <td>{{ $import->type }}</td>
                    <td>{{ $import->status }}</td>
                    <td>{{ $import->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-app-layout>

