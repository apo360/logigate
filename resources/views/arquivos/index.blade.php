<x-app-layout>

<div class="py-12">
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Arquivos', 'url' => route('arquivos.index')],
        ['name' => 'Pesquisar' , 'url' => '']
    ]" separator="/" />

    <h1>Arquivos</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h3>Pastas e Ficheiros</h3>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <input type="text" id="search" placeholder="Pesquisar..." class="form-control mb-3">
                <button id="delete-btn" class="btn btn-danger" disabled>Excluir</button>
                <button id="download-btn" class="btn btn-primary" disabled>Download</button>
                <a href="{{ route('PastaAbrir', ['dir' => auth()->user()->empresas()->first()->conta]) }}" class="btn btn-success">Criar Pasta</a>
                <a href="{{ route('arquivos.create')}}" class="btn btn-warning">Carregar</a>
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
                        <th>Permissões</th>
                    </tr>
                </thead>
                <tbody id="folder-list">
                    @foreach($items as $item)
                        <tr>
                            <td><input type="checkbox" class="folder-checkbox"></td>
                            <td> 
                                <a href="{{ route('arquivos.show', ['arquivo' => $item['name']]) }}">
                                    {{ $item['name'] }}/
                                </a>
                            </td>
                            <td>{{ $item['type'] }}</td>
                            <td>{{ $item['type'] == 'file' ? $item['size'] . ' bytes' : '-' }}</td>
                            <td>{{ $item['type'] == 'file' ? $item['last_modified'] : '-' }}</td>
                            <td>Permitir</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <h3>Excluir Arquivo</h3>
    <form action="" method="POST" id="delete-form">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" disabled id="delete-btn">Excluir Arquivo</button>
    </form>
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
