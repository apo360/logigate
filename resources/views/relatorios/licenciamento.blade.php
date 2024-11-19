<x-app-layout>
<div class="container">
    <h2>Relatório: {{ ucfirst($tipo) }}</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                @foreach ($relatorio->first()->toArray() as $key => $value)
                    <th>{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($relatorio as $item)
                <tr>
                    @foreach ($item->toArray() as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
</x-app-layout>