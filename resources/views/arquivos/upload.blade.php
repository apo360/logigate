<x-app-layout>
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <style>
        .custom-file-upload {
    border: 2px dashed #ddd;
    padding: 20px;
    display: inline-block;
    cursor: pointer;
    text-align: center;
    width: 100%;
    margin-bottom: 20px;
    background-color: #f9f9f9;
}

.upload-container {
    border: 1px solid #ccc;
    padding: 20px;
    margin-top: 20px;
    background-color: #fff;
}

.upload-actions {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
}

    </style>
    <div class="py-12">
        <x-breadcrumb :items="[
            ['name' => 'Dashboard', 'url' => route('dashboard')],
            ['name' => 'Arquivos', 'url' => route('arquivos.index')],
            ['name' => request()->get('dir') ?? 'Raiz', 'url' => '' ],
            ['name' => 'Upload', 'url' => '']
        ]" separator="/" />
        
        <h1>Upload de Arquivos</h1>

        <div class="alert alert-info">
            Adicione os arquivos e pastas que deseja fazer upload para o sistema. Para arquivos maiores que 160GB, contacte o Provedor do Serviço. 
        </div>

        <div class="upload-container">
            <label for="file-upload" class="custom-file-upload">
                Arraste e solte os arquivos e pastas aqui, ou clique para adicionar arquivos ou pastas.
            </label>
            <!-- A conta é o nome da pasta Raiz no S3 -->
            <input type="hidden" name="pasta_raiz" value="{{ request()->get('dir') ?? auth()->user()->empresas()->first()->conta }}"> <!-- A conta é o nome da pasta Raiz no S3 -->
            <input id="file-upload" type="file" multiple style="display: none;" onchange="updateFileList()" />

            <table id="file-list-table" class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Pasta</th>
                        <th>Tipo</th>
                        <th>Tamanho</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Arquivos adicionados serão listados aqui -->
                    <tr>
                        <td colspan="4">Nenhum arquivo ou pasta selecionado.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="upload-actions">
            <button class="btn btn-secondary" onclick="cancelUpload()">Cancelar</button>
            <button class="btn btn-primary" onclick="uploadFiles()">Carregar</button>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function updateFileList() {
            const fileInput = document.getElementById('file-upload');
            const tableBody = document.getElementById('file-list-table').getElementsByTagName('tbody')[0];
            tableBody.innerHTML = '';

            Array.from(fileInput.files).forEach(file => {
                const row = tableBody.insertRow();
                row.insertCell(0).textContent = file.name;
                row.insertCell(1).textContent = file.webkitRelativePath || '/';
                row.insertCell(2).textContent = file.type || 'Arquivo';
                row.insertCell(3).textContent = `${(file.size / 1024).toFixed(2)} KB`;
            });

            if (!fileInput.files.length) {
                const row = tableBody.insertRow();
                row.insertCell(0).colSpan = 4;
                row.insertCell(0).textContent = 'Nenhum arquivo ou pasta selecionado.';
            }
        }

        function cancelUpload() {
            // Limpar a seleção de arquivos
            document.getElementById('file-upload').value = '';
            updateFileList();
        }

        function cancelUpload() {
            $('#file-upload').val('');
            updateFileList();
        }

        function uploadFiles() {
            const formData = new FormData();
            const files = $('#file-upload')[0].files;

            $.each(files, function(i, file) {
                formData.append('files[]', file);
            });

            formData.append('pasta_raiz', $('input[name="pasta_raiz"]').val());

            $.ajax({
                url: '{{ route("arquivos.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert('Arquivos carregados com sucesso!');
                    location.reload();
                },
                error: function(error) {
                    alert('Erro ao carregar arquivos.');
                }
            });
        }

    </script>
</x-app-layout>
