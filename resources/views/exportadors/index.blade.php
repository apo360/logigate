<x-app-layout>
    <!-- resources/views/exportadors/index.blade.php -->
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Pesquisar Exportadores', 'url' => route('exportadors.index')],
        ['name' => 'Novo Exportador', 'url' => route('exportadors.create')]
    ]" separator="/" />

<div class="container mx-auto px-4">

    <!-- Botão para adicionar novo exportador -->
    <a href="{{ route('exportadors.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 mb-4 inline-block">
        Adicionar Exportador
    </a>

    <!-- Tabela de exportadores -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exportador</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endereço</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($exportadors as $exportador)
                    <tr>
                        <td class="px-6 py-4">{{ $exportador->Exportador }}</td>
                        <td class="px-6 py-4">{{ $exportador->ExportadorTaxID }}</td>
                        <td class="px-6 py-4">{{ $exportador->Endereco }}</td>
                        <td class="px-6 py-4">{{ $exportador->Telefone }}</td>
                        <td class="px-6 py-4">{{ $exportador->Email }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('exportadors.edit', $exportador->id) }}" class="text-blue-500 hover:text-blue-700 mr-2">Editar</a>
                            <form action="{{ route('exportadors.destroy', $exportador->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-app-layout>