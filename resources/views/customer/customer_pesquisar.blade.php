<x-app-layout>

<style>
    .pagination {
        display: flex;
        justify-content: center;
        padding-left: 0;
        list-style: none;
        border-radius: .25rem;
    }

    .pagination li {
        margin: 0 2px;
    }

    .pagination li a {
        color: #007bff;
        text-decoration: none;
        padding: .5rem .75rem;
        border: 1px solid #dee2e6;
        border-radius: .25rem;
        cursor: pointer;
    }

    .pagination li.active a {
        background-color: #007bff;
        color: white;
        border: 1px solid #007bff;
    }
</style>


    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => 'Pesquisar Clientes' , 'url' => '']
    ]" separator="/" />
    <br>
    <div class="container mt-5">
        <div class="card card-navy">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Pesquisar Clientes</h3>
                <div class="float-right">
                    <a href="{{ route('customers.create') }}" type="button" class="btn btn-default" style="color: black;">
                        <i class="fas fa-user-plus" style="color: black;"></i> Novo Cliente
                    </a>
                    <a type="button" href="{{ route('customers.create') }}" class="btn btn-default" style="color: black;">
                        <i class="fas fa-download" style="color: #0170cf;"></i> Upload Clientes CSV
                    </a>
                    <a type="button" href="{{ route('customers.create') }}" class="btn btn-default" data-toggle="modal" data-target="#modal-lg" style="color: black;">
                        <i class="fas fa-upload" style="color: #0170cf;"></i> Exportar Clientes
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                    <input type="text" id="search" placeholder="Pesquisar por Nome, NIF, Telefone ou Cidade" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <select name="" id="" class="form-control">
                            <option value="">Todos</option>
                            <option value="">Activo</option>
                            <option value="">Inativo</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary">Pesquisar</button>
                    </div>
                </div>
                <hr>
                <table class="table table-hover" id="customerTable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>NIF</th>
                            <th>Cidade</th>
                            <th>Telemóvel</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        @foreach($customers as $customer)
                            <tr>
                                <td><a href="{{ route('customers.show', $customer->id) }}">{{$customer->CompanyName}}</a></td>
                                <td>{{$customer->CustomerTaxID}}</td>
                                <td>{{$customer->City}}</td>
                                <td>{{$customer->Telephone}}</td>
                                <td>{{$customer->status}}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button id="btnGroupDrop1" type="button" class="btn btn-default dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            Opções
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                            <li><a href="{{ route('customers.show', $customer->id) }}" class="dropdown-item btn btn-sm btn-primary"> <i class="fas fa-eye"></i> Detalhes</a></li>
                                            <li><a href="{{ route('cliente.cc', ['id' => $customer->id]) }}" class="dropdown-item btn btn-sm btn-warning"> <i class="fas fa-edit"></i> Conta Corrente</a></li>
                                            <li><a href="{{ route('customers.edit', $customer->id) }}" class="dropdown-item btn btn-sm btn-warning"> <i class="fas fa-edit"></i> Editar</a></li>
                                            <hr>
                                            <li><a href="{{ route('customers.create', ['id' => $customer->id] )}}" class="dropdown-item"> <i class="fas fa-file-pdf"></i> Suspender Cliente</a></li>
                                            <li>
                                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-action="{{ route('customers.destroy', $customer->id) }}"> <i class="fas fa-trash"></i> Apagar Cliente </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
               <!-- Elementos de Paginação -->
               <div class="d-flex justify-content-center">
                    <nav>
                        <ul class="pagination" id="pagination">
                            <!-- Paginação gerada por JavaScript -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmação de Exclusão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir este cliente?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="deleteCustomerForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#confirmDeleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var action = button.data('action') // Extract info from data-* attributes
            var modal = $(this)
            modal.find('#deleteCustomerForm').attr('action', action)
        })
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            var rowsPerPage = 10; // Número de linhas por página
            var rows = $('#customerTable tbody tr');
            var rowsCount = rows.length;
            var pageCount = Math.ceil(rowsCount / rowsPerPage); // Número total de páginas
            var pagination = $('#pagination');

            // Adiciona os botões de paginação
            for (var i = 1; i <= pageCount; i++) {
                pagination.append('<li class="page-item"><a class="page-link" href="#">' + i + '</a></li>');
            }

            // Exibe as primeiras linhas
            rows.hide();
            rows.slice(0, rowsPerPage).show();
            pagination.find('li:first-child').addClass('active');

            // Controle de clique dos botões de paginação
            pagination.find('li').on('click', function (e) {
                e.preventDefault();
                pagination.find('li').removeClass('active');
                $(this).addClass('active');
                var page = $(this).text();
                var start = (page - 1) * rowsPerPage;
                var end = start + rowsPerPage;
                rows.hide();
                rows.slice(start, end).show();
            });

            $('#search').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#customerTableBody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>

</x-app-layout>
