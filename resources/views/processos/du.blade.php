<x-app-layout>
    <div class="mt-4 p-4">
        <h1 class="text-2xl font-bold mb-4">Lista de XMLs para Aprovação</h1>
        <div class="overflow-x-auto">
            <!-- Exemplo de agrupamento por usuário -->
            <div class="mb-6">
                <!-- Cabeçalho do usuário -->
                <h2 class="text-xl font-semibold mb-2">João Silva</h2>
                <!-- Tabela de XMLs do usuário -->
                <table class="min-w-full bg-white border border-gray-300 mb-4">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 border-b">Data</th>
                            <th class="py-2 px-4 border-b">Hora</th>
                            <th class="py-2 px-4 border-b">Nome do XML</th>
                            <th class="py-2 px-4 border-b">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- XML 1 do usuário -->
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">2023-10-01</td>
                            <td class="py-2 px-4 border-b">14:30</td>
                            <td class="py-2 px-4 border-b">processo_123.xml</td>
                            <td class="py-2 px-4 border-b">
                                <button class="bg-green-500 text-white py-1 px-3 rounded hover:bg-green-600">Download</button>
                                <button class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600">Corrigir XML</button>
                                <button class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600">Retificar</button>
                            </td>
                        </tr>
                        <!-- XML 2 do usuário -->
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">2023-10-02</td>
                            <td class="py-2 px-4 border-b">10:15</td>
                            <td class="py-2 px-4 border-b">processo_456.xml</td>
                            <td class="py-2 px-4 border-b">
                                <button class="bg-green-500 text-white py-1 px-3 rounded hover:bg-green-600">Download</button>
                                <button class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600">Corrigir XML</button>
                                <button class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600">Retificar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Outro usuário -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">Maria Souza</h2>
                <table class="min-w-full bg-white border border-gray-300 mb-4">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 border-b">Data</th>
                            <th class="py-2 px-4 border-b">Hora</th>
                            <th class="py-2 px-4 border-b">Nome do XML</th>
                            <th class="py-2 px-4 border-b">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- XML 1 do usuário -->
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">2023-10-03</td>
                            <td class="py-2 px-4 border-b">09:00</td>
                            <td class="py-2 px-4 border-b">processo_789.xml</td>
                            <td class="py-2 px-4 border-b">
                                <button class="bg-green-500 text-white py-1 px-3 rounded hover:bg-green-600">Download</button>
                                <button class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600">Corrigir XML</button>
                                <button class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600">Retificar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>