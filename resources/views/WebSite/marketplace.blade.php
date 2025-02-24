<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - LogiGate</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <nav class="bg-gray-800 text-white py-4">
        <div class="container mx-auto flex justify-between items-center px-4">
            <div class="flex items-center space-x-4">
                <img src="{{ asset('dist/img/LOGIGATE.png') }}" alt="LogiGate" class="h-12">
                <a href="/" class="text-2xl font-bold">LogiGate</a>
            </div>
            <ul class="hidden md:flex space-x-8">
                <li><a href="/marketplace" class="hover:text-blue-300">Marketplace</a></li>
                <li><a href="/sobre" class="hover:text-blue-300">Sobre</a></li>
                <li><a href="/contato" class="hover:text-blue-300">Contato</a></li>
            </ul>
        </div>
    </nav>

    <!-- Conteúdo do Marketplace -->
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-8">Marketplace</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($produtos as $produto)
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <img src="{{ $produto['imagem'] }}" alt="{{ $produto['nome'] }}" class="w-full h-48 object-cover mb-4">
                    <h2 class="text-xl font-semibold mb-2">{{ $produto['nome'] }}</h2>
                    <p class="text-gray-600 mb-4">{{ $produto['descricao'] }}</p>
                    <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all">
                        Ver Detalhes
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-8">
        <div class="container mx-auto text-center">
            <p>&copy; 2023 LogiGate. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>