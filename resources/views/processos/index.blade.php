<x-app-layout>
    <style>
        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.375rem;
        }

        .badge-warning {
            color: #856404;
            background-color: #fff3cd;
        }

        .badge-success {
            color: #155724;
            background-color: #d4edda;
        }

        .badge-danger {
            color: #721c24;
            background-color: #f8d7da;
        }

        .d-inline-flex {
            display: inline-flex !important;
            align-items: center;
        }

        .mr-1 {
            margin-right: 0.25rem;
        }

        .bg-dark-blue {
            background-color: #1b3a57; /* Azul-escuro */
            color: white; /* Texto branco para melhor contraste */
        }

        .bg-darker-blue:hover {
            background-color: #142a40; /* Azul-escuro mais forte para hover */
        }

    </style>

    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processos', 'url' => route('processos.index')]
    ]" separator="/" />
    <div class="" style="padding: 10px;"> 

        <div class="card">
            <div class="card-header">
                <div class="float-left"></div>
                <div class="float-right">
                    <!-- Botão Cadastro de Processos -->
                    <div class="btn-group">
                        <a href="{{ route('processos.create') }}" class="btn btn-primary btn-sm mb-3">Novo Processo</a>
                        <a href="{{ route('licenciamentos.create') }}" class="btn btn-sm btn-default mb-3">Licenciamento</a>
                        <a href="" class="btn btn-success btn-sm mb-3">Exportar Dados</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <x-input type="text" id="search" placeholder="Pesquisar por Cliente, NIF, Nº Processo" class="form-control" />
                    </div>
                    <div class="col-md-2">
                        <x-input type="date" id="search_date" class="form-control" />
                    </div>
                    <div class="col-md-3">
                        <select name="Situacao" class="form-control">
                            <option value="" selected>Todos</option>
                            <option value="Aberto">Aberto</option>
                            <option value="Em curso">Em curso</option>
                            <option value="Alfandega">Alfandega</option>
                            <option value="Desafaldegamento">Desafaldegamento</option>
                            <option value="Inspensão">Inspensão</option>
                            <option value="Terminal">Terminal</option>
                            <option value="Retido">Retido</option>
                            <option value="Finalizado">Finalizado</option>
                            <!-- Adicione outras opções conforme necessário -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="sort_by" class="form-control">
                            <option value="">Ordenar por...</option>
                            <option value="data_asc">Data Ascendente</option>
                            <option value="data_desc">Data Descendente</option>
                            <option value="nome_asc">Nome Ascendente</option>
                            <option value="nome_desc">Nome Descendente</option>
                            <option value="nif">NIF</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex row border border-red-500">
            <!-- Conteúdo principal -->
            <div class="col-md-10 bg-gray-100 p-4 border border-blue-500">
                <!-- Conteúdo da página -->
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
                            <span>Nº de Processos: {{count($processos)}}</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Tabela de Processos -->
                        <table class="table table-hover" id="ProcessoTable">
                            <thead>
                                <tr>
                                    
                                    <th><a href="?{{ http_build_query(array_merge(request()->all(), ['sort_by' => 'NrProcesso', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])) }}">Número do Processo</a></th>
                                    <th><a href="?{{ http_build_query(array_merge(request()->all(), ['sort_by' => 'TipoProcesso', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])) }}">Tipo de Processo</a></th>
                                    <th><a href="?{{ http_build_query(array_merge(request()->all(), ['sort_by' => 'Situacao', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])) }}">Estado</a></th>
                                    <th>Origem</th>
                                    <th>Valor Aduaneiro</th>
                                    <th><a href="?{{ http_build_query(array_merge(request()->all(), ['sort_by' => 'DataAbertura', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])) }}">Data de Abertura</a></th>
                                    <th>Factura</th>
                                    <th>Acções</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($processos as $processo)
                                    <tr>
                                        <td>
                                            <div class="text-wrap">
                                                <a href="{{ route('customers.show', $processo->cliente->id) }}">
                                                    {{ $processo->cliente->CompanyName }}
                                                </a>
                                                @if($processo->RefCliente)
                                                    <small>({{ $processo->RefCliente }})</small>
                                                @endif
                                                <br>
                                                <span class="badge bg-success mt-1">
                                                    <a href="{{ route('processos.show', $processo->id) }}">
                                                        {{ $processo->NrProcesso }}
                                                    </a>
                                                </span>
                                            </div>
                                        </td>
                                        <td>{{ $processo->tipoProcesso->descricao }}</td>
                                        <td>
                                            <span class="badge {{ $processo->Situacao == 'Aberto' ? 'badge-success' : ($processo->Situacao == 'Desembaraçado' ? 'badge-warning' : 'badge-danger') }}">
                                                {{ $processo->Estado }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="porto-origem">
                                                <span class="pais-codigo" data-toggle="tooltip" title="{{ $processo->PortoOrigem }}">
                                                    <span class="flag-icon flag-icon-{{ strtolower($processo->paisOrigem->codigo ?? '') }}"></span>
                                                    {{ $processo->PortoOrigem }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            {{ number_format($processo->ValorAduaneiro, 2, ',', '.') }} kz <br>
                                            <small>({{ $processo->cif }} {{ $processo->Moeda }})</small>
                                        </td>
                                        <td>
                                            {{ $processo->DataAbertura }} <br>
                                            <small>
                                                <span style="color: darkorange;">0</span> Semanas <span style="color: darkorange;">0</span> dias atrás
                                            </small>
                                        </td>
                                        <!-- Exibir status da fatura -->
                                        @if($processo->procLicenFaturas->isNotEmpty())
                                            @php
                                                $statusFatura = $processo->procLicenFaturas->last()->status_fatura;
                                            @endphp
                                            <td>{{ ucfirst($statusFatura) }} <br>
                                                <small>
                                                    <a href="{{ route('documentos.show', $processo->procLicenFaturas->last()->id) }}">
                                                        Ver Factura
                                                    </a>
                                                </small>
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
                                                        <li><a href="{{ route('processos.show', $processo->id) }}" class="dropdown-item btn btn-sm btn-primary"> <i class="fas fa-eye"></i> Visualizar</a></li>
                                                        <li><a href="{{ route('processos.edit', $processo->id) }}" class="dropdown-item btn btn-sm btn-warning"> <i class="fas fa-edit"></i> Editar</a></li>
                                                        <li><a href="" class="dropdown-item">  <i class="fas fa-file-xml"></i> DU Electronico</a></li>
                                                        <li><a href="{{ route('documentos.create', ['processo_id' => $processo->id])}}" class="dropdown-item"> <i class="fas fa-file-pdf"></i> Factura</a></li>
                                                        <li>
                                                            <form action="{{ route('processos.destroy', $processo->id) }}" method="POST" style="display: inline-block;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-action="{{ route('processos.destroy', $processo->id) }}"> <i class="fas fa-trash"></i> Finalizar </button>
                                                            </form>
                                                        </li>

                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nenhum processo encontrado</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quadro de notificações (O Quadro só aparece quando existir notificações de processos) -->
            <div class="card col-md-2 direct-chat direct-chat-primary" id="quadro-notificacoes-container" style="overflow-y: auto; max-height: 100%;">
                <div class="card-header">
                    <span class="card-title"> <i class="fas fa-info-circle"></i> Notificações</span>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget = "collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" title="Contactos" data-widget = "chat-pane-toggle">
                            <i class="fas fa-comments"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget = "remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="quadro-notificacoes">
                        <!-- Notificações carregadas via AJAX -->
                        <p class="text-gray-500">Carregando notificações...</p>
                    </div>
                </div>
            </div>
        </div>

        
    </div>

    <!-- Inclua o JavaScript do Bootstrap Tooltip -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipElements = document.querySelectorAll('[data-toggle="tooltip"]');
            tooltipElements.forEach(function(element) {
                new bootstrap.Tooltip(element);
            });
        });
    </script>

    <style>
        .porto-origem {
            display: flex;
            align-items: center;
        }

        .pais-codigo {
            margin-left: 5px;
        }

        .pais-codigo .flag-icon {
            width: 20px;
            height: 20px;
        }
    </style>

    <!-- Ensure you have included jQuery and Bootstrap JS at the end of the body tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este processo?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            // Filtro de Pesquisa
            $('#search').on('input', function() {
                var searchTerm = $(this).val().toLowerCase();

                // Filtrar as linhas da tabela
                $('#ProcessoTable tbody tr').each(function() {
                    var row = $(this);
                    var cliente = row.find('td').eq(0).text().toLowerCase(); // Nº Processo
                    var descricao = row.find('td').eq(1).text().toLowerCase(); // Tipo de Processo
                    var referencia = row.find('td').eq(2).text().toLowerCase(); // Estado
                    var origem = row.find('td').eq(3).text().toLowerCase(); // Origem Mercadoria
                    var valor = row.find('td').eq(4).text().toLowerCase(); // Valor Aduaneiro
                    var data_abertura = row.find('td').eq(5).text().toLowerCase(); // Data de Abertura

                    // Verificar se algum dos campos corresponde ao termo de pesquisa
                    if (cliente.includes(searchTerm) || descricao.includes(searchTerm) || referencia.includes(searchTerm) || origem.includes(searchTerm) || valor.includes(searchTerm) || data_abertura.includes(searchTerm)) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
            });
        });
    </script>

    <script>
        $(function () {
            $("#ProcessoTable").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "paging": true,
                "info": true,
                "searching": false, // Desativa a pesquisa
                "ordering": false // Desativa a ordenação
            }).container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>

    <script>
        function deleteProcesso(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Você não poderá reverter isso!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Fazer a requisição para deletar o processo
                    axios.delete('/processos/' + id)
                        .then(response => {
                            Swal.fire(
                                'Deletado!',
                                'O processo foi deletado.',
                                'success'
                            );
                            location.reload(); // Recarregar a página
                        })
                        .catch(error => {
                            Swal.fire(
                                'Erro!',
                                'Ocorreu um erro ao deletar o processo.',
                                'error'
                            );
                        });
                }
            });
        }
    </script>
    
    <script>
        $(document).ready(function () {
            function carregarNotificacoesPopup() {
                $.ajax({
                    url: 'processo/nao-finalizados', // Endpoint para buscar notificações
                    method: 'GET',
                    success: function (data) {
                        if (data.length > 0) {
                            let notificacoesHtml = '';

                            data.forEach(function (processo) {
                                notificacoesHtml += `
                                    <a href="processos/${processo.id}">
                                        <div class="p-2 bg-gradient-to-r from-red-200 via-red-300 to-red-500 rounded-lg mb-4 shadow-lg hover:shadow-2xl transition-all duration-300">
                                            <p class="text-md font-semibold text-green"><strong>Processo:</strong> ${processo.NrProcesso}</p>
                                            <p class="text-sm text-green"><strong>DU:</strong> ${processo.NrDU}</p>
                                            <p class="text-sm text-black mb-2"><strong>Descrição:</strong> ${processo.Descricao || 'Sem descrição'}</p>
                                            <div class="flex items-center space-x-2">
                                                <p class="text-sm text-yellow-300"><strong>Valor Aduaneiro:</strong> ${processo.ValorAduaneiro} Kz</p>
                                                <div class="flex-grow"></div>
                                                <span class="text-xs text-gray-200 italic">Atualizado há x minutos</span>
                                            </div>
                                        </div>
                                    </a>
                                `;
                            });

                            // Atualizar o conteúdo do quadro de notificações
                            $('#quadro-notificacoes').html(notificacoesHtml);

                            // Exibir o quadro de notificações
                            $('#quadro-notificacoes-container').removeClass('hidden');
                        } else {
                            // Esconder o quadro se não houver notificações
                            $('#quadro-notificacoes-container').addClass('hidden');
                        }
                    },
                    error: function (err) {
                        console.error('Erro ao carregar notificações:', err);
                    }
                });
            }

            // Chamar a função ao carregar a página
            carregarNotificacoesPopup();
        });
    </script>

</x-app-layout>
