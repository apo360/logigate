<x-app-layout>
    <div class="container">
        <x-slot name="header">
            <x-breadcrumb title="Facturação" breadcrumb="Facturação" />
        </x-slot>
        <br>

        <div class="row">
            <div class="col-md-4">
                <div class="card card-dark">
                    <div class="card-header">
                        <div class="card-title"> 
                            <span>Filtros</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('faturas.filtrar') }}" method="GET">
                            <label for="dataInicial">Data Inicial:</label>
                            <input type="date" name="dataInicial" id="dataInicial" class="form-control">

                            <label for="dataFinal">Data Final:</label>
                            <input type="date" name="dataFinal" id="dataFinal" class="form-control">

                            <label for="estado">Estado</label>
                            <select name="estado" id="estado" class="form-control">
                                <option value="">Selecionar</option>
                                <option value="pago">Pago</option>
                                <option value="por pagar">Por Pagar</option>
                                <option value="vencido">Vencido</option>
                            </select>

                            <label for="">Valores Intervalo</label>
                            <input type="range" name="intervalo" id="intervalo" class="form-control">

                            <a type="submit" class="form-control btn btn-sm btn-dark">Filtrar</a>
                        </form>
                    </div>
                </div>
            
            </div>

            <div class="col-md-8">
                <div class="card card-dark">
                    <div class="card-header">
                        <div class="card-title"> 
                            <span>Facturas</span>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (isset($tableData['headers']) && isset($tableData['rows']))
                            <x-table :tableData="$tableData" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <canvas id="faturasPorStatus"></canvas>

    <!-- Adicione os CDNs para o Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        var ctx = document.getElementById('faturasPorStatus').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pago', 'Por Pagar', 'Em Atraso'],
                datasets: [{
                    label: 'Número de Faturas',
                    data: [{{ $faturasPagas }}, {{ $faturasPorPagar }}, {{ $faturasEmAtraso }}],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Status das Faturas'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Número de Faturas'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Status'
                        }
                    }
                }
            }
        });
    </script>

</x-app-layout>