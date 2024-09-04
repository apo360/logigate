<!-- resources/views/conta_corrente/create.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Adicionar Transação para o Cliente: ') . $cliente->CompanyName }}
        </h2>
    </x-slot>

    <div class="py-12" style="padding: 10px;">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('conta_corrente.store', $cliente->id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="valor">Valor</label>
                        <input type="number" name="valor" id="valor" class="form-control" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo de Transação</label>
                        <select name="tipo" id="tipo" class="form-control" required>
                            <option value="credito">Crédito</option>
                            <option value="debito">Débito</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <input type="text" name="descricao" id="descricao" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="data">Data</label>
                        <input type="date" name="data" id="data" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Salvar Transação</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
