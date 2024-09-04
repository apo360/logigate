

<x-app-layout>
    <style>
        .text-muted {
            font-weight: bold;
            color: #6c757d;
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .card-body {
            background-color: #ffffff;
        }
        .btn {
            margin-right: 10px;
        }
    </style>

    <div class="" style="padding: 10px;">
        <div class="card">
            <div class="card-header">
                <div class="float-right">
                    <a href="{{ route('customers.edit', $customer->id) }}" type="button" class="btn btn-default" style="color: black;">
                        <i class="fas fa-edit" style="color: black;"></i> Editar Cliente
                    </a>
                    <div class="btn-group" role="group">
                        <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-default dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter"></i>Opções
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                <li><a href="{{ route('customers.show', $customer->id) }}" class="dropdown-item btn btn-sm btn-primary"> <i class="fas fa-eye"></i> Liquidar Facturas</a></li>
                                <li><a href="{{ route('cliente.cc', ['id' => $customer->id]) }}" class="dropdown-item btn btn-sm btn-warning"> <i class="fas fa-edit"></i> Conta Corrente</a></li>
                                <li><a href="{{ route('customers.edit', $customer->id) }}" class="dropdown-item btn btn-sm btn-warning"> <i class="fas fa-edit"></i> Avença</a></li>
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
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="bg-white overflow-hidden p-4 rounded shadow-sm">
                <h2>Dados do Cliente</h2>
                <hr>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <span class="text-muted">Nome</span>
                            <hr>
                            <b>{{ $customer->CompanyName }}</b>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted">Gestor de Conta</span>
                            <hr>
                            <b>{{ auth()->user()->name }}</b>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted">Referência Externa</span>
                            <hr>
                            <b>---</b>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <span class="text-muted">NIF</span>
                            <hr>
                            <b>{{ $customer->CustomerTaxID }}</b>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted">País</span>
                            <hr>
                            <b>Angola [AO]</b>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted">Modo Pagamento</span>
                            <hr>
                            <b>---</b>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="text-muted">Morada Completa</span>
                    <hr>
                    <p class="mb-0">Rua Exemplo, 123, Bairro Exemplo</p>
                </div>

                <div class="mb-4">
                    <span class="text-muted">Contactos</span>
                    <hr>
                    <p class="mb-0">Telefone: {{ $customer->Telephone }}</p>
                    <p class="mb-0">Email: {{ $customer->Email }}</p>
                </div>
            </div>
        </div>

        <div class="container mt-5">
            <div class="bg-white overflow-hidden p-4 rounded shadow-sm">
                <h3>Processos</h3>
                <hr>
                <x-button class="btn btn-primary mr-2">Fechar Processos</x-button>
                <x-button class="btn btn-secondary mr-2">Uploads</x-button>
                <br><br>
                <p>Nº de Processos: {{ count($customer->processos) }}</p>
                <div class="card mb-4">
                    <div class="card-header">
                        Processos Relacionados
                    </div>
                    <div class="card-body">
                        @if($customer->processos->count() > 0)
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Descrição</th>
                                        <th>Status</th>
                                        <th>Data de Criação</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->processos as $processo)
                                        <tr>
                                            <td>{{ $processo->id }}</td>
                                            <td>{{ $processo->Descricao }}</td>
                                            <td>{{ $processo->Situacao }}</td>
                                            <td>{{ $processo->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('processos.show', $processo->id) }}" class="btn btn-info btn-sm">Ver</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>Nenhum processo encontrado.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-5">
            <div class="bg-white overflow-hidden p-4 rounded shadow-sm">
                <h3>Conta</h3>
                <hr>
                <x-button class="btn btn-primary mr-2">Movimentos Conta Corrente</x-button>
                <x-button class="btn btn-secondary mr-2">Liquidar Facturas</x-button>
                <x-button class="btn btn-danger mr-2">Regularizar</x-button>
                <br><br>
                <p>Conta Corrente: ---</p>
                <p>Saldo Total: ---</p>
                <p>Dívida Corrente: ---</p>
                <p>Dívida Vencida: ---</p>
            </div>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        // Example: Toggle visibility of a section
        $('#toggleSectionButton').click(function() {
            $('#sectionToToggle').toggle();
        });
    });
</script>

</x-app-layout>
