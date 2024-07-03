<x-app-layout>
    <h1 class="text-2xl font-bold mb-6">Regras e Regulamentos</h1>

    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Documentação</h2>
        <p>Documentação sobre as regras e regulamentos para importação.</p>
        <!-- Adicione links e documentos relevantes -->
        <ul>
            <li><a href="#" class="text-indigo-600">Link para autoridade governamental</a></li>
            <li><a href="#" class="text-indigo-600">Regulamentos detalhados</a></li>
        </ul>
    </div>

    <hr>

    <h1 class="text-2xl font-bold mb-6">Notificações e Alertas</h1>

    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Configuração de Alertas</h2>
        <form action="" method="POST">
            @csrf
            <div class="mb-4">
                <label for="alerta_tipo" class="block text-sm font-medium text-gray-700">Tipo de Alerta</label>
                <input type="text" name="alerta_tipo" id="alerta_tipo" class="mt-1 block w-full" required>
            </div>
            <button type="submit" class="btn btn-primary">Configurar Alerta</button>
        </form>
    </div>

</x-app-layout>