<x-app-layout>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        #importacaoModal .modal-body {
            padding: 20px;
        }
        #importacaoModal .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        #importacaoModal .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
    </style>
    
    <div class="py-12">
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')]
    ]" separator="/" />

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

        <div class="card">
            <div class="card-header">
                <div class="float-left"></div>
                <div class="float-right">
                    <div class="btn-group">
                        <a href="{{ route('licenciamentos.create') }}" class="btn btn-sm btn-primary"> <i class="fas fa-plus-circle"></i> Licenciamento</a>
                        <a href="#" id="importacaoBtn" class="btn btn-sm btn-default"> <i class="fas fa-download"></i> Importação</a>
                        <a href="{{ route('licenciamentos.create') }}" class="btn btn-sm btn-default"> <i class="fas fa-upload"></i> Exportação</a>
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
                    <div class="col-md-3">
                        <select name="status_factura" id="status_factura" class="form-control">
                            <option value="" selected>Estado</option>
                            <option value="">Facturas Emitidas C/ Licenciamento</option>
                            <option value="">Facturas Emitidas S/ Licenciamento</option>
                            <option value="">Facturas Pagas</option>
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
                            <a href="{{ route('licenciamentos.exportCsv') }}" class="btn btn-sm btn-default"> <i class="fas fa-file-csv"></i> CSV</a>
                            <a href="{{ route('licenciamentos.exportExcel') }}" class="btn btn-sm btn-default"> <i class="fas fa-file-excel"></i> Excel</a>
                            <a href="" class="btn btn-sm btn-default"><i class="fas fa-file-pdf"></i> PDF</a>
                        </div>
                    </div>
                    <div class="float-right">
                        <span>Nº de Licenciamento: {{count($licenciamentos)}}</span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-stripped" id="licenciamentosTable">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Descrição</th>
                                <th>Peso Bruto</th>
                                <th>Origem</th>
                                <th>Estado</th>
                                <th>CIF</th>
                                <th>Factura</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($licenciamentos as $licenciamento)
                            <tr>
                                <td>
                                    <div class="text-wrap">
                                        <a href="{{ route('customers.show', $licenciamento->cliente->id) }}">
                                            {{ $licenciamento->cliente->CompanyName }}
                                        </a>
                                        @if($licenciamento->referencia_cliente)
                                            <small>({{ $licenciamento->referencia_cliente }})</small>
                                        @endif
                                        <br>
                                        <span class="badge bg-success mt-1">{{ $licenciamento->codigo_licenciamento }}</span>
                                    </div>
                                </td>

                                <td>{{ $licenciamento->descricao }}</td>
                                <td>
                                    {{ number_format($licenciamento->peso_bruto < 1000 ? $licenciamento->peso_bruto : $licenciamento->peso_bruto / 1000, 2, ',', '.') }}
                                    {{ $licenciamento->peso_bruto < 1000 ? 'Kg' : 'Ton' }}
                                </td>

                                <td>{{ $licenciamento->porto_origem }}</td>
                                <td>
                                    <span class="badge badge-{{ $licenciamento->txt_gerado === 1 ? 'success' : 'secondary' }}">
                                        {{ ucfirst($licenciamento->estado_licenciamento) }}
                                    </span>
                                </td>
                                <td>
                                    <!-- Badge circular com a moeda -->
                                    <span class="badge rounded-circle" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; background-color: #007bff; color: black;">
                                        {{ $licenciamento->moeda }}
                                    </span>
                                    <!-- Valor do CIF formatado -->
                                    <span class="ms-2">
                                        {{ number_format($licenciamento->cif, 2, ',', '.') }}
                                    </span>
                                </td>

                                <!-- Exibir status da fatura -->
                                @if($licenciamento->procLicenFaturas->isNotEmpty())
                                    @php
                                        $statusFatura = $licenciamento->procLicenFaturas->last()->status_fatura;
                                    @endphp
                                    <td>{{ ucfirst($statusFatura) }} <br>
                                        <span><a href="{{ route('documentos.show', $licenciamento->procLicenFaturas->last()->fatura_id) }}">{{$licenciamento->Nr_factura}}</a></span>
                                    </td>
                                @else
                                    <td>{{ __('Sem Factura') }}</td>
                                @endif
                                <td>
                                    <div class="btn-group" role="group">
                                        <div class="btn-group" role="group">
                                            <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-default dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                Opções
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                <li><a href="{{ route('licenciamentos.edit', $licenciamento->id) }}" class="dropdown-item btn btn-sm btn-primary"> <i class="fas fa-edit"></i> Editar</a></li>
                                                <li><a href="{{ route('licenciamentos.show', $licenciamento->id) }}" class="dropdown-item btn btn-sm btn-primary"> <i class="fas fa-eye"></i> Visualizar</a></li>
                                                <li><a href="{{ route('licenciamentos.destroy', $licenciamento->id) }}" class="dropdown-item btn btn-sm btn-primary"> <i class="fas fa-trash"></i> Excluir</a></li>
                                            </ul>
                                        </div>
                                    </div>
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

    <!-- Modal -->
    <div class="modal fade" id="importacaoModal" tabindex="-1" role="dialog" aria-labelledby="importacaoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importacaoModalLabel">Importar Ficheiro</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Por favor, faça upload de um ficheiro no formato <strong>CSV</strong> ou <strong>Excel</strong>.</p>
                    
                    <!-- Instruções de campos necessários -->
                    <div class="alert alert-info">
                        <h6>Campos necessários no ficheiro:</h6>
                        <ul>
                            <li><strong>Referência:</strong> Código único para cada item.</li>
                            <li><strong>Cliente:</strong> Nome completo do cliente.</li>
                            <li><strong>Descrição:</strong> Descrição do licenciamento.</li>
                            <li><strong>CIF:</strong> Valor da fatura.</li>
                            <li><strong>Estado:</strong> Estado da fatura (Emitida, Paga, etc.).</li>
                        </ul>
                    </div>

                    <!-- Formulário de Upload -->
                    <form id="uploadForm" action="{{ route('licenciamentos.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="fileInput">Escolher Ficheiro</label>
                            <input type="file" name="file" id="fileInput" class="form-control-file" accept=".csv, .xls, .xlsx" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Importar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Ensure you have included jQuery and Bootstrap JS at the end of the body tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Script do modal de importação -->
    <script>
        $(document).ready(function() {
            // Abrir o modal ao clicar no botão de Importação
            $('#importacaoBtn').on('click', function(e) {
                e.preventDefault();
                $('#importacaoModal').modal('show');
            });

            // Validação e envio do formulário via AJAX (opcional, se não for necessário o envio tradicional)
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Processar a resposta, por exemplo, exibindo uma mensagem de sucesso
                        alert('Ficheiro importado com sucesso!');
                        $('#importacaoModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        // Tratar erros
                        alert('Erro ao importar o ficheiro. Por favor, tente novamente.');
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Filtro de Pesquisa
            $('#search').on('input', function() {
                var searchTerm = $(this).val().toLowerCase();

                // Filtrar as linhas da tabela
                $('#licenciamentosTable tbody tr').each(function() {
                    var row = $(this);
                    var cliente = row.find('td').eq(0).text().toLowerCase(); // Nome do Cliente
                    var descricao = row.find('td').eq(1).text().toLowerCase(); // Descrição
                    var referencia = row.find('td').eq(0).text().toLowerCase(); // Referência do Cliente

                    // Verificar se algum dos campos corresponde ao termo de pesquisa
                    if (cliente.includes(searchTerm) || descricao.includes(searchTerm) || referencia.includes(searchTerm)) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
            });

            // Filtro de Status de Fatura
            $('#status_factura').on('change', function() {
                var status = $(this).val();

                $('#licenciamentosTable tbody tr').each(function() {
                    var row = $(this);
                    var estadoFatura = row.find('td').eq(4).text().trim().toLowerCase(); // Estado da Fatura

                    // Filtra as linhas baseadas no status selecionado
                    if (status === '') {
                        row.show();
                    } else if (status === 'emitida_com_licenciamento' && estadoFatura === 'factura emitida com licenciamento') {
                        row.show();
                    } else if (status === 'emitida_sem_licenciamento' && estadoFatura === 'factura emitida sem licenciamento') {
                        row.show();
                    } else if (status === 'pagas' && estadoFatura === 'factura paga') {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
            });

            // Filtro de Ordenação
            $('#sort_by').on('change', function() {
                var sortBy = $(this).val();
                var rows = $('#licenciamentosTable tbody tr').get();

                rows.sort(function(a, b) {
                    var cellA = $(a).find('td').eq(5).text().replace(/[^\d.-]/g, ''); // Coluna CIF
                    var cellB = $(b).find('td').eq(5).text().replace(/[^\d.-]/g, ''); // Coluna CIF

                    if (sortBy === 'preco_asc') {
                        return parseFloat(cellA) - parseFloat(cellB);
                    } else if (sortBy === 'preco_desc') {
                        return parseFloat(cellB) - parseFloat(cellA);
                    } else if (sortBy === 'maior') {
                        return parseFloat(cellB) - parseFloat(cellA);
                    } else if (sortBy === 'menor') {
                        return parseFloat(cellA) - parseFloat(cellB);
                    } else {
                        return 0;
                    }
                });

                // Reanexar as linhas ordenadas
                $.each(rows, function(index, row) {
                    $('#licenciamentosTable tbody').append(row);
                });
            });
        });
    </script>

</x-app-layout>
