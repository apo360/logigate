<x-app-layout>

<x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => 'Pesquisar Clientes' , 'url' => '']
    ]" separator="/" />

<div class="container-fluid" style="margin-top: 20px;">
    
    <div class="card shadow-lg">
        <div class="card-header">
            <div class="float-left">
                <input type="text" id="search" placeholder="Pesquisar por Nome, NIF, Telefone ou Cidade" class="form-control">
            </div>
            <div class="float-right">
                <div class="btn-group">
                    <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary" style="color: black;">
                        <i class="fas fa-user-plus" style="color: black;"></i> Novo
                    </a>
                    <a href="#" id="importacaoBtn" class="btn btn-sm btn-default"> 
                        <i class="fas fa-download" style="color:rgb(130, 23, 69);"></i> Importação
                    </a>
                    <a type="button" href="#" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal-lg" style="color: black;">
                        <i class="fas fa-upload" style="color: rgba(195, 69, 124, 1);"></i> Exportar
                    </a>
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
            <table class="table table-hover align-middle" id="customerTable">
                <thead class="table-dark">
                    <tr>
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>NIF</th>
                        <th>Endereço</th>
                        <th>Telemóvel</th>
                        <th>Status</th>
                        <th class="text-end">Acções</th>
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
                                    title="Alterar Status do Cliente"
                                    {{ $customer->is_active ? 'checked' : '' }}>
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
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        @can('update', $customer)
                                            <a href="{{ route('customers.edit', $customer->id) }}" class="dropdown-item"><i class="fas fa-edit"></i> Editar</a>
                                        @endcan
                                    </li>
                                    <li><a href="{{ route('cliente.cc', ['id' => $customer->id]) }}" class="dropdown-item"><i class="fas fa-wallet"></i> Conta Corrente</a></li>
                                    <li>
                                        <a href="{{ route('customers.create') }}" class="block px-4 py-2 hover:bg-gray-100">
                                            <i class="fas fa-folder-open mr-2 text-gray-500"></i> Abrir Processo
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('customers.create') }}" class="block px-4 py-2 hover:bg-gray-100">
                                            <i class="fas fa-cogs mr-2 text-green-500"></i> Iniciar Licenciamento
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <!-- Verificar se o cliente tem factura associada -->
                                        @php
                                            $hasInvoices = $customer->invoices()->exists();
                                        @endphp
                                        @if($hasInvoices)
                                            <a href="#" class="dropdown-item text-muted disabled" title="Não é possível apagar cliente com facturas associadas.">
                                                <i class="fas fa-trash"></i> Apagar
                                            </a>
                                        @else
                                            <!-- Formulário de exclusão -->
                                                @can('delete', $customer)
                                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Deseja realmente excluir este cliente?');">
                                                        <i class="fas fa-trash"></i> Apagar
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                        
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
                            <li> NIF <span class="text-sm">*</span></li>
                            <li> Nome do Cliente <span class="text-sm">*</span></li>
                            <li> Endereço</li>
                            <li> Telefone</li>
                            <li> Email</li>
                            <li> Cidade</li>
                            <li> País</li>
                        </ul>
                        <p><span class="text-sm ">*</span> Campos obrigatórios</p>
                    </div>

                    <!-- Formulário de Upload -->
                    <form id="uploadForm" action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Importar Clientes</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="importErrors" class="alert alert-danger d-none"></div>
                            <input type="file" name="file" class="form-control" required>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" id="importBtn" class="btn btn-primary">
                                <span id="importBtnSpinner" class="spinner-border spinner-border-sm d-none"></span>
                                Importar
                            </button>
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
        $(function () {
            $("#customerTable").DataTable({
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

                    fetch(`customers/toggle-status/${customerId}`, {
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

    <script>
        // ...existing code...
        // Script do modal de importação
        $(document).ready(function() {
            // Abrir o modal ao clicar no botão de Importação
            $('#importacaoBtn').on('click', function(e) {
                e.preventDefault();
                $('#importacaoModal').modal('show');
            });

            // Validação e envio do formulário via AJAX (opcional, se não for necessário o envio tradicional)
            // Submeter form via AJAX
            $('#uploadForm').on('submit', function (e) {
                e.preventDefault();

                var formData = new FormData(this);
                $('#importBtn').prop('disabled', true);
                $('#importBtnSpinner').removeClass('d-none');
                $('#importErrors').addClass('d-none').empty();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        alert(response.message || 'Ficheiro importado com sucesso!');
                        $('#importacaoModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        let message = 'Erro ao importar o ficheiro.';

                        if (xhr.status === 422) {
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                message = '';
                                $.each(xhr.responseJSON.errors, function(key, value) {
                                    if (Array.isArray(value)) {
                                        message += value.join('<br>') + '<br>';
                                    } else {
                                        message += value + '<br>';
                                    }
                                });
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                        } else {
                            message = xhr.responseText; // fallback
                        }

                        $('#importErrors').html('<div class="alert alert-danger">' + message + '</div>');
                    },
                    complete: function () {
                        $('#importBtn').prop('disabled', false);
                        $('#importBtnSpinner').addClass('d-none');
                    }
                });
            });
        });
    </script>
</x-app-layout>
