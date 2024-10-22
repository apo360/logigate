<x-app-layout>
    <a href="{{ route('avenca.create') }}"> Nova Avença</a>

    <hr>

    @foreach ($clientes as $cliente)
        <h3>{{ $cliente->CompanyName }}</h3>
        <ul>
            @foreach ($cliente->avencas as $avenca)
                <li>{{ $avenca->valor }} - {{ $avenca->periodicidade }}</li>
            @endforeach
        </ul>
    @endforeach
</x-app-layout>