<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Licenciamento</title>
    <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans&family=Poppins:wght@500&display=swap" rel="stylesheet">
  <!-- AOS (Animate On Scroll) -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Estilos Personalizados -->
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans&family=Poppins:wght@500&display=swap" rel="stylesheet">
  <!-- Estilos Personalizados -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x/dist/cdn.min.js" defer></script>
    <style>
    body {
      font-family: 'Open Sans', sans-serif;
    }
    h1, h2, h3 {
      font-family: 'Montserrat', sans-serif;
    }
    .menu-hamburguer {
      display: none;
    }

    /* Responsividade: Ocultar em telas maiores, exibir em telas menores *//* Responsividade: Ocultar em telas maiores, exibir em telas menores */
    @media (min-width: 768px) {
      .floating-button {
        display: none; /* Oculta em telas maiores */
      }
    }
    @media (max-width: 767px) {
      .floating-button {
        display: flex; /* Exibe em telas menores */
      }
    }
    .glass-card {
      background: linear-gradient(135deg, rgba(0, 71, 171, 0.8), rgba(128, 196, 255, 0.8)); /* Gradiente azul com transparência */
      backdrop-filter: blur(10px); /* Efeito de desfoque */
      border: 1px solid rgba(255, 255, 255, 0.2); /* Borda sutil */
      border-radius: 12px; /* Bordas arredondadas */
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra suave */
      transition: transform 0.3s ease, box-shadow 0.3s ease; /* Transição suave */
    }
    .glass-card:hover {
      transform: scale(1.05); /* Efeito de zoom no hover */
      box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2); /* Sombra mais intensa no hover */
    }

    /* Botão flutuante */
    .floating-button {
      position: fixed;
      bottom: 3rem;
      right: 1rem;
      width: 50px;
      height: 50px;
      background-color: #0047AB;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      z-index: 1000;
    }
    .floating-button:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
    }

    /* Ícone do menu */
    .menu-icon {
      width: 24px;
      height: 24px;
      fill: white;
    }

    /* Menu circular */
    .circular-menu {
      position: fixed;
      bottom: 3rem;
      right: 1rem;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-color: rgba(0, 71, 171, 0.9);
      backdrop-filter: blur(10px);
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      transform: scale(0);
      transition: transform 0.3s ease;
      z-index: 999;
    }
    .circular-menu.active {
      display: flex;
      transform: scale(1);
    }

    /* Itens do menu */
    .menu-item {
      position: absolute;
      width: 45px;
      height: 45px;
      background-color: black;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #0047AB;
      font-size: 14px;
      text-decoration: none;
      box-shadow: 0 2px 4px rgba(225, 6, 6, 0.1);
      transition: transform 0.3s ease, background-color 0.3s ease;
    }
    .menu-item:hover {
      background-color: #f0f0f0;
      transform: scale(1.1);
    }
  </style>
  <style>
    /* Efeito de vidro */
    .glass-effect {
        background: rgba(255, 255, 255, 0.1); /* Fundo semi-transparente */
        backdrop-filter: blur(10px); /* Efeito de desfoque */
        border-bottom: 1px solid rgba(255, 255, 255, 0.2); /* Borda sutil */
      }

      /* Gradiente para o header */
      .gradient-bg {
        background: linear-gradient(135deg, rgba(0, 71, 171, 0.9), rgba(128, 196, 255, 0.9));
      }

      /* Efeito de sublinhado animado */
      .underline-effect {
        position: relative;
      }
      .underline-effect::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: #1E88E5;
        transition: width 0.3s ease;
      }
      .underline-effect:hover::after {
        width: 100%;
      }

      /* Botão de login com gradiente */
      .login-button {
        background: linear-gradient(135deg, #1E88E5, #0D47A1);
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
      }
      .login-button:hover {
        background: linear-gradient(135deg, #0D47A1, #1E88E5);
        transform: scale(1.05);
      }
  </style>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">

<!-- Botão Flutuante -->
<div class="floating-button" onclick="toggleMenu()">
    <!-- Ícone do menu hambúrguer via CDN -->
    <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/icons/list.svg" alt="Menu" class="menu-icon">
  </div>

  <!-- Menu Circular -->
  <div class="circular-menu" id="circularMenu">
    <a href="#home" class="menu-item" style="transform: translateY(-80px);">Home</a>
    <a href="#sobre" class="menu-item" style="transform: translateX(-60px) translateY(-60px);">Sobre</a>
    <a href="#servicos" class="menu-item" style="transform: translateX(-80px);">Serviços</a>
    <a href="#contato" class="menu-item" style="transform: translateX(-60px) translateY(60px);">Contato</a>
    <a href="#login" class="menu-item" style="transform: translateY(80px);">Login</a>
  </div>

  <!-- Header Fixo -->
  <nav id="header" class="fixed top-0 w-full text-white py-4 z-50 transition-all duration-300">
    <div class="container mx-auto flex justify-between items-center px-4">
      <!-- Logo e Nome da Empresa -->
      <div class="flex items-center space-x-4">
        <img src="{{ asset('dist/img/LOGIGATE.png') }}" alt="LogiGate" style="opacity: .8; max-width: 70px;" class="hidden md:block">
        <a href="#home" class="text-2xl font-bold hidden md:block">LogiGate</a>
      </div>

      <!-- Menu Horizontal -->
      <ul class="hidden md:flex space-x-8 items-center">
        <li>
          <a href="#sobre" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-home"></i> <!-- Ícone do Font Awesome -->
            <span>Pagina Incial</span>
          </a>
        </li>
        <li>
          <a href="#sobre" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-info-circle"></i> <!-- Ícone do Font Awesome -->
            <span>Sobre</span>
          </a>
        </li>
        <li>
          <a href="#servicos" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-cogs"></i> <!-- Ícone do Font Awesome -->
            <span>Serviços</span>
          </a>
        </li>
        <li>
          <a href="#transitarios" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-truck"></i> <!-- Ícone do Font Awesome -->
            <span>Marketplace</span>
          </a>
        </li>
        <li>
          <a href="#noticias" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-blog"></i> <!-- Ícone do Font Awesome -->
            <span>Notícias</span>
          </a>
        </li>
        <li>
          <a href="#contato" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-envelope"></i> <!-- Ícone do Font Awesome -->
            <span>Contacto</span>
          </a>
        </li>
        <li>
          @if (Route::has('login'))
              @auth
                  <li class="nav-item">
                      <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Pagina Inicial</a>
                  </li>
              @else
                  <li>
                      <a href="{{ route('login') }}" class="login-button flex items-center space-x-2 underline-effect">
                        <i class="fas fa-sign-in-alt"></i> <!-- Ícone do Font Awesome -->
                        <span>Acesso</span>
                      </a>
                  </li>
              @endauth
          @endif
        </li>
      </ul>
    </div>
  </nav>

    <div class="bg-white p-8 shadow-md rounded-lg max-w-lg w-full">
        <h1 class="text-2xl font-bold text-blue-900 text-center mb-4">Consulta de Licenciamento</h1>

        <!-- Mensagens de erro -->
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulário de Consulta -->
        <form action="{{ route('resultado.consulta') }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <div class="mb-4">
                <label for="codigo_licenciamento" class="block text-gray-700 font-semibold">Código do Licenciamento:</label>
                <input type="text" name="codigo_licenciamento" id="codigo_licenciamento" class="w-full px-4 py-2 border rounded focus:ring focus:ring-blue-300" placeholder="Insira o código">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 flex items-center justify-center">
                <span x-show="!loading">Consultar</span>
                <span x-show="loading" class="animate-spin border-t-2 border-white border-solid rounded-full h-5 w-5 ml-2"></span>
            </button>
        </form>
    </div>
</body>
</html>
