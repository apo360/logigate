<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => $customer->CompanyName, 'url' => route('customers.show', $customer->id)],
        ['name' => 'Editar Cliente', 'url' => route('customers.edit', $customer->id)]
    ]" separator="/" />
    <div class="py-4">
        <!-- Cabeçalho -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>👤 {{ $customer->CompanyName }}</h4>
                <div>
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i> Editar Cliente
                    </a>
                    <button class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs"></i> Opções
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('customers.show', $customer->id) }}" class="dropdown-item"><i class="fas fa-eye"></i> Liquidar Facturas</a></li>
                        <li><a href="{{ route('cliente.cc', ['id' => $customer->id]) }}" class="dropdown-item"><i class="fas fa-wallet"></i> Conta Corrente</a></li>
                        <li><a href="{{ route('customers.edit', $customer->id) }}" class="dropdown-item"><i class="fas fa-briefcase"></i> Avença</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                    <i class="fas fa-trash"></i> Apagar Cliente
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Dados do Cliente -->
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h5 class="mb-3">📌 Informações do Cliente</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="text-muted">Nome:</span> <b>{{ $customer->CompanyName }}</b></p>
                        <p><span class="text-muted">Gestor de Conta:</span> <b>{{ auth()->user()->name }}</b></p>
                        <p><span class="text-muted">Referência Externa:</span> <b>---</b></p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="text-muted">NIF:</span> <b>{{ $customer->CustomerTaxID }}</b></p>
                        <p><span class="text-muted">País:</span> <b>Angola [AO]</b></p>
                        <p><span class="text-muted">Modo Pagamento:</span> <b>---</b></p>
                    </div>
                </div>
                <p class="mt-3"><span class="text-muted">Morada Completa:</span> <b>Rua Exemplo, 123, Bairro Exemplo</b></p>
                <p><span class="text-muted">Telefone:</span> <b>{{ $customer->Telephone }}</b></p>
                <p><span class="text-muted">Email:</span> <b>{{ $customer->Email }}</b></p>
            </div>
        </div>

        <!-- Processos -->
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h5 class="mb-3">📂 Processos Relacionados</h5>
                <p>Nº de Processos: <b>{{ count($customer->processos) }}</b></p>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Descrição</th>
                                <th>Status</th>
                                <th>Data de Criação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->processos as $processo)
                            <tr>
                                <td>
                                    <span class="badge bg-success mt-1">
                                        <a href="{{ route('processos.show', $processo->id) }}">
                                            {{ $processo->NrProcesso }}
                                        </a>
                                    </span> 
                                    {{ $processo->Descricao }}
                                </td>
                                <td><span class="badge bg-primary">{{ $processo->Estado }}</span></td>
                                <td>{{ $processo->created_at }}</td>
                                <td><a href="{{ route('processos.show', $processo->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Ver</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">Nenhum processo encontrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h5 class="mb-3">📊 Estatísticas</h5>
                <div class="row">
                    <div class="col-md-9">
                        <canvas id="statsChart" width="400" height="200"></canvas>
                    </div>
                    <div class="col-md-3">
                        <h5 class="mb-3">💰 Conta Corrente</h5>
                        <p><b>Saldo Total:</b> <span id="saldoTotal" class="text-success">---</span></p>
                        <p><b>Dívida Corrente:</b> <span class="text-warning">---</span></p>
                        <p><b>Dívida Vencida:</b> <span class="text-danger">---</span></p>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <span class="card-title">📄 Facturas do Cliente</span>
                <div class="table-responsive">
                <table class="table table-hover table-sm">
    <thead class="table-dark">
        <tr>
            <th>Nº Factura</th>
            <th>Valor</th>
            <th>Status</th>
            <th>Data</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @forelse($customer->invoices as $invoice)
            <tr>
                <td>{{ $invoice->invoice_no }}</td>
                <td>{{ number_format($invoice->salesdoctotal->gross_total ?? '0.00', 2, ',', '.') }} Kz</td>
                <td>
                    @php
                        // Verifica se salesstatus é uma coleção e obtém o primeiro item
                        $salesStatus = is_iterable($invoice->salesstatus) ? $invoice->salesstatus->first() : null;
                        // Verifica se invoice_status é uma coleção e obtém o primeiro item
                        $status = optional(is_iterable($salesStatus->invoice_status ?? null) ? $salesStatus->invoice_status->first() : null)->status;
                    @endphp

                    @if($status === 'A')
                        <span class="text-danger">Factura Anulada</span>
                    @else
                        @if ($invoice->invoice_date_end >= now())
                            <span class="badge bg-warning text-dark">Por Pagar</span>
                        @else
                            <span class="badge bg-danger">Factura Vencida</span>
                        @endif
                    @endif
                </td>
                <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                <td>
                    <a href="{{ route('documentos.show', $invoice->id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <a href="{{ route('documento.download', $invoice->id) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-download"></i> Baixar
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted">Nenhuma Factura encontrada.</td>
            </tr>
        @endforelse
    </tbody>
</table>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // Modal de exclusão de cliente
            $('#confirmDeleteModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var action = button.data('action');
                $(this).find('form').attr('action', action);
            });

            // Estatísticas com Chart.js
            var ctx = document.getElementById('statsChart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['2023', '2024'],
                    datasets: [{
                        label: 'Importações',
                        data: [10, 15],
                        backgroundColor: 'blue'
                    }, {
                        label: 'Exportações',
                        data: [5, 8],
                        backgroundColor: 'red'
                    }]
                }
            });
        });
    </script>
</x-app-layout>

