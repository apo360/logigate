<x-app-layout>
    <div class="container mt-4">
        <h1 class="mb-4">Dados Extraídos</h1>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Chave</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <!-- Percorre todos os campos dos dados -->
                @foreach ($data as $key => $value)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                        <td>
                            <!-- Verifica se o valor é um array -->
                            @if (is_array($value))
                                <!-- Exibe uma tabela para os valores do array -->
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            @if (isset($value[0]) && is_array($value[0]))
                                                <!-- Se o array contém arrays -->
                                                @foreach (array_keys($value[0]) as $subKey)
                                                    <th>{{ ucfirst($subKey) }}</th>
                                                @endforeach
                                            @else
                                                <!-- Se o array é simples -->
                                                <th>Valor</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($value as $item)
                                            <tr>
                                                @if (is_array($item))
                                                    @foreach ($item as $subValue)
                                                        <td>{{ $subValue }}</td>
                                                    @endforeach
                                                @else
                                                    <td>{{ $item }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <!-- Exibe o valor diretamente se não for array -->
                                {{ $value }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
