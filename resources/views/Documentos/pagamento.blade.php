<!-- pagamento.blade.php -->
<x-app-layout>
    <div class="container">
        <h1>Realizar Pagamento - Fatura {{$salesInvoice->invoice_no}}</h1>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('documento.efetuarPagamento', ['id' => $salesInvoice->id]) }}" method="post">
            @csrf

            <span>Resumo de Pagamento</span>
            <div>
                <span>Valor a Pagar: {{$salesInvoice->salesdoctotal->gross_total}} </span>
                <input type="hidden" name="valor_pagar" id="valor_pagar" value="{{$salesInvoice->salesdoctotal->gross_total}}">
            </div>
            <hr>
            <!-- Adicione os campos necessários para o pagamento, como valor, método de pagamento, etc. -->
            <div class="form-group">
                <label for="data_pagamento">Data de Pagamento</label>
                <input type="date" name="data_pagamento" id="data_pagamento" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            
            <div class="form-group">
                <label for="moeda_pagamento">Moeda de Pagamento</label>
                <select name="moeda_pagamento" id="moeda_pagamento" class="form-control" required>
                    <option value="kz">Kwanza (kz)</option>
                    <option value="usd">Dólar Americano (USD)</option>
                    <option value="eur">Euro (EUR)</option>
                    <option value="zar">Rand (ZAR)</option>
                    <!-- Adicione mais opções conforme necessário -->
                </select>
            </div>

            <div class="form-group">
                <label for="forma_pagamento">Forma de Pagamento</label>
                <select name="forma_pagamento" id="forma_pagamento" class="form-control" required>
                    @foreach($meios as $meio)
                        <option value="{{$meio->Id}}">{{$meio->Descriptions}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="valor">Valor recebido (kz)</label>
                <input type="number" name="valor" id="valor" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="troco_credito">Troco/Crédito</label>
                <input type="number" name="troco_credito" id="troco_credito" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="restante">Valor Restante será</label>
                <select name="restante" id="restante" class="form-control" required>
                    <option value="credito">Crédito</option>
                    <option value="troco">Troco</option>
                </select>
            </div>
            <!-- Adicione mais campos conforme necessário -->

            <button type="submit" class="btn btn-primary">Realizar Pagamento</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Obtenha os elementos do DOM
            var valorRecebidoInput = document.getElementById('valor');
            var valorPagarInput = document.getElementById('valor_pagar');
            var trocoCreditoInput = document.getElementById('troco_credito');

            // Adicione um evento de input ao campo "Valor Recebido"
            valorRecebidoInput.addEventListener('input', function () {
                // Obtenha os valores dos campos
                var valorRecebido = parseFloat(valorRecebidoInput.value) || 0;
                var valorPagar = parseFloat(valorPagarInput.value) || 0;

                // Calcule o Troco ou Crédito
                var trocoCredito = valorRecebido - valorPagar;

                // Atualize o valor do campo "Troco/Crédito"
                trocoCreditoInput.value = trocoCredito.toFixed(2);
            });
        });
    </script>
</x-app-layout>
