<x-app-layout>

    <!-- Outras tags -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <div class="" style="padding: 10px;">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 row" style="padding: 10px;">
            <!-- Card de Total de Saldo -->
            <div class="card bg-white shadow-lg rounded-lg p-4 col-md-4 flex items-center">
                <i class="fas fa-wallet fa-3x text-blue-600 mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold">Total de Saldo</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($totalSaldo, 2, ',', '.') }} AOA</p>
                </div>
            </div>

            <!-- Card de Total de Dívida Corrente -->
            <div class="card bg-white shadow-lg rounded-lg p-4 col-md-4 flex items-center">
                <i class="fas fa-credit-card fa-3x text-green-600 mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold">Total de Dívida Corrente</h3>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($totalDividaCorrente, 2, ',', '.') }} AOA</p>
                </div>
            </div>

            <!-- Card de Total de Dívida Vencida -->
            <div class="card bg-white shadow-lg rounded-lg p-4 col-md-4 flex items-center">
                <i class="fas fa-exclamation-triangle fa-3x text-red-600 mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold">Total de Dívida Vencida</h3>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($totalDividaVencida, 2, ',', '.') }} AOA</p>
                </div>
            </div>
        </div>

        

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <a href="" class="btn btn-success btn-sm mb-3">Exportar Dados</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <x-input type="text" id="search" placeholder="Pesquisar por Nome, NIF" class="form-control" />
                    </div>
                    <div class="col-md-2">
                        <x-input type="date" id="search_date" class="form-control" />
                    </div>
                    <div class="col-md-3">
                        <select name="" id="" class="form-control">
                            <option value="">Todos</option>
                            <option value="">Dívida Corrente</option>
                            <option value="">Dívida Vencida</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="sort_by" class="form-control">
                            <option value="">Ordenar por...</option>
                            <option value="saldo_asc">Saldo Ascendente</option>
                            <option value="saldo_desc">Saldo Descendente</option>
                            <option value="divida_corrente_asc">Dívida Corrente Ascendente</option>
                            <option value="divida_corrente_desc">Dívida Corrente Descendente</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th width="45%">Cliente</th>
                            <th>Saldo Atual (AOA)</th>
                            <th>Dívida Corrente (AOA)</th>
                            <th>Dívida Vencida (AOA)</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resultados as $resultado)
                            <tr>
                                <td>{{ $resultado['cliente']->CompanyName }}</td>
                                <td style="color: {{ $resultado['saldo'] >= 0 ? 'blue' : 'red' }}">
                                    {{ number_format(abs($resultado['saldo']), 2, ',', '.') }}
                                    @if($resultado['saldo'] < 0)
                                        <span>(Dívida Atual)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($resultado['dividaCorrente'] > 100000)
                                        <i class="fa fa-exclamation-triangle text-red-600" title="Dívida Corrente Alta"></i>
                                    @endif
                                    {{ number_format($resultado['dividaCorrente'], 2, ',', '.') }}
                                </td>
                                <td>{{ number_format($resultado['dividaVencida'], 2, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('customers.show', $resultado['cliente']->id) }}" class="btn btn-info btn-sm">Detalhes</a>
                                    <a href="{{ route('customers.edit', $resultado['cliente']->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>