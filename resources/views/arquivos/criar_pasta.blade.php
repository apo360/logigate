
<x-app-layout>
    <div class="py-12">
        <h3>Criar Nova Pasta</h3>
        <p>
            Use pastas para agrupar objetos nos buckets. Ao criar uma pasta, o S3 cria um objeto usando o nome que você especificar seguido por uma barra (/).
            Este objeto aparece como uma pasta no console.
        </p>

        <form action="{{ route('arquivos.criarPasta') }}" method="POST">
            @csrf
            <input type="hidden" name="pasta_raiz" value="{{ $conta }}">
            <div class="form-group">
                <label for="folder_name">Nome da Pasta</label>
                <input type="text" id="folder_name" name="nome_pasta" class="form-control" required>
                <small class="form-text text-muted">Nomes de pastas não podem conter "/".</small>
            </div>

            <div class="form-group">
                <label>Criptografia do Lado do Servidor</label>
                <div>
                    <input type="radio" id="default_encryption" name="encryption" value="default" checked>
                    <label for="default_encryption">Não especificar uma chave de criptografia</label>
                </div>
                <div>
                    <input type="radio" id="custom_encryption" name="encryption" value="custom">
                    <label for="custom_encryption">Especificar uma chave de criptografia</label>
                    <input type="text" id="encryption_key" name="encryption_key" class="form-control mt-2" placeholder="Chave de criptografia">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Criar Pasta</button>
        </form>
    </div>
</x-app-layout>
