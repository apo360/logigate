<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado</title>
</head>
<body>
<div class="container">
        <h1>Detalhes do Licenciamento</h1>

        <table class="table table-bordered">
            <tr>
                <th>Código do Licenciamento</th>
                <td>{{ $licenciamento->codigo_licenciamento }}</td>
            </tr>
            <tr>
                <th>Cliente</th>
                <td>{{ $licenciamento->cliente->CompanyName }}</td>
            </tr>
            <tr>
                <th>Empresa</th>
                <td>{{ $licenciamento->empresa->Empresa }}</td>
            </tr>
            <tr>
                <th>FOB Total</th>
                <td>{{ number_format($licenciamento->fob_total, 2, ',', '.') }} {{ $licenciamento->moeda }}</td>
            </tr>
            <tr>
                <th>Frete</th>
                <td>{{ number_format($licenciamento->frete, 2, ',', '.') }} {{ $licenciamento->moeda }}</td>
            </tr>
            <tr>
                <th>Seguro</th>
                <td>{{ number_format($licenciamento->seguro, 2, ',', '.') }} {{ $licenciamento->moeda }}</td>
            </tr>
                <th>Peso Bruto</th>
                <td>
                    @if($licenciamento->peso_bruto >= 1000)
                        {{ number_format($licenciamento->peso_bruto / 1000, 2, ',', '.') }} Ton
                    @else
                        {{ number_format($licenciamento->peso_bruto, 2, ',', '.') }} Kg
                    @endif
                </td>
            <tr>
                <th>Mercadorias</th>
                <td>
                    <ul>
                        @if($mercadoriaAgrupadas->count())
                            @foreach($mercadoriaAgrupadas as $mercadoria)
                                <li>{{ $mercadoria->codigo_aduaneiro }} - 
                                {{ $mercadoria->pautaAduaneira ? $mercadoria->pautaAduaneira->descricao : '' }}

                                    - Quantidade: {{ $mercadoria->quantidade_total }}</li>
                            @endforeach
                        @else
                            <li>Mercadoria em Adição</li>
                        @endif
                    </ul>
                </td>
            </tr>
            <tr>
                <th>Situação</th>
                <td>
                    @if ($licenciamento->txt_gerado == 0)
                        Em Processo
                    @else
                        Em licenciamento
                    @endif
                </td>
            </tr>
            <tr>
                <th>Estado da Factura</th>
                <td>{{ $licenciamento->status_fatura }}</td>
            </tr>
            <!-- Adicione outros campos conforme necessário -->
        </table>

        <a href="{{ route('consultar.licenciamento') }}" class="btn btn-default">Nova Consulta</a>
    </div>
</body>
</html>