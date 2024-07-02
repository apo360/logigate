<x-app-layout>
    <h1 class="text-2xl font-bold mb-6">Impostos e Tarifas</h1>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Resumo dos Impostos e Tarifas</h2>
        <p>Aqui está o resumo dos impostos e tarifas aplicáveis aos processos de importação.</p>
        <!-- Adicione aqui a lógica para mostrar o resumo -->
    </div>

    <div class="bg-white p-6 rounded-lg shadow mt-6">
        <h2 class="text-lg font-semibold mb-4">Tabela de Tarifas por Tipo de Produto</h2>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-indigo-600 tracking-wider">Tipo de Produto</th>
                    <th class="px-6 py-3 border-b-2 border-gray-300 text-left leading-4 text-indigo-600 tracking-wider">Tarifa</th>
                </tr>
            </thead>
            <tbody>
                <!-- Adicione aqui a lógica para mostrar as tarifas -->
            </tbody>
        </table>
    </div>

    <hr>

    <h1 class="text-2xl font-bold mb-6">Cálculo de Impostos</h1>

    <form action="" method="POST" class="bg-white p-6 rounded-lg shadow">
        @csrf
        <div class="mb-4">
            <label for="valor_produto" class="block text-sm font-medium text-gray-700">Valor do Produto</label>
            <input type="text" name="valor_produto" id="valor_produto" class="mt-1 block w-full" required>
        </div>
        <div class="mb-4">
            <label for="tipo_produto" class="block text-sm font-medium text-gray-700">Tipo de Produto</label>
            <input type="text" name="tipo_produto" id="tipo_produto" class="mt-1 block w-full" required>
        </div>
        <div class="mb-4">
            <label for="origem" class="block text-sm font-medium text-gray-700">Origem</label>
            <input type="text" name="origem" id="origem" class="mt-1 block w-full" required>
        </div>
        <button type="submit" class="btn btn-primary">Calcular</button>
    </form>

    @if(isset($resultado))
        <div class="bg-white p-6 rounded-lg shadow mt-6">
            <h2 class="text-lg font-semibold mb-4">Resultado do Cálculo</h2>
            <p>{{ $resultado }}</p>
        </div>
    @endif
</x-app-layout>