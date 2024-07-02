<x-app-layout>
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Rastreamento de Processos</h1>

    <!-- Visão Geral do Rastreamento -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h2 class="text-lg font-semibold mb-2">Visão Geral</h2>
        <p>Acompanhe o status e a localização dos seus processos de importação em tempo real.</p>
    </div>

    <!-- Formulário de Rastreamento -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h2 class="text-lg font-semibold mb-2">Rastrear Processo</h2>
        <form method="GET" action="">
            <div class="mb-4">
                <label for="trackingNumber" class="block text-sm font-medium text-gray-700">Número de Rastreamento</label>
                <input type="text" name="trackingNumber" id="trackingNumber" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" placeholder="Digite o número de rastreamento">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">Rastrear</button>
            </div>
        </form>
    </div>

    <!-- Histórico de Rastreamento -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h2 class="text-lg font-semibold mb-2">Histórico de Rastreamento</h2>
        <p>Visualize o histórico de rastreamento dos seus processos.</p>
        <!-- Exemplo de tabela de histórico -->
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="px-4 py-2">Número de Rastreamento</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Data</th>
                    <th class="px-4 py-2">Localização</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aqui você pode inserir os dados do histórico -->
                <tr>
                    <td class="border px-4 py-2">123456</td>
                    <td class="border px-4 py-2">Em trânsito</td>
                    <td class="border px-4 py-2">2024-06-10</td>
                    <td class="border px-4 py-2">São Paulo, Brasil</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Notificações de Rastreamento -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h2 class="text-lg font-semibold mb-2">Notificações</h2>
        <p>Configure notificações para receber atualizações sobre o status dos seus processos.</p>
        <form method="POST" action="">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" placeholder="Digite seu e-mail">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">Configurar Notificações</button>
            </div>
        </form>
    </div>

    <!-- Mapa de Rastreamento -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h2 class="text-lg font-semibold mb-2">Mapa de Rastreamento</h2>
        <div id="map" style="height: 400px;"></div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var map = L.map('map').setView([51.505, -0.09], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(map);
            });
        </script>
    </div>
</div>

</x-app-layout>