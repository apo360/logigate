<x-app-layout>

<div class="py-8">
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Arquivos', 'url' => route('arquivos.index')],
        ['name' => 'Pesquisar' , 'url' => '']
    ]" separator="/" />

    {{-- BREADCRUMB REAL --}}
    @php
        $path = request('dir') ? explode('/', request('dir')) : [];
        $breadcrumbLinks = [];
        $partial = '';
        foreach ($path as $segment) {
            $partial .= ($partial === '' ? '' : '/') . $segment;
            $breadcrumbLinks[] = [
                'name' => $segment,
                'url' => route('PastaAbrir', ['dir' => $partial])
            ];
        }
    @endphp

    <x-breadcrumb :items="array_merge([ ['name' => 'Arquivos', 'url' => route('arquivos.index')] ], $breadcrumbLinks)" separator="/" />

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <input type="text" id="search" placeholder="Pesquisar..." class="form-control w-50">
            <div class="d-flex gap-2">

                {{-- UPLOAD NA PASTA ACTUAL --}}
                <a href="{{ route('arquivos.create', ['dir' => request('dir')]) }}" class="btn btn-warning">
                    Upload
                </a>

                {{-- CRIAR PASTA --}}
                <a href="{{ route('PastaAbrir', ['dir' => auth()->user()->empresas()->first()->conta]) }}" class="btn btn-success">
                    Criar Pasta
                </a>

                {{-- DOWNLOAD ZIP --}}
                <button id="download-zip-btn" class="btn btn-primary" disabled>
                    Download ZIP
                </button>

                {{-- APAGAR --}}
                <form action="" method="POST" id="delete-form">
                    @csrf
                    <input type="hidden" name="paths" id="delete-paths">
                    <button type="submit" class="btn btn-danger" disabled id="delete-btn">
                        Excluir
                    </button>
                </form>

            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Tamanho</th>
                        <th>Última Modificação</th>
                        <th>Acções</th>
                    </tr>
                </thead>
                <tbody id="folder-list">
                    @foreach($items as $item)
                        <tr>
                            <td><input type="checkbox" class="folder-checkbox"></td>
                            <td>
                                @if($item['type'] === 'folder')
                                    <a href="{{ route('arquivos.show', ['arquivo' => $item['name']]) }}">
                                        📁 {{ $item['name'] }}
                                    </a>
                                @else
                                    <a href="{{ route('arquivos.show', ['arquivo' => $item['name']]) }}">
                                        📄 {{ $item['name'] }}
                                    </a>
                                @endif
                            </td>
                            <td>{{ $item['type'] }}</td>
                            <td>{{ $item['type'] == 'file' ? $item['size'] . ' bytes' : '-' }}</td>
                            <td>{{ $item['type'] == 'file' ? $item['last_modified'] : '-' }}</td>
                            <td>
                                {{-- RENOMEAR --}}
                                <button class="btn btn-sm btn-info rename-btn"
                                        data-current="{{ $item['name'] }}"
                                        data-path="{{ $item['path'] }}">
                                    Renomear
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#folder-list tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        $('#select-all').on('click', function() {
            $('.folder-checkbox').prop('checked', this.checked);
            toggleActionButtons();
        });

        $('.folder-checkbox').on('change', function() {
            toggleActionButtons();
        });

        function toggleActionButtons() {
            var anyChecked = $('.folder-checkbox:checked').length > 0;
            $('#delete-btn, #download-btn').prop('disabled', !anyChecked);
        }
    });
</script>

</x-app-layout>
