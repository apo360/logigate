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
    <div class="" style="padding: 10px;"> 

        <div class="card">
            <div class="card-header">
                <div class="float-left"></div>
                <div class="float-right">
                    <!-- Botão Cadastro de Processos -->
                    <div class="btn-group">
                        <a href="{{ route('processos.create') }}" class="btn btn-primary btn-sm mb-3">Novo Processo</a>
                        <a href="" class="btn btn-sm mb-3">Licenciamento</a>
                        <a href="" class="btn btn-success btn-sm mb-3">Exportar Dados</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
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
        
        <div class="card">

            <div class="card-body">
                <!-- Tabela de Processos -->
                <table id="example1" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Acções</th>
                            <th><a href="?{{ http_build_query(array_merge(request()->all(), ['sort_by' => 'NrProcesso', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])) }}">Número do Processo</a></th>
                            <th><a href="?{{ http_build_query(array_merge(request()->all(), ['sort_by' => 'CompanyName', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])) }}">Cliente</a></th>
                            <th><a href="?{{ http_build_query(array_merge(request()->all(), ['sort_by' => 'TipoProcesso', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])) }}">Tipo de Processo</a></th>
                            <th><a href="?{{ http_build_query(array_merge(request()->all(), ['sort_by' => 'Situacao', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])) }}">Situação</a></th>
                            <th>Porto de Origem</th>
                            <th><a href="?{{ http_build_query(array_merge(request()->all(), ['sort_by' => 'DataAbertura', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc'])) }}">Data de Abertura</a></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($processos as $processo)
                            <tr>
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
                                                <li><a href="{{ route('documentos.create', ['id' => $processo->id] )}}" class="dropdown-item"> <i class="fas fa-file-pdf"></i> Factura</a></li>
                                                <li>
                                                    <form action="{{ route('processos.destroy', $processo->id) }}" method="POST" style="display: inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-action="{{ route('processos.destroy', $processo->id) }}"> <i class="fas fa-trash"></i> Eliminar </button>
                                                    </form>
                                                </li>

                                            </ul>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $processo->NrProcesso }}</td>
                                <td>{{ $processo->CompanyName }}</td>
                                <td>{{ $processo->TipoProcesso }}</td>
                                <td>
                                    <span class="badge {{ $processo->Situacao == 'Em processamento' ? 'badge-warning' : ($processo->Situacao == 'Desembaraçado' ? 'badge-success' : 'badge-danger') }}">
                                        {{ $processo->Situacao }}
                                    </span>
                                </td>
                                <td>
                                    <div class="porto-origem">
                                        <span class="pais-codigo" data-toggle="tooltip" title="{{ $processo->origem }}">
                                            <span class="flag-icon flag-icon-{{ strtolower($processo->codigo) }}"></span>
                                            {{ $processo->PortoOrigem }}
                                        </span>
                                    </div>
                                </td>
                                <td>{{ $processo->DataAbertura }}</td>
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
            $('#processosTable').DataTable({
                "paging": false,
                "lengthChange": false,
                "searching": true,
                "ordering": false,
                "info": false,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>

    <script>
        $(function () {
            $("#example1").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false, "paging": true, "info": true,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
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

    <!-- <script>
        $(document).ready(function() {
            $('.form-control').select2();
        });
    </script> -->
</x-app-layout>
