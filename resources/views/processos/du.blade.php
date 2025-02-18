<x-app-layout>
    <div class="mt-4 p-4">
        <h1 class="text-2xl font-bold mb-4">Lista de XMLs para Aprovação</h1>

        <!-- Input de Pesquisa -->
        <div class="mb-6">
            <input
                type="text"
                id="searchInput"
                placeholder="Pesquisar usuários..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>

        <!-- Lista de Usuários e XMLs -->
        <div class="overflow-x-auto" id="userList">
            <!-- Exemplo de usuário 1 -->
            <div class="mb-6 user-group">
                <h2 class="text-xl font-semibold mb-2">João Silva</h2>
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
                    </tbody>
                </table>
            </div>

            <!-- Exemplo de usuário 2 -->
            <div class="mb-6 user-group">
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

    <!-- Script para filtragem -->
    <script>
        document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase(); // Texto digitado no input
            const userGroups = document.querySelectorAll('.user-group'); // Todos os grupos de usuários

            userGroups.forEach(group => {
                const userName = group.querySelector('h2').textContent.toLowerCase(); // Nome do usuário
                if (userName.includes(searchTerm)) {
                    group.style.display = 'block'; // Mostra o grupo se o nome corresponder
                } else {
                    group.style.display = 'none'; // Oculta o grupo se o nome não corresponder
                }
            });
        });
    </script>
</x-app-layout>