<x-app-layout>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => 'Pesquisar Clientes' , 'url' => '']
    ]" separator="/" />
    
    <div class="card">
        <div class="card-header">
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
                <div class="col-md-4 text-end">
                    <select id="filterStatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inativos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="float-left">
                <div class="btn-group">
                    <a href="{{ route('customers.exportCsv') }}" class="btn btn-sm btn-default"> <i class="fas fa-file-csv"></i> CSV</a>
                    <a href="{{ route('customers.exportExcel') }}" class="btn btn-sm btn-default"> <i class="fas fa-file-excel"></i> Excel</a>
                    <a href="#" class="btn btn-sm btn-default"><i class="fas fa-file-pdf"></i> PDF</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Tabela de Clientes -->
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="customerTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Foto</th>
                            <th>Nome</th>
                            <th>NIF</th>
                            <th>Endereço</th>
                            <th>Telemóvel</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        @forelse($customers as $customer)
                        <tr>
                            <!-- Foto ou Iniciais -->
                            <td>
                                @if($customer->foto)
                                    <img src="{{ asset($customer->foto) }}" alt="{{ $customer->CompanyName }}" class="rounded-circle" width="50" height="50">
                                @else
                                    <div class="rounded-circle bg-secondary text-white text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                        {{ strtoupper(substr($customer->CompanyName, 0, 2)) }}
                                    </div>
                                @endif
                            </td>
                            
                            <!-- Nome -->
                            <td>
                            @php
                                $overdueInvoices = $customer->invoices->filter(function ($invoice) {
                                    return Carbon\Carbon::parse($invoice->invoice_date_end)->lt(Carbon\Carbon::now());
                                });
                            @endphp
                                @if ($overdueInvoices->count() > 0)
                                    <i class = "fas fa-exclamation-triangle" style="color: red;"></i>
                                @endif
                                <a href="{{ route('customers.show', $customer->id) }}" class="text-decoration-none">
                                    {{ $customer->CompanyName }}
                                </a>
                            </td>
                            
                            <!-- NIF -->
                            <td>{{ $customer->CustomerTaxID }}</td>
                            
                            <!-- Endereço -->
                            <td>{{ $customer->AddressDetail ?? 'N/A' }}</td>
                            
                            <!-- Telemóvel -->
                            <td>{{ $customer->Telephone ?? 'N/A' }}</td>
                            
                            <!-- Status -->
                            <td>
                                <div class="form-check form-switch">
                                    <input 
                                        class="form-check-input toggle-status" 
                                        type="checkbox" 
                                        id="statusSwitch{{ $customer->id }}" 
                                        data-id="{{ $customer->id }}" 
                                        data-status="{{ $customer->is_active ? 0 : 1 }}"
                                        {{ $customer->is_active ? 'checked' : '' }}
                                    >
                                    <span class="spinner-border spinner-border-sm text-primary d-none" id="spinner{{ $customer->id }}"></span>
                                </div>
                            </td>

                            
                            <!-- Ações -->
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="{{ route('customers.show', $customer->id) }}" class="dropdown-item"><i class="fas fa-eye"></i> Detalhes</a></li>
                                        <li><a href="{{ route('customers.edit', $customer->id) }}" class="dropdown-item"><i class="fas fa-edit"></i> Editar</a></li>
                                        <li><a href="{{ route('cliente.cc', ['id' => $customer->id]) }}" class="dropdown-item"><i class="fas fa-wallet"></i> Conta Corrente</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Deseja realmente excluir este cliente?');">
                                                    <i class="fas fa-trash"></i> Apagar
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Nenhum cliente encontrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

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
                            <li>...</li>
                        </ul>
                    </div>

                    <!-- Formulário de Upload -->
                    <form id="uploadForm" action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
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

    <!-- Modal de Confirmação -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir este cliente? Esta ação não poderá ser desfeita.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteCustomerForm" action="#" method="POST">
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

 <!-- Ensure you have included jQuery and Bootstrap JS at the end of the body tag -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

    <!-- Toggle para activar clientes -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusSwitches = document.querySelectorAll('.toggle-status');

            statusSwitches.forEach(switchEl => {
                switchEl.addEventListener('change', function () {
                    const customerId = this.getAttribute('data-id');
                    const newStatus = this.checked ? 1 : 0;
                    const spinner = document.getElementById(`spinner${customerId}`);

                    // Exibir spinner
                    spinner.classList.remove('d-none');

                    fetch(`customer/toggle-status/${customerId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ is_active: newStatus })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Status atualizado com sucesso!');
                        } else {
                            alert('Erro ao atualizar o status.');
                            this.checked = !this.checked; // Reverter toggle
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        this.checked = !this.checked; // Reverter toggle
                    })
                    .finally(() => {
                        // Ocultar spinner
                        spinner.classList.add('d-none');
                    });
                });
            });
        });

    </script>

</x-app-layout>
