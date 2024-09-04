<!-- resources/views/customer/conta_c.blade.php -->

<x-app-layout>

    <div class="py-12" style="padding: 10px;">
        
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Conta Corrente do Cliente: ') . $cliente->CompanyName }}
            </h2>
        </x-slot>

        <div class="card">
            <div class="card-header">
                <a href="{{ route('conta_corrente.create', $cliente->id) }}" class="btn btn-primary">
                    Adicionar Transação
                </a>
            </div>
            <div class="card-body">
                <h3 id="saldo-label">{{ number_format($saldo, 2, ',', '.') }} AOA</h3>
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Saldo Contabilistico</th>
                            <th>Moeda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transacoes as $transacao)
                            <tr class="{{ $transacao->tipo == 'credito' ? 'credito-row' : 'debito-row' }}">
                                <td>{{ $transacao->data }}</td>
                                <td>{{ $transacao->descricao }}</td>
                                <td>{{ ucfirst($transacao->tipo) }}</td>
                                <td>{{ number_format($transacao->valor, 2, ',', '.') }} AOA</td>
                                <td>{{ number_format($transacao->saldo_contabilistico, 2, ',', '.') }} AOA</td> 
                                <td>AKZ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .credito-row {
            background-color: #d4edda; /* Verde claro */
        }

        .debito-row {
            background-color: #f8d7da; /* Vermelho claro */
        }

        .saldo-positivo {
            color: blue;
        }

        .saldo-negativo {
            color: red;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var saldo = {{ $saldo }};
            var saldoLabel = $('#saldo-label');

            if (saldo < 0) {
                saldoLabel.addClass('saldo-negativo').removeClass('saldo-positivo');
                saldoLabel.text('Dívida Atual: ' + Math.abs(saldo).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' AOA');
            } else {
                saldoLabel.addClass('saldo-positivo').removeClass('saldo-negativo');
                saldoLabel.text('Saldo Atual: ' + saldo.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' AOA');
            }
        });
    </script>

</x-app-layout>
