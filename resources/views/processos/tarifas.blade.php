<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => '📊 Impostos e Tarifas', 'url' => route('licenciamentos.index')]
    ]" separator="/" />

    <div class="bg-gray-100 p-6">
        <div class=" mx-auto">
            <!-- Seção de Resumo -->
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <h2 class="text-lg font-semibold mb-4">Resumo dos Impostos e Tarifas</h2>
                <p class="text-gray-700">Aqui está o resumo dos impostos e tarifas aplicáveis aos processos de importação.</p>
                <!-- Adicione aqui a lógica para mostrar o resumo -->
            </div>

            <div class="flex flex-wrap">
                <!-- Coluna 1 -->
                <div class="w-full md:w-1/2 p-2">
                    <!-- Seção de Tabela de Tarifas -->
                    <div class="bg-white p-6 rounded-lg shadow mb-6">
                        <h2 class="text-lg font-semibold mb-4">Tabela de Tarifas por Tipo de Produto</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Tipo de Produto</th>
                                        <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Tarifa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Exemplo de dados dinâmicos -->
                                    <tr>
                                        <td class="px-6 py-4 border-b border-gray-300">Peixe</td>
                                        <td class="px-6 py-4 border-b border-gray-300">10%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Coluna 2 -->
                <div class="w-full md:w-1/2 p-2">
                    <!-- Seção de Cálculo de Impostos -->
                    <div class="bg-white p-6 rounded-lg shadow mb-6">
                        <h2 class="text-lg font-semibold mb-4">Cálculo de Impostos</h2>
                        <form action="" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="valor_produto" class="block text-sm font-medium text-gray-700">Valor do Produto</label>
                                <input type="text" name="valor_produto" id="valor_produto" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label for="tipo_produto" class="block text-sm font-medium text-gray-700">Tipo de Produto</label>
                                <input type="text" name="tipo_produto" id="tipo_produto" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required>
                            </div>
                            <div>
                                <label for="origem" class="block text-sm font-medium text-gray-700">Origem</label>
                                <input type="text" name="origem" id="origem" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required>
                            </div>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Calcular</button>
                        </form>

                        <!-- Resultado do Cálculo -->
                        @if(isset($resultado))
                            <div class="mt-6 p-4 bg-gray-50 rounded-md">
                                <h3 class="text-lg font-semibold mb-2">Resultado do Cálculo</h3>
                                <p class="text-gray-700">{{ $resultado }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Seção de Tabela Detalhada de Impostos -->
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <h2 class="text-lg font-semibold mb-4">Tabela de Impostos e Tarifas</h2>
                <input type="text" placeholder="🔍 Buscar imposto ou tarifa..." class="w-full p-2 border border-gray-300 rounded-md mb-4">

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border-collapse border border-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Código Aduaneiro</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Descrição</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Taxa de Importação</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">IVA</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">IEQ</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Valor Base</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Total Tributos</th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm font-semibold text-gray-700">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($impostos as $imposto)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 border-b border-gray-300">{{ $imposto['codigo'] }}</td>
                                    <td class="px-6 py-4 border-b border-gray-300">{{ $imposto['descricao'] }}</td>
                                    <td class="px-6 py-4 border-b border-gray-300">{{ $imposto['taxa_importacao'] }}%</td>
                                    <td class="px-6 py-4 border-b border-gray-300">{{ $imposto['iva'] }}%</td>
                                    <td class="px-6 py-4 border-b border-gray-300">{{ $imposto['ieq'] }}%</td>
                                    <td class="px-6 py-4 border-b border-gray-300">{{ number_format($imposto['valor_base'], 2, ',', '.') }} Kz</td>
                                    <td class="px-6 py-4 border-b border-gray-300 font-semibold text-red-600">
                                        {{ number_format($imposto['total_tributos'], 2, ',', '.') }} Kz
                                    </td>
                                    <td class="px-6 py-4 border-b border-gray-300">
                                        <button class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600">Editar</button>
                                        <button class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600">Excluir</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>