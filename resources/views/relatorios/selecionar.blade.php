<x-app-layout>
<div class="container">
    <h1>Gerar Relatório de Licenciamento</h1>

    <form method="GET" id="relatorio-form" action="{{ route('relatorio.visualizar', ['tipo' => request('tipo', 'cliente')]) }}" class="form-inline">
    <!-- Select para tipo de relatório -->
    <div class="form-group mr-3">
        <label for="tipo">Tipo de Relatório:</label>
        <select name="tipo" id="tipo" class="form-control ml-2" onchange="updateFormAction()">
            <option value="cliente" {{ request('tipo') == 'cliente' ? 'selected' : '' }}>Por Cliente</option>
            <option value="tipo" {{ request('tipo') == 'tipo' ? 'selected' : '' }}>Por Tipo de Licenciamento</option>
            <option value="periodo" {{ request('tipo') == 'periodo' ? 'selected' : '' }}>Por Período</option>
            <option value="localidade" {{ request('tipo') == 'localidade' ? 'selected' : '' }}>Por Localidade</option>
        </select>
    </div>

    <!-- Campos de data -->
    <div class="form-group mr-3">
        <label for="data_inicio">Data de Início:</label>
        <input type="date" name="data_inicio" id="data_inicio" class="form-control ml-2" value="{{ request('data_inicio') }}">
    </div>

    <div class="form-group mr-3">
        <label for="data_fim">Data de Fim:</label>
        <input type="date" name="data_fim" id="data_fim" class="form-control ml-2" value="{{ request('data_fim') }}">
    </div>

    <button type="submit" class="btn btn-primary">Gerar Relatório</button>
</form>

<script>
    function updateFormAction() {
        const tipo = document.getElementById('tipo').value;
        const form = document.getElementById('relatorio-form');
        form.action = `/relatorios/licenciamento/${tipo}`;
    }
</script>

</div>
</x-app-layout>