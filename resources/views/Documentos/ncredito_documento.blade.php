<x-app-layout>
<form action="{{ route('documentos.update', $documento) }}" method="POST">
    @csrf
    @method('PUT')
    <!-- Motivo de devolução -->
    <div class="container">
        <div class="row">
            <div class="col-md-4">
            <div class="form-group">
        <label for="motivo_devolucao">Motivo de Devolução</label>
        <textarea class="form-control" id="motivo_devolucao" name="motivo_devolucao" rows="3" required></textarea>
    </div>

    <!-- Seleção de método de devolução de valores -->
    <div class="form-group">
        <label for="metodo_devolucao">Método de Devolução de Valores</label>
        <select class="form-control" id="metodo_devolucao" name="metodo_devolucao" required>
            <option value="dinheiro">Dinheiro</option>
            <option value="transferencia">Transferência</option>
            <option value="conta_corrente">Manter em Conta Corrente</option>
        </select>
    </div>

    <!-- Detalhes da referida fatura -->
    <div class="form-group">
        <label for="detalhes_fatura">Detalhes da Fatura</label>
        <textarea class="form-control" id="detalhes_fatura" name="detalhes_fatura" rows="4" required></textarea>
    </div>
            </div>
            <div class="col-md-8">
                <input type="hidden" name="document_type" id="document_type" value="NC">
                NC Nota de Credito <br>
                {{ $documento->invoice_no}} <br>
                Emitido por : {{$documento->user->name}}
                <hr>
                <!-- Lista de itens da fatura -->
    <div class="form-group">
        <label for="itens_fatura">Itens da Fatura</label>
        <table class="table table-sm table-default">
            <thead>
                <tr>
                    <th></th>
                    <th>Descrição do Item</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Valor Total</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aqui você pode iterar sobre os itens da fatura -->
                @foreach($documento->salesitem as $item)
                <tr>
                    <td>
                        <a href="#" onclick="return confirmDelete()"> <i class="fas fa-trash" style="color: red;"></i></a>
                    </td>
                    <td>{{ $item->produto->ProductDescription }}</td>
                    <td><input type="number" class="form-control" name="quantidade[]" onchange="recalculate()" value="{{ $item->quantity }}"></td>
                    <td><input type="number" class="form-control" name="preco_unitario[]" onchange="recalculate()" value="{{ $item->unit_price }}"></td>
                    <td>{{ $item->quantity * $item->unit_price }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <input type="hidden" id="total">
    </div>
            </div>
        </div>
    </div>

    <!-- Botão para adicionar mais itens -->
    <button class="btn btn-primary" id="add_item">Adicionar Item</button>

    <!-- Botão de submissão -->
    <button type="submit" class="btn btn-success">Submeter Nota de Crédito</button>
</form>
<script>
    function confirmDelete() {
        if (confirm("Tem certeza de que deseja remover este item?")) {
            // Se confirmado, execute a ação de remoção do item
            // Código para remover o item da fatura aqui...
        } else {
            // Se cancelado, não faça nada
            return false;
        }
    }

    function recalculate() {
        var quantity = parseFloat(document.getElementById('quantity').value);
        var unitPrice = parseFloat(document.getElementById('unitPrice').value);
        var total = quantity * unitPrice;
        document.getElementById('total').value = total.toFixed(2); // Arredonde para 2 casas decimais
    }
</script>

</x-app-layout>