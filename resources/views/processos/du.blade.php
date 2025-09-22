<!-- resources/views/processos/du.blade.php -->
<!-- Layout principal da aplicação, tanto para desktop quanto para dispositivos móveis -->
<!-- Uma view para o processo de desembaraço aduaneiro(ASYSCUDA) XML disponíveis e para Analisar, validar e Importar na base de dados -->
<x-app-layout>
    <!-- Cabeçalho -->
    <div class="bg-white shadow p-4 mb-4">
        <h2 class="text-lg font-semibold">Processo de Desembaraço Aduaneiro</h2>
    </div>

    <!-- Lista de XMLs para Aprovação -->
    <div class="bg-white shadow p-4 mb-6">
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
        <h2 class="mb-4">Ficheiros XML Disponíveis</h2>
        <ul class="list-group">
            @forelse ($xmlFiles as $file)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ basename($file) }}
                    <a href="{{ route('xmls.analisar', ['file' => basename($file)]) }}" class="btn btn-sm btn-primary">
                        Analisar com IA
                    </a>
                </li>
            @empty
                <li class="list-group-item">Nenhum ficheiro XML encontrado.</li>
            @endforelse
        </ul>
    </div>

    <!-- Importar Ficheiro XML -->
    <div class="bg-white shadow p-4">
        <h1 class="text-2xl font-bold mb-4">Importar Ficheiro XML</h1>
        <form action="{{ route('asycuda.upload.post') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="xmlFile" class="block text-sm font-medium text-gray-700">Selecione o ficheiro XML:</label>
                <input type="file" name="xmlFile" id="xmlFile" accept=".xml" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div class="flex space-x-4 mt-4 justify-center">
                <button type="button" id="analyzeButton" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Analisar Ficheiro
                </button>
                <button type="button" id="validateButton" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    Validar Ficheiro
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Importar Ficheiro
                </button>
            </div>
        </form>
    </div>

    // Scripts para funcionalidades adicionais
    <script>
        document.getElementById('analyzeButton').addEventListener('click', function() {
            alert('Funcionalidade de Análise em desenvolvimento.');
            sendAjaxRequest("{{ route('asycuda.analyze.post') }}");
        });
        document.getElementById('validateButton').addEventListener('click', function() {
            alert('Funcionalidade de Validação em desenvolvimento.');
            sendAjaxRequest("{{ route('asycuda.validate.post') }}");
        });

        function sendAjaxRequest(url) {
            const formData = new FormData();
            const fileInput = document.getElementById('xmlFile');
            if (fileInput.files.length === 0) {
                alert('Por favor, selecione um ficheiro XML primeiro.');
                return;
            }
            formData.append('xmlFile', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert('Resposta do servidor: ' + JSON.stringify(data));
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao processar o pedido.');
            });
        }
    </script>
</x-app-layout>