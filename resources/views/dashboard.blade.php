<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

                <div class="row">
                    <div class="col-md-4">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Processos</h2>
                            <p>Total: {{ \App\Models\Processo::where('empresa_id', auth()->user()->empresas->first()->id)->count() }}</p>
                            <canvas id="processosChart"></canvas>
                            <a href="#" class="text-indigo-600">Ver todos</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Clientes</h2>
                            <p>Total: {{ \App\Models\Customer::where('empresa_id', auth()->user()->empresas->first()->id)->count() }}</p>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
