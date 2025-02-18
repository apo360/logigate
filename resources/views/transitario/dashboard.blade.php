@extends('layouts.transitario.app') <!-- Use o layout principal da aplicação -->

@section('content')
<div class="container mx-auto p-4">
    <!-- Título -->
    <h1 class="text-2xl font-bold mb-4">Dashboard do Transitário</h1>
    <p class="text-gray-600 mb-8">Bem-vindo ao painel do Transitário. Aqui você pode gerenciar suas operações logísticas.</p>

    <!-- Resumo de Operações -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total de Funcionários -->
        <div class="bg-gradient-to-r from-red-50 to-red-100 p-6 rounded-lg shadow-xl border-l-4 border-red-500 transform transition-transform duration-300 hover:scale-105">
            <div class="flex items-center">
                <i class="fas fa-users text-3xl text-red-500 mr-4"></i>
                <div>
                    <p class="text-gray-600">Total de Funcionários</p>
                    <p class="text-2xl font-bold">400</p>
                </div>
            </div>
        </div>

        <!-- Total de Transportes -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-lg shadow-xl border-l-4 border-blue-500 transform transition-transform duration-300 hover:scale-105">
            <div class="flex items-center">
                <i class="fas fa-truck text-3xl text-blue-500 mr-4"></i>
                <div>
                    <p class="text-gray-600">Total de Transportes</p>
                    <p class="text-2xl font-bold">150</p>
                </div>
            </div>
        </div>

        <!-- Transportes Ativos -->
        <div class="bg-gradient-to-r from-green-50 to-green-100 p-6 rounded-lg shadow-xl border-l-4 border-green-500 transform transition-transform duration-300 hover:scale-105">
            <div class="flex items-center">
                <i class="fas fa-truck-moving text-3xl text-green-500 mr-4"></i>
                <div>
                    <p class="text-gray-600">Transportes Ativos</p>
                    <p class="text-2xl font-bold">25</p>
                </div>
            </div>
        </div>

        <!-- Transportes Concluídos -->
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-6 rounded-lg shadow-xl border-l-4 border-purple-500 transform transition-transform duration-300 hover:scale-105">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-3xl text-purple-500 mr-4"></i>
                <div>
                    <p class="text-gray-600">Transportes Concluídos</p>
                    <p class="text-2xl font-bold">125</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Transportes Ativos -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-bold mb-4">Transportes Ativos</h2>
        <table class="w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-3 text-left">Código</th>
                    <th class="p-3 text-left">Origem</th>
                    <th class="p-3 text-left">Destino</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b">
                    <td class="p-3">#12345</td>
                    <td class="p-3">Luanda</td>
                    <td class="p-3">Benguela</td>
                    <td class="p-3"><span class="bg-green-100 text-green-600 px-2 py-1 rounded-full">Em Trânsito</span></td>
                </tr>
                <tr class="border-b">
                    <td class="p-3">#12346</td>
                    <td class="p-3">Huambo</td>
                    <td class="p-3">Lubango</td>
                    <td class="p-3"><span class="bg-yellow-100 text-yellow-600 px-2 py-1 rounded-full">Aguardando</span></td>
                </tr>
                <tr class="border-b">
                    <td class="p-3">#12347</td>
                    <td class="p-3">Cabinda</td>
                    <td class="p-3">Luanda</td>
                    <td class="p-3"><span class="bg-red-100 text-red-600 px-2 py-1 rounded-full">Atrasado</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Gráficos de Desempenho -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Gráfico de Transportes por Mês -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Transportes por Mês</h2>
            <canvas id="transportesChart"></canvas> <!-- Usando Chart.js para gráficos -->
        </div>

        <!-- Gráfico de Status de Transportes -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Status de Transportes</h2>
            <canvas id="statusChart"></canvas> <!-- Usando Chart.js para gráficos -->
        </div>
    </div>

    <!-- Notificações e Alertas -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Notificações e Alertas</h2>
        <ul>
            <li class="flex items-center p-3 hover:bg-gray-50 rounded-lg">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span>Transporte #12347 está atrasado.</span>
            </li>
            <li class="flex items-center p-3 hover:bg-gray-50 rounded-lg">
                <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                <span>Novo transporte registrado: #12348.</span>
            </li>
            <li class="flex items-center p-3 hover:bg-gray-50 rounded-lg">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <span>Transporte #12345 concluído com sucesso.</span>
            </li>
        </ul>
    </div>
</div>

<!-- Inclua Chart.js para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Transportes por Mês
    const transportesCtx = document.getElementById('transportesChart').getContext('2d');
    new Chart(transportesCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Transportes',
                data: [12, 19, 3, 5, 2, 3],
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
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

    // Gráfico de Status de Transportes
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Em Trânsito', 'Aguardando', 'Atrasado'],
            datasets: [{
                label: 'Status',
                data: [25, 10, 5],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.2)',
                    'rgba(253, 224, 71, 0.2)',
                    'rgba(239, 68, 68, 0.2)'
                ],
                borderColor: [
                    'rgba(34, 197, 94, 1)',
                    'rgba(253, 224, 71, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 1
            }]
        }
    });
</script>
@endsection
