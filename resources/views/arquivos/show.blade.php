<!-- arquivos.show.blade.php -->
<x-app-layout>
    <head>
        <!-- Bootstrap CSS -->
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <div class="py-12">
        <x-breadcrumb :items="[
            ['name' => 'Dashboard', 'url' => route('dashboard')],
            ['name' => 'Arquivos', 'url' => route('arquivos.index')],
            ['name' => $arquivo, 'url' => '']
        ]" separator="/" />

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Arquivos e Sub-pastas</h3>
            </div>
            <div class="card-body">
                <form id="actions-form" method="POST" action="{{ route('arquivos.bulkActions') }}">
                    @csrf
                    <table class="table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Nome</th>
                                <th>Tamanho</th>
                                <th>Última Modificação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($files as $file)
                                <tr>
                                    <td><input type="checkbox" name="files[]" value="{{ $file['Key'] }}" class="file-checkbox"></td>
                                    <td>
                                        @if(isset($file['Subfolders']))
                                            <a href="{{ route('arquivos.show', ['dir' => $file['Key']]) }}">
                                                {{ basename($file['Key']) }}/
                                            </a>
                                        @else
                                            {{ basename($file['Key']) }}
                                        @endif
                                    </td>
                                    <td>{{ $file['Size'] }} bytes</td>
                                    <td>{{ \Carbon\Carbon::parse($file['LastModified'])->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($file['Size'] > 0)
                                            <div class="btn-group">
                                                <!-- Botão para visualização -->
                                                <a href="{{ route('arquivos.visualizar', ['key' => urlencode($file['Key'])]) }}" type="button" class="btn btn-sm btn-default" title="Visualizar" target="_blank">
                                                    <i class="fas fa-eye" style="color: cyan;"></i>
                                                </a>

                                                <!-- Botão para exclusão -->
                                                <a href="{{ route('arquivos.destroy', ['arquivo' => $file['Key'] ])}}" type="button" class="btn btn-sm btn-default" title="Excluir" target="_blank">
                                                    <i class="fas fa-trash-alt" style="color: lightcoral;"></i>
                                                </a>

                                                <!-- Botão para compartilhamento com dropdown -->
                                                <div class="btn-group" role="group">
                                                    <div class="btn-group" role="group">
                                                        <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-default dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-share-alt" style="color: darkorange"></i>
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                            <li>
                                                                <a class="dropdown-item" href="whatsapp://send?text=Seu%20link%20aqui" target="_blank">
                                                                    <i class="fab fa-whatsapp"></i> Via WhatsApp
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="mailto:?subject=Assunto&body=Seu%20link%20aqui" target="_blank">
                                                                    <i class="fas fa-envelope"></i> Via E-mail
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="#" id="share-link">
                                                                    <i class="fas fa-link"></i> Copiar Link
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('arquivos.dowload', ['key' => $file['Key'] ])}}">
                                                                    <i class="fas fa-download"></i> Download
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="submit" class="btn btn-outline-danger" name="action" value="delete" disabled>Excluir Selecionados</button>
                            <button type="submit" class="btn btn-outline-primary" name="action" value="move" disabled>Mover Selecionados</button>
                            <button type="submit" class="btn btn-outline-secondary" name="action" value="copy" disabled>Copiar Selecionados</button>
                            <a href="{{ route('PastaAbrir', ['dir' => auth()->user()->empresas()->first()->conta.'/'.$arquivo]) }}" class="btn btn-success">Criar Pasta</a>
                            <a href="{{ route('arquivos.create', ['dir' => auth()->user()->empresas()->first()->conta.'/'.$arquivo])}}" class="btn btn-warning">Carregar Ficheiros</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Marcar todos os checkboxes
        document.getElementById('select-all').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.file-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleActionButtons();
        });

        // Habilitar/Desabilitar botões de ação dependendo dos arquivos selecionados
        document.querySelectorAll('.file-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                toggleActionButtons();
            });
        });

        function toggleActionButtons() {
            const selectedFiles = document.querySelectorAll('.file-checkbox:checked');
            const deleteButton = document.querySelector('button[name="action"][value="delete"]');
            const moveButton = document.querySelector('button[name="action"][value="move"]');
            const copyButton = document.querySelector('button[name="action"][value="copy"]');
            
            if (selectedFiles.length > 0) {
                deleteButton.disabled = false;
                moveButton.disabled = false;
                copyButton.disabled = false;
            } else {
                deleteButton.disabled = true;
                moveButton.disabled = true;
                copyButton.disabled = true;
            }
        }
    </script>

    <script>
        document.getElementById('copy-link').addEventListener('click', function(e) {
            e.preventDefault();
            var link = '{{ $url }}'; // Substitua com a variável que contém o link
            navigator.clipboard.writeText(link).then(function() {
                alert('Link copiado para a área de transferência!');
            }, function(err) {
                alert('Falha ao copiar o link.');
            });
        });
    </script>

</x-app-layout>

