<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans&family=Poppins:wght@500&display=swap" rel="stylesheet">

    <!-- Alpine.js para interatividade -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-4xl">
        <h1 class="text-2xl font-bold text-blue-900 text-center mb-4">Detalhes do Licenciamento</h1>

        <!-- Tabela de Dados -->
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 rounded-lg">
                <tbody>
                    <tr class="border-b">
                        <th class="bg-blue-200 px-4 py-2 text-left">Código do Licenciamento</th>
                        <td class="px-4 py-2">{{ $licenciamento->codigo_licenciamento ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="bg-blue-200 px-4 py-2 text-left">Cliente</th>
                        <td class="px-4 py-2">{{ $licenciamento->cliente->CompanyName ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="bg-blue-200 px-4 py-2 text-left">Empresa</th>
                        <td class="px-4 py-2">{{ $licenciamento->empresa->Empresa ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="bg-blue-200 px-4 py-2 text-left">FOB Total</th>
                        <td class="px-4 py-2">{{ number_format($licenciamento->fob_total, 2, ',', '.') }} {{ $licenciamento->moeda ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="bg-blue-200 px-4 py-2 text-left">Frete</th>
                        <td class="px-4 py-2">{{ number_format($licenciamento->frete, 2, ',', '.') }} {{ $licenciamento->moeda ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="bg-blue-200 px-4 py-2 text-left">Seguro</th>
                        <td class="px-4 py-2">{{ number_format($licenciamento->seguro, 2, ',', '.') }} {{ $licenciamento->moeda ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <th class="bg-blue-200 px-4 py-2 text-left">Peso Bruto</th>
                        <td class="px-4 py-2">
                            @if($licenciamento->peso_bruto >= 1000)
                                {{ number_format($licenciamento->peso_bruto / 1000, 2, ',', '.') }} Ton
                            @else
                                {{ number_format($licenciamento->peso_bruto, 2, ',', '.') }} Kg
                            @endif
                        </td>
                    </tr>
                    <tr class="border-b">
                        <th class="bg-blue-200 px-4 py-2 text-left">Mercadorias</th>
                        <td class="px-4 py-2">
                            <ul class="list-disc pl-5">
                                @if($mercadoriaAgrupadas->count())
                                    @foreach($mercadoriaAgrupadas as $mercadoria)
                                        <li>{{ $mercadoria->codigo_aduaneiro }} - 
                                            {{ $mercadoria->pautaAduaneira ? $mercadoria->pautaAduaneira->descricao : 'N/A' }}
                                            - Quantidade: {{ $mercadoria->quantidade_total }}
                                        </li>
                                    @endforeach
                                @else
                                    <li>Mercadoria em Adição</li>
                                @endif
                            </ul>
                        </td>
                    </tr>
                    <tr class="border-b">
                        <th class="bg-blue-200 px-4 py-2 text-left">Situação</th>
                        <td class="px-4 py-2">
                            @if ($licenciamento->txt_gerado == 0)
                                <span class="text-yellow-600 font-semibold">Em Processo</span>
                            @else
                                <span class="text-green-600 font-semibold">Em Licenciamento</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-blue-200 px-4 py-2 text-left">Estado da Factura</th>
                        <td class="px-4 py-2">{{ $licenciamento->status_fatura ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('consultar.licenciamento') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Nova Consulta</a>
            <button onclick="window.history.back()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">Voltar</button>
        </div>
    </div>

</body>
</html>
