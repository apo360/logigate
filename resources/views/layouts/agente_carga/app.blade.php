<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agente de Carga | Logigate</title>
    <!-- Inclua os estilos e scripts específicos do Agente de Carga -->
    <link href="{{ asset('css/agente_carga.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">
            <img src="{{ asset('dist/img/LOGIGATE.png') }}" alt="Logigate">
        </div>
        <nav>
            <ul>
                <li><a href="{{ route('agente_carga.dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('agente_carga.cargas') }}">Cargas</a></li>
                <li><a href="{{ route('agente_carga.relatorios') }}">Relatórios</a></li>
            </ul>
        </nav>
    </header>

    <!-- Conteúdo Principal -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; {{ date('Y') }} Logigate. Todos os direitos reservados.</p>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/agente_carga.js') }}"></script>
</body>
</html>