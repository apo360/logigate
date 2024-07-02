<x-app-layout>
    <div class="container">
        <div class="card">
            <div class="card-header">
                Editar Empresa
            </div>
            <div class="card-body">
                <form action="{{ route('empresas.update', $empresa->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <!-- Campos de edição da empresa -->
                    <div class="form-group">
                        <label for="Empresa">Nome da Empresa:</label>
                        <input type="text" class="form-control" id="Empresa" name="Empresa" value="{{ $empresa->Empresa }}">
                    </div>
                    <div class="form-group">
                        <label for="ActividadeComercial">Atividade Comercial:</label>
                        <input type="text" class="form-control" id="ActividadeComercial" name="ActividadeComercial" value="{{ $empresa->ActividadeComercial }}">
                    </div>
                    <div class="form-group">
                        <label for="NIF">NIF:</label>
                        <input type="text" class="form-control" id="NIF" name="NIF" value="{{ $empresa->NIF }}">
                    </div>
                    <!-- Adicione outros campos da empresa conforme necessário -->
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>