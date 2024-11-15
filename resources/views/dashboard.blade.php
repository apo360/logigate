<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-lg-4 col-6">
                                    <!-- small box -->
                                    <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>{{count($licenciamento)}}</h3>

                                        <p>Licenciamentos</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-file-lines"></i>
                                    </div>
                                    <div class="container">
                                        <div class="row text-center">
                                            <!-- Importação -->
                                            <div class="col-md-6 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-file-import fa-2x text-primary me-2"></i>
                                                <div>
                                                    <span>Importação:</span> 
                                                    <h4>{{ count($licenciamento->where('tipo_declaracao', 11)) }}</h4>
                                                </div>
                                            </div>

                                            <!-- Exportação -->
                                            <div class="col-md-6 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-file-export fa-2x text-success me-2"></i>
                                                <div>
                                                    <span>Exportação:</span> 
                                                    <h4>{{ count($licenciamento->where('tipo_declaracao', 21)) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <a href="{{ route('licenciamento.estatistica')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-6">
                                    <!-- small box -->
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h3>{{ count($processos) }}</h3>

                                            <p>Processos</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-tasks"></i> <!-- Ícone de processos -->
                                        </div>
                                        <div class="container">
                                            <div class="row text-center">
                                                <!-- Processos de Importação -->
                                                <div class="col-md-6 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-box-open fa-2x text-secondary me-2"></i> <!-- Ícone de importação -->
                                                    <div>
                                                        <span>Importação:</span> 
                                                        <h4>12</h4>
                                                    </div>
                                                </div>

                                                <!-- Processos de Exportação -->
                                                <div class="col-md-6 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-ship fa-2x text-danger me-2"></i> <!-- Ícone de exportação -->
                                                    <div>
                                                        <span>Exportação:</span> 
                                                        <h4>20</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="{{ route('processos.estatistica') }}" class="small-box-footer">Mais info <i class="fas fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-6">
                                    <!-- small box -->
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h3>{{ count($clientes) + count($exportadores) }}</h3>
                                            <p>Total de Clientes e Exportadores</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-users"></i> <!-- Ícone de pessoas -->
                                        </div>
                                        <div class="container">
                                            <div class="row text-center">
                                                <!-- Total de Clientes -->
                                                <div class="col-md-6 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-user fa-2x text-warning me-2"></i> <!-- Ícone de cliente -->
                                                    <div>
                                                        <span>Clientes:</span> 
                                                        <h4>{{ count($clientes) }}</h4>
                                                    </div>
                                                </div>

                                                <!-- Total de Exportadores -->
                                                <div class="col-md-6 d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-people-carry-box text-dark fa-2x me-2"></i> <!-- Ícone de exportador -->
                                                    <div>
                                                        <span>Exportadores:</span> 
                                                        <h4>{{ count($exportadores) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="" class="small-box-footer">Mais info <i class="fas fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="bg-white p-4 rounded-lg shadow">
                                        <h2 class="text-lg font-semibold mb-2">Processos</h2>
                                        <canvas id="processosChart"></canvas>
                                        <a href="#" class="text-indigo-600">Ver todos</a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-white p-4 rounded-lg shadow">
                                        <h2 class="text-lg font-semibold mb-2">Processos por Cliente</h2>
                                        <canvas id="processesByCustomerChart"></canvas>
                                        <a href="#" class="text-indigo-600">Ver todos</a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-white p-4 rounded-lg shadow">
                                        <h2 class="text-lg font-semibold mb-2">Países</h2>
                                        <p>Total: {{ \App\Models\Pais::count() }}</p>
                                        <canvas id="topCountriesChart"></canvas>
                                        <a href="#" class="text-indigo-600">Ver todos</a>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <div class="col-md-3">
                            <div class="col-md-12">
                                <div class="col-lg-12">
                                    <!-- small box -->
                                    <div class="small-box bg-danger">
                                        <div class="inner">
                                            <h3>{{ number_format($totalFaturamento, 2, ',', '.') }} KZ</h3> <!-- Exibe o total formatado -->
                                            <p>Total de Faturamento</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-dollar-sign"></i> <!-- Ícone de dinheiro -->
                                        </div>
                                        <div class="container">

                                            <div class="row text-center" style="margin: 3px;">
                                                <h1>Gráficos de Faturamento</h1>
                                                <!-- Gráfico Diário -->
                                                <canvas id="dailyChart" class="bg-white overflow-hidden shadow-xl sm:rounded-lg" style="margin: 3px; border-radius: 10px; box-shadow: 3px;"></canvas>
                                                <!-- Gráfico Mensal -->
                                                <canvas id="monthlyChart" class="bg-white overflow-hidden shadow-xl sm:rounded-lg" style="margin: 3px; border-radius: 10px; box-shadow: 3px;"></canvas>
                                                <!-- Gráfico Anual -->
                                                <canvas id="yearlyChart" class="bg-white overflow-hidden shadow-xl sm:rounded-lg" style="margin: 3px; border-radius: 10px; box-shadow: 3px;"></canvas>
                                            </div>

                                            <div class="row text-center">
                                                <!-- Número de Faturas -->
                                                <div class="col-md-6">
                                                    <h5>Faturas Emitidas</h5>
                                                    <h4>{{ count($numeroFaturas) }}</h4> <!-- Número total de faturas -->
                                                </div>

                                                <!-- Transações Processadas -->
                                                <div class="col-md-6">
                                                    <h5>Transações Processadas</h5>
                                                    <h4></h4> <!-- Número total de transações -->
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row text-center">

                                        </div>

                                        <a href="" class="small-box-footer">Mais info <i class="fas fa-arrow-circle-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                </div>

                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    // Faturamento Diário
    const dailyData = @json($dailyRevenue);
    const dailyLabels = dailyData.map(item => item.date);
    const dailyValues = dailyData.map(item => item.total);

    const dailyChart = new Chart(document.getElementById("dailyChart"), {
        type: "line",
        data: {
            labels: dailyLabels,
            datasets: [{
                label: "Faturamento Diário",
                data: dailyValues,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                fill: false,
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { 
                    beginAtZero: true
                }
            }
        }
    });

    // Faturamento Mensal
    const monthlyData = @json($monthlyRevenue);
    const monthlyLabels = monthlyData.map(item => `${item.month}/${item.year}`);
    const monthlyValues = monthlyData.map(item => item.total);

    const monthlyChart = new Chart(document.getElementById("monthlyChart"), {
        type: "bar",
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: "Faturamento Mensal",
                data: monthlyValues,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { 
                    beginAtZero: true
                }
            }
        }
    });

    // Faturamento Anual (Comparação com Ano Anterior)
    const yearlyData = @json($yearlyRevenue);
    const previousYearData = @json($previousYearRevenue);
    const yearlyLabels = yearlyData.map(item => item.year);
    const yearlyValues = yearlyData.map(item => item.total);
    const previousYearValues = previousYearData.map(item => item.total);

    const yearlyChart = new Chart(document.getElementById("yearlyChart"), {
        type: "line",
        data: {
            labels: yearlyLabels,
            datasets: [
                {
                    label: "Faturamento Anual",
                    data: yearlyValues,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    fill: false,
                },
                {
                    label: "Faturamento Ano Anterior",
                    data: previousYearValues,
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1,
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: { 
                    beginAtZero: true
                }
            }
        }
    });
</script>
    <script>
        var processosCtx = document.getElementById('processosChart').getContext('2d');
        var processosChart = new Chart(processosCtx, {
            type: 'bar',
            data: {
                labels: @json($processesByCountries->pluck('paisss')),
                datasets: [{
                    label: 'Processos',
                    data: @json($processesByCountries->pluck('total')),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        var topCountriesCtx = document.getElementById('topCountriesChart').getContext('2d');
        var topCountriesChart = new Chart(topCountriesCtx, {
            type: 'pie',
            data: {
                labels: @json($topCountries->pluck('pais')),
                datasets: [{
                    label: 'Top 5 Países',
                    data: @json($topCountries->pluck('total')),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });

        var processesByCustomerCtx = document.getElementById('processesByCustomerChart').getContext('2d');
        var processesByCustomerChart = new Chart(processesByCustomerCtx, {
            type: 'bar',
            data: {
                labels: @json($processesByCustomer->pluck('CompanyName')),
                datasets: [{
                    label: 'Processos por Cliente',
                    data: @json($processesByCustomer->pluck('total')),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

    </script>
</x-app-layout>
