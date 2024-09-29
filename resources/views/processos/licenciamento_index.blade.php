<x-app-layout>
    <div class="py-12">
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pesquisa de Licenciamentos') }}
            </h2>
        </x-slot>

        <div class="card">
            <div class="card-header">
                <div class="float-left"></div>
                <div class="float-right">
                    <div class="btn-group">
                        <a href="{{ route('licenciamentos.create') }}" class="btn btn-sm btn-primary">Novo Licenciamento</a>
                        <a href="{{ route('licenciamentos.create') }}" class="btn btn-sm btn-primary">Impotação</a>
                        <a href="{{ route('licenciamentos.create') }}" class="btn btn-sm btn-primary">Exportação</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <x-input type="text" id="search" placeholder="Pesquisar Licenciamento por: Referência, Cliente, Exportador" class="form-control" />
                    </div>
                    <div class="col-md-2">
                        <select name="taxa_iva" id="taxa_iva" class="form-control">
                            <option value="" selected>Todas as Taxas</option>
                    
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status_factura" id="status_factura" class="form-control">
                            <option value="" selected>Todos os Tipos</option>
                            
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="sort_by" class="form-control">
                            <option value="">Ordenar por...</option>
                            <option value="preco_asc">Preço Ascendente</option>
                            <option value="preco_desc">Preço Descendente</option>
                            <option value="maior">Maior Facturação</option>
                            <option value="menor">Menor Facturação</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header">
                    <div class="float-left">
                        <div class="btn-group">
                            <a href="" class="btn btn-sm btn-default"> <i class="fas fa-file-csv"></i> CSV</a>
                            <a href="" class="btn btn-sm btn-default"> <i class="fas fa-file-excel"></i> Excel</a>
                            <a href="" class="btn btn-sm btn-default"><i class="fas fa-file-pdf"></i> PDF</a>
                            <a href="" class="btn btn-sm btn-default"><i class="fas fa-print"></i> Imprimir</a>
                        </div>
                    </div>
                    <div class="float-right">
                        <span>Nº de Licenciamento: {{count($licenciamentos)}}</span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-stripped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Exportador</th>
                                <th>Descrição</th>
                                <th>Status Fatura</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($licenciamentos as $licenciamento)
                            <tr>
                                <td>{{ $licenciamento->codigo_licenciamento }}</td>
                                <td>{{ $licenciamento->cliente->CompanyName }}</td>
                                <td>{{ $licenciamento->exportador->Exportador }}</td>
                                <td>{{ $licenciamento->descricao }}</td>
                                <!-- Exibir status da fatura -->
                                @if($licenciamento->procLicenFaturas->isNotEmpty())
                                    @php
                                        // Verificar o status da fatura mais recente
                                        $statusFatura = $licenciamento->procLicenFaturas->last()->status_fatura;
                                    @endphp
                                    <td>{{ ucfirst($statusFatura) }}</td>
                                @else
                                    <td>Sem Fatura</td>
                                @endif
                                <td>
                                    <!-- Mostrar ou desativar o botão de edição com base no status da fatura -->
                                    @if ($licenciamento->procLicenFaturas->whereIn('status_fatura', ['emitida', 'paga'])->isNotEmpty())
                                        <button class="btn btn-secondary" disabled>Editar</button>
                                    @else
                                        <a href="{{ route('licenciamentos.edit', $licenciamento->id) }}" class="btn btn-primary">Editar</a>
                                    @endif

                                    <!-- Mostrar botão para visualizar detalhes -->
                                    <a href="{{ route('licenciamentos.show', $licenciamento->id) }}" class="btn btn-info">Visualizar</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <!-- Paginação -->
                    {{ $licenciamentos->links() }}
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Capturar eventos de mudança nos filtros
            $('#search, #ProductType, #sort_by').on('change keyup', function() {
                fetchLicenciamentos();
            });

            // Função para buscar licenciamentos com AJAX
            function fetchLicenciamentos() {
                var search = $('#search').val();
                var productType = $('#ProductType').val();
                var sortBy = $('#sort_by').val();

                $.ajax({
                    url: "",  // Rota para o backend
                    type: 'GET',
                    data: {
                        search: search,
                        productType: productType,
                        sortBy: sortBy
                    },
                    success: function(response) {
                        // Substituir a tabela com os dados filtrados
                        $('#licenciamentos_table').html(response.html);

                        // Atualizar a contagem de licenciamentos
                        $('#count').text(response.count);

                        // Atualizar links de paginação
                        $('#pagination_links').html(response.pagination);
                    }
                });
            }
        });

    </script>
</x-app-layout>
