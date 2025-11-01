<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => $customer->CompanyName, 'url' => route('customers.show', $customer->id)],
        ['name' => 'Editar Cliente', 'url' => route('customers.edit', $customer->id)]
    ]" separator="/" />
    <div class="py-4">
        <!-- Card do Cliente -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">👤 {{ $customer->CompanyName }}</h4>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        {{ $customer->CompanyName }}
                        @cannot('update', $customer)
                            <span class="badge bg-secondary">
                                <i class="fas fa-lock"></i> Somente Leitura
                            </span>
                        @else
                            <span class="badge bg-success">
                                <i class="fas fa-unlock"></i> Edição Ativa
                            </span>
                        @endcannot
                    </h4>

                    <a href="{{ route('customers.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="btn-group">
                    @can('update', $customer)
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    @endcan
                    <button class="btn btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs"></i> Opções
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a href="{{ route('customers.show', $customer->id) }}" class="dropdown-item">
                                <i class="fas fa-eye"></i> Liquidar Facturas
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('cliente.cc', ['id' => $customer->id]) }}" class="dropdown-item">
                                <i class="fas fa-wallet"></i> Conta Corrente
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customers.edit', $customer->id) }}" class="dropdown-item">
                                <i class="fas fa-briefcase"></i> Avença
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            @can('delete', $customer)
                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                        <i class="fas fa-trash"></i> Apagar Cliente
                                    </button>
                                </form>
                            @endcan
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Corpo com dados -->
            <div class="card-body">
                <h5 class="mb-3">📌 Informações do Cliente</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="fw-bold text-muted">Nome:</span> {{ $customer->CompanyName }}</p>
                        <p><span class="fw-bold text-muted">Gestor de Conta:</span> {{ auth()->user()->name }}</p>
                        <p><span class="fw-bold text-muted">Referência Externa:</span> ---</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="fw-bold text-muted">NIF:</span> {{ $customer->CustomerTaxID }}</p>
                        <p><span class="fw-bold text-muted">País:</span> Angola [AO]</p>
                        <p><span class="fw-bold text-muted">Modo Pagamento:</span> ---</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="fw-bold text-muted">Morada:</span> Rua Exemplo, 123, Bairro Exemplo</p>
                        <p><span class="fw-bold text-muted">Telefone:</span> {{ $customer->Telephone }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="fw-bold text-muted">Email:</span> {{ $customer->Email }}</p>
                    </div>
                </div>
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

        <!-- Documentos -->
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h5 class="mb-3">🗂️ Documentos Relacionados</h5>
                <p>Nº de Documentos: <b>{{ count($customer->invoices) }}</b></p>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Nº Documento</th>
                                <th>Estado</th>
                                <th>Data de Emissão</th>
                                <th>Valor Total</th>
                                <th>Valor Pago</th>
                                <th>Valor em Dívida</th>
                                <th>Acções</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->invoices as $invoice)
                                <tr>
                                    <td>
                                        <span class="badge bg-success mt-1">
                                            <a href="{{ route('documentos.show', $invoice->id) }}">
                                                {{ $invoice->invoice_no }}
                                            </a>
                                        </span>
                                        @if($invoice->is_cancelled)
                                            <span class="badge text-danger mt-1">(Anulada)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $invoice->payment_status['class'] }}">
                                            <i class="fas {{ $invoice->payment_status['icon'] }}"></i>
                                            {{ $invoice->payment_status['label'] }}
                                        </span>
                                    </td>
                                    <td>{{ $invoice->invoice_date }}</td>
                                    <td>{{ number_format($invoice->gross_total, 2, ',', '.') }} AKZ</td>
                                    <td class="text-success">{{ number_format($invoice->paid_amount, 2, ',', '.') }} AKZ</td>
                                    <td class="text-danger">{{ number_format($invoice->due_amount, 2, ',', '.') }} AKZ</td>
                                    <td><a href="{{ route('documentos.show', $invoice->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Ver</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">Nenhum documento encontrado.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Totais:</th>
                                <th>{{ number_format($customer->invoices->sum('gross_total'), 2, ',', '.') }} AKZ</th>
                                <th>{{ number_format($customer->invoices->sum('paid_amount'), 2, ',', '.') }} AKZ</th>
                                <th>{{ number_format($customer->invoices->sum('due_amount'), 2, ',', '.') }} AKZ</th>
                                <th></th>
                            </tr>
                        </tfoot>
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
                
                <small class="text-muted">Última Actualização: ---</small>
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

