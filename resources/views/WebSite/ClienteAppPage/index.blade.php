<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>Logigate | Área do Cliente</title>
</head>
<body>
    <h1>Área do Cliente</h1>
    <p>Bem-vindo, {{ $customer->CompanyName ?? $portal->username ?? 'Cliente' }}.</p>

    @if(session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <ul>
        <li><a href="{{ route('cliente.portal.licenciamentos.rastreamento') }}">Consultar licenciamento</a></li>
    </ul>

    <form action="{{ route('cliente.portal.logout') }}" method="POST">
        @csrf
        <button type="submit">Sair</button>
    </form>
</body>
</html>
