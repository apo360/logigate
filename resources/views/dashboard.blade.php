<x-app-layout>
    <head>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/pt-br.min.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

        <style>
            .chart-container {
                height: 300px; /* Altura fixa para os gráficos */
            }
            #map {
            height: 325px;
            width: 100%;
        }
        </style>
    </head>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')]
    ]" separator="/" />

    <div class="py-2">
        <div class="mx-auto">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="flex">
                                <div class="w-full sm:w-1/2 lg:w-1/3 p-2">
                                    <!-- Small Box -->
                                    <div class="bg-gradient-to-r from-amber-200 to-yellow-500 rounded-lg shadow-lg p-6 text-black">
                                        <!-- Inner Content -->
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <h3 class="text-2xl font-bold">{{ count($licenciamento) }}</h3>
                                                <p class="text-sm">Licenciamentos</p>
                                            </div>
                                            <i class="fas fa-file-lines text-4xl"></i>
                                        </div>

                                        <!-- Importação e Exportação -->
                                        <div class="mt-4">
                                            <div class="flex justify-between text-center">
                                                <!-- Importação -->
                                                <div class="flex items-center">
                                                    <i class="fas fa-file-import text-2xl text-blue-500 mr-2"></i>
                                                    <div>
                                                        <span class="text-sm">Importação:</span>
                                                        <h4 class="text-lg font-bold">{{ count($licenciamento->where('tipo_declaracao', 11)) }}</h4>
                                                    </div>
                                                </div>

                                                <!-- Exportação -->
                                                <div class="flex items-center">
                                                    <i class="fas fa-file-export text-2xl text-green-500 mr-2"></i>
                                                    <div>
                                                        <span class="text-sm">Exportação:</span>
                                                        <h4 class="text-lg font-bold">{{ count($licenciamento->where('tipo_declaracao', 21)) }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Link para Mais Informações -->
                                        <a href="{{ route('licenciamento.estatistica') }}" class="block mt-4 text-center text-white hover:text-gray-200">
                                            Mais info <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="w-full sm:w-1/2 lg:w-1/3 p-2">
                                    <!-- small box -->
                                    <div class="bg-gradient-to-r from-indigo-400 to-cyan-400 rounded-lg shadow-lg p-6 text-black">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <h3 class="text-2xl font-bold">{{ count($processos) }}</h3>
                                                <p class="text-sm">Processos</p>
                                            </div>
                                            <i class="fas fa-tasks text-4xl"></i> <!-- Ícone de processos -->
                                        </div>
                                        <div class="mt-4">
                                            <div class="flex justify-between text-center">

                                                <!-- Processos de Importação -->
                                                <div class="flex items-center">
                                                    <i class="fas fa-box-open text-2xl text-blue-500 mr-2"></i>
                                                    <div>
                                                        <span class="text-sm">Importação:</span>
                                                        <h4 class="text-lg font-bold">{{ count($processos->where('TipoProcesso', 10)) }}</h4>
                                                    </div>
                                                </div>

                                                <!-- Processos Exportação -->
                                                <div class="flex items-center">
                                                    <i class="fas fa-ship text-2xl text-red-500 mr-2"></i>
                                                    <div>
                                                        <span class="text-sm">Exportação:</span>
                                                        <h4 class="text-lg font-bold">{{ count($processos->where('TipoProcesso', 1)) }}</h4>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <a href="{{ route('processos.estatistica') }}" class="block mt-4 text-center text-white hover:text-gray-200">
                                            Mais info <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="w-full sm:w-1/2 lg:w-1/3 p-2">
                                    <!-- small box -->
                                    <div class="bg-gradient-to-r from-lime-400 to-lime-500 rounded-lg shadow-lg p-6 text-black">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <h3 class="text-2xl font-bold">{{ count($clientes) + count($exportadores) }}</h3>
                                                <p class="text-sm">Total de Clientes e Exportadores</p>
                                            </div>
                                            <i class="fas fa-users text-4xl"></i> <!-- Ícone de pessoas -->
                                        </div>
                                        <div class="mt-4">
                                            <div class="flex justify-between text-center">
                                                <!-- Total de Clientes -->
                                                <div class="flex items-center">
                                                    <i class="fas fa-user fa-2x text-yellow-400 mr-2"></i> <!-- Ícone de cliente -->
                                                    <div>
                                                        <span class="text-sm">Clientes:</span> 
                                                        <h4 class="text-lg font-bold">{{ count($clientes) }}</h4>
                                                    </div>
                                                </div>

                                                <!-- Total de Exportadores -->
                                                <div class="flex items-center">
                                                    <i class="fas fa-people-carry-box text-gray-900 fa-2x mr-2"></i> <!-- Ícone de exportador -->
                                                    <div>
                                                        <span class="text-sm">Exportadores:</span> 
                                                        <h4 class="text-lg font-bold">{{ count($exportadores) }}</h4>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <a href="" class="block mt-4 text-center text-white hover:text-gray-200">
                                            Mais info <i class="fas fa-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap">
                                <!-- Card de Processos por Estância -->
                                <div class="w-full sm:w-1/2 lg:w-1/2 p-2">
                                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                        <!-- Cabeçalho do Card -->
                                        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-500 to-blue-600">
                                            <div class="flex items-center">
                                                <i class="fas fa-building text-white text-2xl mr-2"></i>
                                                <h4 class="text-lg font-semibold text-white">Processos por Estância</h4>
                                            </div>
                                            <button class="text-white hover:text-gray-200 focus:outline-none" onclick="toggleCard('estanciaCard')">
                                                <i class="fas fa-minus" id="estanciaIcon"></i>
                                            </button>
                                        </div>
                                        <!-- Corpo do Card -->
                                        <div id="estanciaCard" class="p-4">
                                            <div class="chart-container">
                                                <canvas id="processosPorEstanciaChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card de Processos por Estado -->
                                <div class="w-full sm:w-1/2 lg:w-1/2 p-2">
                                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                        <!-- Cabeçalho do Card -->
                                        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-500 to-green-600">
                                            <div class="flex items-center">
                                                <i class="fas fa-chart-bar text-white text-2xl mr-2"></i>
                                                <h4 class="text-lg font-semibold text-white">Processos por Estado</h4>
                                            </div>
                                            <button class="text-white hover:text-gray-200 focus:outline-none" onclick="toggleCard('estadoCard')">
                                                <i class="fas fa-minus" id="estadoIcon"></i>
                                            </button>
                                        </div>
                                        <!-- Corpo do Card -->
                                        <div id="estadoCard" class="p-4">
                                            <div class="chart-container">
                                                <canvas id="processosPorEstadoChart"></canvas>
                                            </div>
                                        </div>
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

                    <div class="flex flex-wrap">
                        <!-- Card do Calendário -->
                        <div class="w-full sm:w-1/2 lg:w-1/3 p-2">
                            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg overflow-hidden">
                                <!-- Cabeçalho do Card -->
                                <div class="flex justify-between items-center border-b border-green-400">
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-alt text-white text-2xl mr-2"></i>
                                        <h3 class="text-lg font-semibold text-white">Calendário</h3>
                                    </div>
                                    <!-- Botões de Controle -->
                                    <div class="flex items-center space-x-2">
                                        <!-- Dropdown -->
                                        <div class="relative">
                                            <button class="bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-1 rounded-lg focus:outline-none">
                                                <i class="fas fa-bars"></i>
                                            </button>
                                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden">
                                                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Adicionar Evento</a>
                                                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Limpar Eventos</a>
                                                <div class="border-t border-gray-200"></div>
                                                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Ver Calendário</a>
                                            </div>
                                        </div>
                                        <!-- Botão de Minimizar -->
                                        <button class="bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-1 rounded-lg focus:outline-none" onclick="toggleCard('calendarBody')">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <!-- Botão de Fechar -->
                                        <button class="bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-1 rounded-lg focus:outline-none" onclick="removeCard('calendarCard')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Corpo do Card -->
                                <div id="calendarBody" class="">
                                    <div id="calendar"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Card de Processos Relevantes -->
                        <div class="w-full sm:w-1/2 lg:w-1/2 p-2">
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                <!-- Cabeçalho do Card -->
                                <div class="flex justify-between items-center p-4 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        <i class="fas fa-tasks text-blue-500 mr-2"></i>
                                        Processos Relevantes
                                    </h3>
                                    <!-- Botões de Controle -->
                                    <div class="flex items-center space-x-2">
                                        <!-- Botão de Minimizar -->
                                        <button class="text-gray-500 hover:text-gray-700 focus:outline-none" onclick="toggleCard('processesBody')">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <!-- Botão de Fechar -->
                                        <button class="text-gray-500 hover:text-gray-700 focus:outline-none" onclick="removeCard('processesCard')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Corpo do Card -->
                                <div id="processesBody" class="p-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID do Processo</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridade</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <!-- Processo 1 -->
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <a href="#" class="text-blue-500 hover:text-blue-700">#1234</a>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">Importação de Eletrônicos</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 py-1 text-sm font-semibold bg-green-100 text-green-800 rounded-full">Concluído</span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="h-2 w-24 bg-gray-200 rounded-full">
                                                            <div class="h-2 bg-green-500 rounded-full" style="width: 90%;"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <!-- Processo 2 -->
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <a href="#" class="text-blue-500 hover:text-blue-700">#5678</a>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">Exportação de Roupas</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 py-1 text-sm font-semibold bg-yellow-100 text-yellow-800 rounded-full">Pendente</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- Rodapé do Card -->
                                <div class="flex justify-between items-center p-4 border-t">
                                    <a href="{{ route('processos.create') }}" class="text-blue-500 hover:text-blue-700 btn">Adicionar Novo Processo</a>
                                    <a href="{{ route('processos.index') }}" class="text-gray-500 hover:text-gray-700 btn">Ver Todos os Processos</a>
                                </div>
                            </div>
                        </div>

                        <div class="w-full sm:w-1/2 lg:w-1/3 p-2"></div>
                    </div>
                    
                </div>

                <div class="p-2">
                    <!-- Card do Mapa de Importações -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <!-- Cabeçalho do Card -->
                        <div class="flex justify-between items-center p-4 border-b">
                            <h3 class="text-lg font-semibold">Mapa de Importações por País</h3>
                            <div class="flex items-center space-x-2">
                                <!-- Botão de Minimizar -->
                                <button class="text-gray-500 hover:text-gray-700 focus:outline-none" onclick="toggleCard('mapBody')">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <!-- Botão de Fechar -->
                                <button class="text-gray-500 hover:text-gray-700 focus:outline-none" onclick="removeCard('mapCard')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Corpo do Card -->
                        <div id="mapBody" class="p-4">
                            <div class="flex flex-col md:flex-row">
                                <!-- Mapa -->
                                <div class="w-full md:w-2/3">
                                    <div id="map"></div>
                                </div>
                                <!-- Painel de Dados -->
                                <div class="w-full md:w-1/3 bg-green-600 p-4 text-white">
                                    <div class="description-block mb-4">
                                        <h5 class="description-header">5000</h5>
                                        <span class="description-text">Total de Importações</span>
                                    </div>
                                    <div class="description-block mb-4">
                                        <h5 class="description-header">China</h5>
                                        <span class="description-text">Maior Exportador</span>
                                    </div>
                                    <div class="description-block">
                                        <h5 class="description-header">Eletrônicos</h5>
                                        <span class="description-text">Categoria Mais Importada</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <body class="bg-gray-100">
    <div class="p-4">
        <!-- Card de Comparação de Faturação -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Cabeçalho do Card -->
            <div class="flex justify-between items-center p-4 border-b">
                <h5 class="text-lg font-semibold">Comparação de Faturação</h5>
                <div class="flex items-center space-x-2">
                    <!-- Botão de Minimizar -->
                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none" onclick="toggleCard('reportBody')">
                        <i class="fas fa-minus"></i>
                    </button>
                    <!-- Dropdown -->
                    <div class="relative">
                        <button class="text-gray-500 hover:text-gray-700 focus:outline-none">
                            <i class="fas fa-wrench"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden">
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Exportar Dados</a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Configurações</a>
                            <div class="border-t border-gray-200"></div>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Ajuda</a>
                        </div>
                    </div>
                    <!-- Botão de Fechar -->
                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none" onclick="removeCard('reportCard')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- Corpo do Card -->
            <div id="reportBody" class="p-4">
                <div class="flex flex-col md:flex-row">
                    <!-- Gráfico de Faturação -->
                    <div class="w-full md:w-2/3">
                        <p class="text-center font-semibold mb-4">Faturação: Jan 2023 - Dez 2023 vs Jan 2022 - Dez 2022</p>
                        <div class="chart">
                            <canvas id="salesChart" height="180"></canvas>
                        </div>
                    </div>
                    <!-- Métricas -->
                    <div class="w-full md:w-1/3 pl-4">
                        <p class="text-center font-semibold mb-4">Métricas</p>
                        <div class="space-y-4">
                            <!-- Faturação Total -->
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <span class="text-sm text-gray-600">Faturação Total (2023)</span>
                                <h5 class="text-lg font-semibold">$350,210.43</h5>
                                <span class="text-sm text-green-600"><i class="fas fa-caret-up"></i> 17% vs 2022</span>
                            </div>
                            <!-- Custos Totais -->
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <span class="text-sm text-gray-600">Custos Totais (2023)</span>
                                <h5 class="text-lg font-semibold">$120,390.90</h5>
                                <span class="text-sm text-red-600"><i class="fas fa-caret-down"></i> 5% vs 2022</span>
                            </div>
                            <!-- Lucro Total -->
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <span class="text-sm text-gray-600">Lucro Total (2023)</span>
                                <h5 class="text-lg font-semibold">$229,819.53</h5>
                                <span class="text-sm text-green-600"><i class="fas fa-caret-up"></i> 25% vs 2022</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Rodapé do Card -->
            <div class="p-4 border-t">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Faturação Total -->
                    <div class="text-center">
                        <span class="text-sm text-gray-600">Faturação Total (2023)</span>
                        <h5 class="text-lg font-semibold">$350,210.43</h5>
                        <span class="text-sm text-green-600"><i class="fas fa-caret-up"></i> 17% vs 2022</span>
                    </div>
                    <!-- Custos Totais -->
                    <div class="text-center">
                        <span class="text-sm text-gray-600">Custos Totais (2023)</span>
                        <h5 class="text-lg font-semibold">$120,390.90</h5>
                        <span class="text-sm text-red-600"><i class="fas fa-caret-down"></i> 5% vs 2022</span>
                    </div>
                    <!-- Lucro Total -->
                    <div class="text-center">
                        <span class="text-sm text-gray-600">Lucro Total (2023)</span>
                        <h5 class="text-lg font-semibold">$229,819.53</h5>
                        <span class="text-sm text-green-600"><i class="fas fa-caret-up"></i> 25% vs 2022</span>
                    </div>
                    <!-- Crescimento -->
                    <div class="text-center">
                        <span class="text-sm text-gray-600">Crescimento</span>
                        <h5 class="text-lg font-semibold">12%</h5>
                        <span class="text-sm text-green-600"><i class="fas fa-caret-up"></i> vs 2022</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Função para alternar entre minimizar e maximizar o card
        function toggleCard(cardId) {
            const card = document.getElementById(cardId);
            const icon = card.previousElementSibling.querySelector('.fa-minus');

            if (card.classList.contains('hidden')) {
                card.classList.remove('hidden');
                icon.classList.remove('fa-plus');
                icon.classList.add('fa-minus');
            } else {
                card.classList.add('hidden');
                icon.classList.remove('fa-minus');
                icon.classList.add('fa-plus');
            }
        }

        // Função para remover o card
        function removeCard(cardId) {
            const card = document.getElementById(cardId);
            card.remove();
        }

        // Dados para o gráfico de faturação
        const salesData = {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [
                {
                    label: '2022',
                    data: [28000, 32000, 40000, 48000, 42000, 55000, 60000, 65000, 70000, 75000, 80000, 85000],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false
                },
                {
                    label: '2023',
                    data: [30000, 35000, 45000, 50000, 48000, 60000, 68000, 72000, 78000, 82000, 88000, 92000],
                    borderColor: 'rgba(54, 162, 235, 1)',
                    fill: false
                },
                {
                    label: '2024',
                    data: [35000, 40000, 50000, 55000, 53000, 65000, 73000, 77000, 83000, 87000, 93000, 97000],
                    borderColor: 'rgba(255, 99, 132, 1)',
                    fill: false
                },
                {
                    label: '2025 (Previsão)',
                    data: [40000, 45000, 55000, 60000, 58000, 70000, 78000, 82000, 88000, 92000, 98000, 102000],
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderDash: [5, 5], // Linha tracejada para previsões
                    fill: false
                }
            ]
        };

        // Configuração do gráfico de faturação
        const salesConfig = {
            type: 'line',
            data: salesData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        // Renderizar o gráfico de faturação
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, salesConfig);
    </script>
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

        // Função para alternar entre minimizar e maximizar os cards
        function toggleCard(cardId) {
            const card = document.getElementById(cardId);
            const icon = document.getElementById(cardId.replace('Card', 'Icon'));

            if (card.classList.contains('hidden')) {
                card.classList.remove('hidden');
                icon.classList.remove('fa-plus');
                icon.classList.add('fa-minus');
            } else {
                card.classList.add('hidden');
                icon.classList.remove('fa-minus');
                icon.classList.add('fa-plus');
            }
        }

        // Dados de exemplo (substitua pelos dados reais da sua tabela)
        const estancias = [1, 2, 3, 4]; // IDs das estâncias
        const tiposProcesso = ['Tipo 1', 'Tipo 2', 'Tipo 3']; // Tipos de processo
        const dados = {
            labels: estancias.map(e => `Estância ${e}`), // Rótulos do eixo X
            datasets: [
                {
                    label: 'Tipo 1',
                    data: [10, 20, 30, 15], // Quantidade de processos do Tipo 1 por estância
                    backgroundColor: 'rgba(255, 99, 132, 0.6)', // Cor da barra
                },
                {
                    label: 'Tipo 2',
                    data: [15, 25, 10, 20], // Quantidade de processos do Tipo 2 por estância
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Cor da barra
                },
                {
                    label: 'Tipo 3',
                    data: [5, 10, 20, 25], // Quantidade de processos do Tipo 3 por estância
                    backgroundColor: 'rgba(2, 34, 34, 0.6)', // Cor da barra
                }
            ]
        };

        // Configuração do gráfico
        const config = {
            type: 'bar',
            data: dados,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Processos por Estância (Agrupados por Tipo de Processo)'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        stacked: true, // Barras empilhadas no eixo X
                    },
                    y: {
                        stacked: true, // Barras empilhadas no eixo Y
                        beginAtZero: true
                    }
                }
            }
        };

        // Renderizar o gráfico
        const ctx = document.getElementById('processosPorEstanciaChart').getContext('2d');
        new Chart(ctx, config);
    </script>

    <script>
        // Dados de exemplo para Processos por Estado
        const processosPorEstadoData = {
            labels: @json($processosPorEstado->pluck('Estado')), // Estados dos processos
            datasets: [{
                label: 'Processos',
                data: @json($processosPorEstado->pluck('total')), // Quantidade de processos por estado
                backgroundColor: [
                    'rgba(131, 11, 37, 0.6)',
                    'rgba(7, 79, 126, 0.6)',
                    'rgba(139, 169, 169, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(184, 103, 22, 0.6)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        };

        // Configuração do gráfico de Processos por Estado
        const processosPorEstadoConfig = {
            type: 'bar',
            data: processosPorEstadoData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Processos por Estado'
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        // Renderizar o gráfico de Processos por Estado
        const processosPorEstadoCtx = document.getElementById('processosPorEstadoChart').getContext('2d');
        new Chart(processosPorEstadoCtx, processosPorEstadoConfig);
    </script>

    <script>
        // Função para remover o card
        function removeCard(cardId) {
            const card = document.getElementById(cardId);
            card.remove();
        }

        // Inicializar o calendário com FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth', // Visualização inicial (mês)
                locale: 'pt-br', // Configuração para português do Brasil
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    {
                        title: 'Reunião de Equipe',
                        start: '2023-10-10T10:00:00',
                        end: '2023-10-10T12:00:00'
                    },
                    {
                        title: 'Entrega de Projeto',
                        start: '2023-10-15',
                        end: '2023-10-17'
                    }
                ]
            });
            calendar.render();
        });

        // Mostrar/ocultar o dropdown
        document.querySelector('.relative button').addEventListener('click', function() {
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('hidden');
        });
    </script>

    <script>

        // Inicializar o mapa com Leaflet
        const map = L.map('map').setView([20, 0], 2); // Centro do mapa e zoom inicial

        // Adicionar camada de mapa (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Dados de exemplo: Países e quantidade de importações
        const countries = [
            { name: 'Brasil', lat: -14.2350, lng: -51.9253, imports: 1000 },
            { name: 'China', lat: 35.8617, lng: 104.1954, imports: 5000 },
            { name: 'Estados Unidos', lat: 37.0902, lng: -95.7129, imports: 3000 },
            { name: 'Alemanha', lat: 51.1657, lng: 10.4515, imports: 2000 },
            { name: 'Índia', lat: 20.5937, lng: 78.9629, imports: 1500 }
        ];

        // Definir cores com base na quantidade de importações
        const getColor = (imports) => {
            if (imports > 4000) return '#00441b'; // Verde escuro
            if (imports > 2000) return '#238b45'; // Verde médio
            if (imports > 1000) return '#66c2a4'; // Verde claro
            return '#b2e2e2'; // Verde muito claro
        };

        // Adicionar marcadores ao mapa
        countries.forEach(country => {
            L.circleMarker([country.lat, country.lng], {
                color: getColor(country.imports),
                fillColor: getColor(country.imports),
                fillOpacity: 0.8,
                radius: 10 // Tamanho do marcador
            }).addTo(map).bindPopup(`${country.name}<br>Importações: ${country.imports}`);
        });
    </script>
</x-app-layout>
