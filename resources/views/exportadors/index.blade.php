<x-app-layout>
    <head>
        <!-- Outros meta tags e links -->
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">
    </head>

    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Pesquisar Exportadores', 'url' => route('exportadors.index')],
        ['name' => 'Novo Exportador', 'url' => route('exportadors.create')]
    ]" separator="/" />

    <div class="container mx-auto px-4 mb-6">
        <!-- Botão para adicionar novo exportador -->
        <a href="{{ route('exportadors.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 mb-4 inline-block">
            Adicionar Exportador
        </a>

        <!-- Campo de Busca Personalizado -->
        <div class="bg-white p-4 rounded-lg shadow">
            <label for="campo-busca" class="block text-sm font-medium text-gray-700">Pesquisar:</label>
            <input type="text" id="campo-busca" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Digite para buscar...">
        </div>

        <!-- Tabela de exportadores -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table id="tabela-exportadores" class="min-w-full">
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
                                <button @click="open = true; exportadorId = {{ $exportador->id }};" class="text-red-500 hover:text-red-700">Excluir</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Feedback de Busca -->
        <div id="feedback-busca" class="hidden mt-2 text-sm text-gray-600"></div>
        <!-- Modal de Confirmação -->
        <div x-data="{ open: false, exportadorId: null }">
            <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center px-4">
                <div class="bg-white rounded-lg p-6 max-w-sm w-full">
                    <h3 class="text-lg font-semibold mb-4">Confirmar Exclusão</h3>
                    <p class="mb-6">Tem certeza que deseja excluir este exportador?</p>
                    <div class="flex justify-end space-x-4">
                        <button @click="open = false" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancelar</button>
                        <form :action="`{{ route('exportadors.destroy', '') }}/${exportadorId}`" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Excluir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script do DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Script do DataTables e Busca Dinâmica -->

    <script>
        $(document).ready(function() {
            var table = $('#tabela-exportadores').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
                },
                "order": [[0, 'asc']],
                "paging": true,
                "searching": true,
                "info": true,
                "lengthChange": true,
                "pageLength": 10,
                "columnDefs": [
                    { "orderable": false, "targets": [5] } // Desativa ordenação na coluna "Ações"
                ],
                "dom": '<"flex justify-between items-center mb-4"lf><"bg-white rounded-lg shadow overflow-hidden"t><"flex justify-between items-center mt-4"ip>',
                "initComplete": function() {
                    $('.dataTables_length select').addClass('border-gray-300 rounded-md shadow-sm');
                    $('.dataTables_filter input').addClass('border-gray-300 rounded-md shadow-sm');
                    $('.dataTables_paginate .paginate_button').addClass('bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600');
                }
            });

            // Vincula o campo de busca personalizado ao DataTables
            $('#campo-busca').on('keyup', function() {
                table.search(this.value).draw();
                if (table.rows({ search: 'applied' }).count() === 0) {
                    $('#feedback-busca').text('Nenhum resultado encontrado.').removeClass('hidden');
                } else {
                    $('#feedback-busca').addClass('hidden');
                }
            });
        });
    </script>
</x-app-layout>