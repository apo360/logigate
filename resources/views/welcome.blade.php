<!DOCTYPE html>
<h lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Canonical SEO -->
  <link rel="canonical" href="https://www.logigate.ao"/>

  <!-- Meta Tags -->
  <meta name="keywords" content="logigate, sistema de gestão aduaneira, gestão financeira, gestão contabilística, automação aduaneira, hongayetu lda, software aduaneiro, controle logístico, contabilidade aduaneira, despacho aduaneiro, gestão de operações, Angola, África">
  <meta name="description" content="Logigate: Solução completa para gestão aduaneira, financeira e contabilística. Automatize processos, reduza custos e aumente a eficiência dos seus despachos com a Hongayetu Lda.">

  <!-- Schema.org markup -->
  <meta itemprop="name" content="Logigate - Gestão Aduaneira, Financeira e Contabilística">
  <meta itemprop="description" content="Logigate oferece uma solução robusta e integrada para automação e controle de processos aduaneiros, financeiros e contabilísticos, garantindo eficiência e precisão.">
  <meta itemprop="image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg">
  <meta itemprop="datePublished" content="2023-10-01">
  <meta itemprop="ratingValue" content="4.9">
  <meta itemprop="reviewCount" content="150">

  <!-- Twitter Card data -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@hongayetu">
  <meta name="twitter:title" content="Logigate - Sistema de Gestão Aduaneira, Financeira e Contabilística">
  <meta name="twitter:description" content="Aumente a eficiência dos seus processos com o Logigate, desenvolvido pela Hongayetu Lda. Automatize despachos e gestão financeira com uma plataforma avançada. #Logística #Angola">
  <meta name="twitter:creator" content="@hongayetu">
  <meta name="twitter:image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg">
  <meta name="twitter:image:alt" content="Logigate - Sistema de Gestão Aduaneira">

  <!-- Open Graph data -->
  <meta property="og:title" content="Logigate | Sistema de Gestão Aduaneira e Financeira" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://www.logigate.ao" />
  <meta property="og:image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="628" />
  <meta property="og:description" content="Logigate é uma solução desenvolvida pela Hongayetu Lda para gestão aduaneira, financeira e contabilística, garantindo automação e eficiência nos processos de despacho e controle financeiro." />
  <meta property="og:site_name" content="Logigate" />
  <meta property="og:locale" content="pt_AO" />
  <meta property="og:updated_time" content="2023-10-01T00:00:00+01:00" />

  <!-- Favicon-->
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}" />
  <title>Logigate - Gestão Aduaneira Simplificada</title>
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
   <!-- Particles.js -->
   <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <!-- Estilos Personalizados -->
  <style>
    /* Efeito de vidro nos botões */
    .glass-button {
      background: rgba(255, 255, 255, 0.1); /* Fundo semi-transparente */
      backdrop-filter: blur(10px); /* Efeito de desfoque */
      border: 1px solid rgba(255, 255, 255, 0.2); /* Borda sutil */
      border-radius: 8px; /* Bordas arredondadas */
      padding: 12px 24px;
      color: white;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .glass-button:hover {
      background: rgba(255, 255, 255, 0.2); /* Fundo mais claro no hover */
      transform: scale(1.05); /* Efeito de zoom */
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra suave */
    }

    /* Container do Hero Section */
    .hero-section {
      position: relative;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: white;
      overflow: hidden;
    }

    /* Fundo animado com particles.js */
    #particles-js {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }

    /* Imagem no Hero Section */
    .hero-image {
      max-width: 400px;
      margin: 0 auto 2rem;
    }
  </style>
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
<body class="bg-gray-900">

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
            <i class="fas fa-info-circle" aria-hidden="true"></i> <!-- Ícone do Font Awesome -->
            <span>Sobre</span>
          </a>
        </li>
        <li>
          <a href="#servicos" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-cogs" aria-hidden="true"></i> <!-- Ícone do Font Awesome -->
            <span>Funcionalidades</span>
          </a>
        </li>
        <li>
          <a href="{{ route('marketplace') }}" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-truck" aria-hidden="true"></i> <!-- Ícone do Font Awesome -->
            <span>Marketplace</span>
          </a>
        </li>
        <li>
          <a href="{{ route('consultar.licenciamento') }}" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-comments" aria-hidden="true"></i> <!-- Ícone do Font Awesome -->
            <span>Consultar</span>
          </a>
        </li>
        <li>
          <a href="#noticias" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-blog" aria-hidden="true"></i> <!-- Ícone do Font Awesome -->
            <span>Notícias</span>
          </a>
        </li>
        <li>
          <a href="#contactos" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-envelope" aria-hidden="true"></i> <!-- Ícone do Font Awesome -->
            <span>Contacto</span>
          </a>
        </li>
        <li>
          @if (Route::has('login'))
              @auth
                <a href="{{ url('/dashboard') }}" class="flex items-center space-x-2">
                  <i class="fas fa-home"></i>
                  <span>Pagina Inicial</span>
                </a>
              @else
                  <li>
                    <a href="{{ route('login') }}" class="login-button flex items-center space-x-2 bg-blue-600 px-4 py-2 rounded-lg hover:bg-blue-700 transition-all">
                      <i class="fas fa-sign-in-alt"></i>
                      <span>Acesso</span>
                    </a>
                  </li>
              @endauth
          @endif
        </li>
      </ul>
    </div>
  </nav>

  <!-- Botão Flutuante -->
  <div class="floating-button" onclick="toggleMenu()">
    <!-- Ícone do menu hambúrguer via CDN -->
    <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/icons/list.svg" alt="Menu" class="menu-icon">
  </div>

  <!-- Menu Circular -->
  <div class="circular-menu" id="circularMenu">
    <a href="#home" class="menu-item" style="transform: translateY(-80px);">Home</a>
    <a href="#sobre" class="menu-item" style="transform: translateX(-60px) translateY(-60px);">Sobre</a>
    <a href="#servicos" class="menu-item" style="transform: translateX(-80px);">Funcionalidades</a>
    <a href="#contacto" class="menu-item" style="transform: translateX(-60px) translateY(60px);">Contato</a>
    <a href="{{ route('login') }}" class="menu-item" style="transform: translateY(80px);">Login</a>
  </div>

  <!-- Hero Section -->
  <section class="hero-section">
    <!-- Fundo animado com particles.js -->
    <div id="particles-js"></div>
    <!-- Conteúdo do Hero -->
    <div class="relative z-10">
      <!-- Imagem temática com animação de fade-up -->
      <img 
        src="https://via.placeholder.com/400x300.png?text=Ilustração+Aduaneira" 
        alt="Gestão Aduaneira" 
        class="hero-image" 
        data-aos="fade-up" 
        data-aos-duration="800"
      >
      <!-- Título com animação de fade-up e delay -->
      <h3 
        class="text-5xl font-bold mb-4" 
        data-aos="fade-up" 
        data-aos-delay="200" 
        data-aos-duration="800"
      >
        Automatizando a Gestão Aduaneira
      </h3>
      <!-- Texto com animação de fade-up e delay -->
      <p 
        class="text-lg mb-8" 
        data-aos="fade-up" 
        data-aos-delay="400" 
        data-aos-duration="800"
      >
        Soluções inteligentes para importação, exportação e logística portuária.
      </p>
      <!-- Botões com animação de fade-up e delay -->
      <div 
        class="space-x-4" 
        data-aos="fade-up" 
        data-aos-delay="600" 
        data-aos-duration="800"
      >
        <button class="glass-button">Saiba Mais</button>
        <button class="glass-button">
          <a href="{{ route('register') }}">Experimente Grátis</a>
        </button>
        <button class="glass-button">
          <a href="#planos">Pacotes</a>
        </button>
      </div>
    </div>

    <!-- Rodapé do Hero Section -->
    <div class="absolute bottom-0 w-full py-6 bg-blue-900 bg-opacity-30">
      <div class="container mx-auto flex flex-wrap justify-center gap-8 text-sm">
        <div 
          class="absolute inset-0 bg-cover bg-center opacity-30" 
          style="background-image: url('https://source.unsplash.com/1600x900/?cargo,shipping')"
        ></div>
        <!-- Grid de estatísticas com animação de fade-up e delay -->
        <div 
          class="relative z-10 w-full max-w-4xl grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 p-6"
          data-aos="fade-up" 
          data-aos-delay="800" 
          data-aos-duration="800"
        >
          <!-- Estatística 1 -->
          <div 
            class="bg-white/10 backdrop-blur-md shadow-lg p-6 rounded-2xl border border-white/30 text-white text-center transition-transform transform hover:scale-105 hover:border-blue-400 hover:shadow-blue-500/50"
            data-aos="zoom-in" 
            data-aos-delay="1000" 
            data-aos-duration="800"
          >
            <span class="text-4xl font-extrabold text-blue-300">24-72h</span>
            <p class="text-base mt-2 text-gray-200">Tempo médio dos Processos</p>
          </div>
          <!-- Estatística 2 -->
          <div 
            class="bg-white/10 backdrop-blur-md shadow-lg p-6 rounded-2xl border border-white/30 text-white text-center transition-transform transform hover:scale-105 hover:border-green-400 hover:shadow-green-500/50"
            data-aos="zoom-in" 
            data-aos-delay="1200" 
            data-aos-duration="800"
          >
            <span class="text-4xl font-extrabold text-green-300">10.000+</span>
            <p class="text-base mt-2 text-gray-200">Processos este Ano</p>
          </div>
          <!-- Estatística 3 -->
          <div 
            class="bg-white/10 backdrop-blur-md shadow-lg p-6 rounded-2xl border border-white/30 text-white text-center transition-transform transform hover:scale-105 hover:border-yellow-400 hover:shadow-yellow-500/50"
            data-aos="zoom-in" 
            data-aos-delay="1400" 
            data-aos-duration="800"
          >
            <span class="text-4xl font-extrabold text-yellow-300">Kz 5M+</span>
            <p class="text-base mt-2 text-gray-200">Mercadorias processadas</p>
          </div>
          <!-- Estatística 4 -->
          <div 
            class="bg-white/10 backdrop-blur-md shadow-lg p-6 rounded-2xl border border-white/30 text-white text-center transition-transform transform hover:scale-105 hover:border-red-400 hover:shadow-red-500/50"
            data-aos="zoom-in" 
            data-aos-delay="1600" 
            data-aos-duration="800"
          >
            <span class="text-4xl font-extrabold text-red-300">98%</span>
            <p class="text-base mt-2 text-gray-200">Cargas disponibilizadas no prazo</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <svg class="wave-bottom" viewBox="0 95 1430 225">
    <path fill="#f8f9fa" fill-opacity="0.9" d="M0,224L1440,96L1440,320L0,320Z"></path>
  </svg>

  <!-- Seção Sobre -->
  <section id="sobre" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        <div data-aos="fade-right">
          <h2 class="text-3xl font-bold text-blue-900 mb-4">Sobre a Logigate</h2>
          <p class="text-gray-700 mb-4 leading-relaxed">
            O <strong>Logigate</strong> é uma plataforma inovadora que simplifica e automatiza processos aduaneiros, proporcionando eficiência e segurança para despachantes aduaneiros e transitários.
          </p>
          <p class="text-gray-700 mb-4 leading-relaxed">
            <strong>Logigate</strong> robusto e versátil, criado para resolver os desafios enfrentados pelas empresas e despachantes aduaneiros na gestão de processos. 
            Nossa solução oferece uma gama de módulos integrados que abrangem não apenas a <strong>gestão aduaneira</strong>, mas também áreas essenciais como:
          </p>
          <ul class="text-gray-700 list-disc list-inside mb-4">
            <li><strong>Recursos Humanos</strong></li>
            <li><strong>Finanças</strong></li>
            <li><strong>Contabilidade</strong></li>
            <li><strong>Gestão de Arquivos</strong></li>
          </ul>
          <p class="text-gray-700 leading-relaxed">
            Nossa missão é transformar a logística global com <strong>tecnologia de ponta</strong> e soluções personalizadas.
          </p>
        </div>
        <div data-aos="fade-left" class="relative">
          <img src="https://via.placeholder.com/500x300" alt="Ilustração Logística" class="rounded-lg shadow-md w-full">
          <div class="absolute inset-0 bg-gradient-to-t from-blue-900/60 rounded-lg"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Seção Funcionalidades -->
  <section id="servicos" class="py-16 bg-gray-90">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-blue-900 mb-8 text-center" data-aos="fade-up">Funcionalidades</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Gestão Aduaneira -->
        <div class="glass-card bg-gradient-to-r from-blue-600 to-blue-500 text-white p-6 rounded-lg shadow-md text-center transform transition-all hover:scale-105 hover:shadow-lg" data-aos="fade-up" data-aos-delay="100">
          <i class="fas fa-ship text-4xl mb-4"></i> <!-- Ícone de navio -->
          <h3 class="text-xl font-bold mb-4">Gestão Aduaneira</h3>
          <p class="mb-4">Automatização completa de processos aduaneiros.</p>
          <ul class="text-left mb-4">
            <li class="flex items-center space-x-2 mb-2">
              <i class="fas fa-check-circle text-green-300"></i>
              <span>Redução de custos.</span>
            </li>
            <li class="flex items-center space-x-2 mb-2">
              <i class="fas fa-check-circle text-green-300"></i>
              <span>Processos mais ágeis.</span>
            </li>
          </ul>
          <button class="bg-white text-blue-600 px-6 py-2 rounded-lg hover:bg-blue-50 transition-all">
            Saiba Mais
          </button>
        </div>

        <!-- Despachos Alfandegários -->
        <div class="glass-card bg-gradient-to-r from-green-600 to-green-500 text-white p-6 rounded-lg shadow-md text-center transform transition-all hover:scale-105 hover:shadow-lg" data-aos="fade-up" data-aos-delay="200">
          <i class="fas fa-truck text-4xl mb-4"></i> <!-- Ícone de caminhão -->
          <h3 class="text-xl font-bold mb-4">Despachos Alfandegários</h3>
          <p class="mb-4">Agilidade e segurança na liberação de cargas.</p>
          <ul class="text-left mb-4">
            <li class="flex items-center space-x-2 mb-2">
              <i class="fas fa-check-circle text-green-300"></i>
              <span>Liberação rápida.</span>
            </li>
            <li class="flex items-center space-x-2 mb-2">
              <i class="fas fa-check-circle text-green-300"></i>
              <span>Segurança garantida.</span>
            </li>
          </ul>
          <button class="bg-white text-green-600 px-6 py-2 rounded-lg hover:bg-green-50 transition-all">
            Saiba Mais
          </button>
        </div>

        <!-- Automação de Processos -->
        <div class="glass-card bg-gradient-to-r from-purple-600 to-purple-500 text-white p-6 rounded-lg shadow-md text-center transform transition-all hover:scale-105 hover:shadow-lg" data-aos="fade-up" data-aos-delay="300">
          <i class="fas fa-cogs text-4xl mb-4"></i> <!-- Ícone de engrenagens -->
          <h3 class="text-xl font-bold mb-4">Automação de Processos</h3>
          <p class="mb-4">Redução de custos e erros operacionais.</p>
          <ul class="text-left mb-4">
            <li class="flex items-center space-x-2 mb-2">
              <i class="fas fa-check-circle text-green-300"></i>
              <span>Menos erros humanos.</span>
            </li>
            <li class="flex items-center space-x-2 mb-2">
              <i class="fas fa-check-circle text-green-300"></i>
              <span>Processos otimizados.</span>
            </li>
          </ul>
          <button class="bg-white text-purple-600 px-6 py-2 rounded-lg hover:bg-purple-50 transition-all">
            Saiba Mais
          </button>
        </div>

        <!-- Integração de APIs -->
        <div class="glass-card bg-gradient-to-r from-orange-600 to-orange-500 text-white p-6 rounded-lg shadow-md text-center transform transition-all hover:scale-105 hover:shadow-lg" data-aos="fade-up" data-aos-delay="400">
          <i class="fas fa-network-wired text-4xl mb-4"></i> <!-- Ícone de rede -->
          <h3 class="text-xl font-bold mb-4">Integração de APIs</h3>
          <p class="mb-4">Conectividade com sistemas de terceiros.</p>
          <ul class="text-left mb-4">
            <li class="flex items-center space-x-2 mb-2">
              <i class="fas fa-check-circle text-green-300"></i>
              <span>Integração simplificada.</span>
            </li>
            <li class="flex items-center space-x-2 mb-2">
              <i class="fas fa-check-circle text-green-300"></i>
              <span>Compatibilidade com vários sistemas.</span>
            </li>
          </ul>
          <button class="bg-white text-orange-600 px-6 py-2 rounded-lg hover:bg-orange-50 transition-all">
            Saiba Mais
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Seção Para Transitários -->
  <section id="transitarios" class="py-16 bg-gradient-to-r from-white-900 to-blue-50 text-white">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold mb-8 text-center" data-aos="fade-up">Para Transitários</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        <!-- Imagem com Efeito de Vidro -->
        <div class="relative" data-aos="fade-right">
          <img src="https://via.placeholder.com/500x300.png?text=Ilustração+Transitários" alt="Transitários" class="rounded-lg shadow-md">
          <div class="absolute inset-0 bg-gradient-to-t from-blue-900 opacity-50 rounded-lg"></div>
        </div>

        <!-- Conteúdo -->
        <div data-aos="fade-left">
          <h3 class="text-2xl font-bold mb-4">Transforme sua Operação com a LogiGate</h3>
          <p class="text-lg mb-6">
            Oferecemos soluções personalizadas para otimizar a gestão aduaneira e logística dos transitários.
          </p>
          <ul class="mb-6">
            <li class="mb-4 flex items-center space-x-2">
              <i class="fas fa-check-circle text-green-400"></i>
              <span>Redução de 30% no tempo de despacho.</span>
            </li>
            <li class="mb-4 flex items-center space-x-2">
              <i class="fas fa-check-circle text-green-400"></i>
              <span>Integração com mais de 50 sistemas aduaneiros.</span>
            </li>
            <li class="mb-4 flex items-center space-x-2">
              <i class="fas fa-check-circle text-green-400"></i>
              <span>Suporte 24/7 para sua equipe.</span>
            </li>
            <li class="mb-4 flex items-center space-x-2">
              <i class="fas fa-check-circle text-green-400"></i>
              <span>Relatórios em tempo real para decisões estratégicas.</span>
            </li>
          </ul>
          <div class="space-x-4">
            <button class="glass-button">Saiba Mais</button>
            <button class="glass-button">
              <a href="{{ route('register') }}">Experimente Grátis</a>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Seção Planos -->
  <section id="planos" data-aos="fade-up" class="container mx-auto py-16 bg-gradient-to-r from-white to-blue-50 relative overflow-hidden">
    <!-- Formas no Background -->
    <div class="absolute inset-0 z-0">
      <!-- Navio -->
      <div class="absolute -left-20 -bottom-20 w-64 h-64 opacity-20">
        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M32 0L0 32L32 64L64 32L32 0Z" fill="#1E88E5"/>
        </svg>
      </div>
      <!-- Avião -->
      <div class="absolute -right-20 -top-20 w-64 h-64 opacity-20">
        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M32 0L0 32L32 64L64 32L32 0Z" fill="#1E88E5"/>
        </svg>
      </div>
      <!-- Caminhão -->
      <div class="absolute -left-40 top-1/4 w-64 h-64 opacity-20">
        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M32 0L0 32L32 64L64 32L32 0Z" fill="#1E88E5"/>
        </svg>
      </div>
      <!-- Contêiner -->
      <div class="absolute -right-40 bottom-1/4 w-64 h-64 opacity-20">
        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M32 0L0 32L32 64L64 32L32 0Z" fill="#1E88E5"/>
        </svg>
      </div>
    </div>

    <!-- Conteúdo -->
    <div class="container mx-auto px-4 relative z-10">
      <h2 class="text-3xl font-bold text-blue-900 mb-8 text-center">Planos e Preços</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Plano Básico -->
        <div data-aos="zoom-in" data-aos-delay="100" class="bg-white p-6 rounded-lg shadow-md text-center transform transition-all hover:scale-105 hover:shadow-lg border-2 border-black-500">
          <h3 class="text-xl font-bold text-blue-900 mb-4">Básico</h3>
          <p class="text-gray-700 mb-4">Ideal para pequenas empresas.</p>
          <p class="text-4xl font-bold text-blue-900 mb-6">AOA 20.000<span class="text-lg text-gray-500">/mês</span></p>
          <ul class="text-left mb-6">
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Gestão de Licenciamentos.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Relatórios básicos.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Conexão com a Pauta Aduaneira.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Suporte por e-mail.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3 text-gray-400">
              <i class="fas fa-times-circle"></i>
              <span>Extração Ficheiro .txt</span>
            </li>
            <li class="flex items-center space-x-2 mb-3 text-gray-400">
              <i class="fas fa-times-circle"></i>
              <span>Serviço de e-mail.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3 text-gray-400">
              <i class="fas fa-times-circle"></i>
              <span>Integração com sistemas de terceiros.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3 text-gray-400">
              <i class="fas fa-times-circle"></i>
              <span>Gestão de Arquivos</span>
            </li>
            <li class="flex items-center space-x-2 mb-3 text-gray-400">
              <i class="fas fa-times-circle"></i>
              <span>Suporte prioritário 24/7.</span>
            </li>
          </ul>
          <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all w-full">
            Assinar
          </button>
        </div>

        <!-- Plano Profissional (Recomendado) -->
        <div data-aos="zoom-in" data-aos-delay="200" class="bg-white p-6 rounded-lg shadow-md text-center transform transition-all hover:scale-105 hover:shadow-lg border-2 border-blue-500">
          <div class="absolute top-0 right-0 bg-blue-500 text-white px-4 py-1 rounded-bl-lg">
            Mais Popular
          </div>
          <h3 class="text-xl font-bold text-blue-900 mb-4">Profissional</h3>
          <p class="text-gray-700 mb-4">Perfeito para empresas em crescimento.</p>
          <p class="text-4xl font-bold text-blue-900 mb-6">AOA 35.000<span class="text-lg text-gray-500">/mês</span></p>
          <ul class="text-left mb-6">
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Gestão de processos aduaneiros.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Gestão de Licenciamentos.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Relatórios avançados.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Integração com sistemas de terceiros.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Suporte por e-mail e chat.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3 text-gray-400">
              <i class="fas fa-times-circle"></i>
              <span>Suporte 24/7.</span>
            </li>
          </ul>
          <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all w-full">
            Assinar
          </button>
        </div>

        <!-- Plano Empresarial -->
        <div data-aos="zoom-in" data-aos-delay="300" class="bg-white p-6 rounded-lg shadow-md text-center transform transition-all hover:scale-105 hover:shadow-lg border-2 border-red-500">
          <h3 class="text-xl font-bold text-blue-900 mb-4">Empresarial</h3>
          <p class="text-gray-700 mb-4">Solução completa para empresas.</p>
          <p class="text-4xl font-bold text-blue-900 mb-6">AOA 50.000<span class="text-lg text-gray-500">/mês</span></p>
          <ul class="text-left mb-6">
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Gestão de Licenciamentos.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Gestão de processos aduaneiros.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Relatórios avançados e personalizados.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Integração com sistemas de terceiros.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Suporte 24/7 por e-mail, chat e telefone.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Personalização avançada.</span>
            </li>
          </ul>
          <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all w-full">
            Assinar
          </button>
        </div>

        <!-- Plano Empresarial PLUS -->
        <div data-aos="zoom-in" data-aos-delay="300" class="bg-white p-6 rounded-lg shadow-md text-center transform transition-all hover:scale-105 hover:shadow-lg border-2 border-purple-500">
          <h3 class="text-xl font-bold text-blue-900 mb-4">Empresarial Plus</h3>
          <p class="text-gray-700 mb-4">Solução completa para grandes empresas.</p>
          <p class="text-4xl font-bold text-blue-900 mb-6">AOA 70.000<span class="text-lg text-gray-500">/mês</span></p>
          <ul class="text-left mb-6">
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Gestão de Licenciamentos.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Gestão de processos aduaneiros.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Relatórios avançados e personalizados.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Integração com sistemas de terceiros.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Suporte 24/7 por e-mail, chat e telefone.</span>
            </li>
            <li class="flex items-center space-x-2 mb-3">
              <i class="fas fa-check-circle text-green-500"></i>
              <span>Personalização avançada.</span>
            </li>
          </ul>
          <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all w-full">
            Assinar
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Nossos Clientes -->
  <section id="customers" class="relative py-16 bg-gray-50" data-aos="zoom-in">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-3xl font-bold text-blue-900 mb-6">Nossos Clientes</h2>
      <p class="text-gray-700 mb-10">O que nossos clientes dizem sobre nós.</p>

      <!-- Swiper Container -->
      <div class="swiper mySwiper">
        <div class="swiper-wrapper">
          <!-- Cliente 1 -->
          <div class="swiper-slide bg-white shadow-lg p-6 rounded-xl flex space-x-4 items-center">
            <div class="w-16 h-16 bg-blue-100 flex justify-center items-center rounded-full">
              <span class="text-blue-700 text-2xl font-bold">C1</span>
            </div>
            <div class="text-left">
              <p class="text-gray-700 italic">"A Logigate revolucionou nossa operação. Agora tudo é mais rápido e eficiente!"</p>
              <p class="text-blue-900 font-semibold mt-2">Carlos Silva</p>
              <p class="text-gray-500 text-sm">Gerente de Logística</p>
            </div>
          </div>

          <!-- Cliente 2 -->
          <div class="swiper-slide bg-white shadow-lg p-6 rounded-xl flex space-x-4 items-center">
            <div class="w-16 h-16 bg-green-100 flex justify-center items-center rounded-full">
              <span class="text-green-700 text-2xl font-bold">C2</span>
            </div>
            <div class="text-left">
              <p class="text-gray-700 italic">"A tecnologia da Logigate nos deu total controle sobre os processos aduaneiros."</p>
              <p class="text-blue-900 font-semibold mt-2">Ana Pereira</p>
              <p class="text-gray-500 text-sm">Diretora de Importação</p>
            </div>
          </div>

          <!-- Cliente 3 -->
          <div class="swiper-slide bg-white shadow-lg p-6 rounded-xl flex space-x-4 items-center">
            <div class="w-16 h-16 bg-red-100 flex justify-center items-center rounded-full">
              <span class="text-red-700 text-2xl font-bold">C3</span>
            </div>
            <div class="text-left">
              <p class="text-gray-700 italic">"Uma solução robusta e confiável, essencial para nossa empresa."</p>
              <p class="text-blue-900 font-semibold mt-2">Fernando Costa</p>
              <p class="text-gray-500 text-sm">CEO - TransLog</p>
            </div>
          </div>
        </div>

        <!-- Controles -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </section>

  <!-- Notícias -->
  <section id="noticias" class="bg-blue-50 py-16" data-aos="zoom-in" data-aos-duration="1000" data-aos-easing="ease-in-out">
    <div class="container mx-auto px-4">
      <h2 class="text-3xl font-bold text-blue-900 mb-8 text-center">Notícias</h2>

      <!-- Carrossel de Destaques -->
      <div class="relative w-full max-w-4xl mx-auto overflow-hidden rounded-lg shadow-md">
        <div x-data="{ active: 0, noticias: [
            { title: 'Nova Regulamentação Aduaneira', desc: 'Confira as novas regras para importação e exportação.', img: 'https://via.placeholder.com/800x400', date: '15/02/2025', slug: 'nova-regulamentacao' },
            { title: 'Dicas para Despachantes', desc: 'Como agilizar processos e evitar multas.', img: 'https://via.placeholder.com/800x400', date: '10/02/2025', slug: 'dicas-despachantes' },
            { title: 'Atualização no Sistema Logigate', desc: 'Melhorias e novas funcionalidades no sistema.', img: 'https://via.placeholder.com/800x400', date: '05/02/2025', slug: 'atualizacao-sistema' }
          ] }">
          
          <div class="relative">
            <template x-for="(noticia, index) in noticias" :key="index">
              <div x-show="active === index" class="absolute inset-0 transition-opacity duration-500 animate-fadeIn">
                <a :href="`/noticias/${noticia.slug}`">
                  <img :src="noticia.img" alt="" class="w-full h-64 object-cover rounded-lg">
                  <div class="absolute inset-0 bg-gradient-to-b from-black/40 to-black/70 rounded-lg"></div>
                  <div class="absolute bottom-4 left-4 text-white">
                    <h3 class="text-lg font-bold" x-text="noticia.title"></h3>
                    <p class="text-sm" x-text="noticia.desc"></p>
                    <span class="text-xs text-gray-300" x-text="noticia.date"></span>
                  </div>
                </a>
              </div>
            </template>
          </div>

          <!-- Controles -->
          <div class="absolute inset-0 flex items-center justify-between p-4">
            <button @click="active = active > 0 ? active - 1 : noticias.length - 1" aria-label="Slide Anterior" class="text-white bg-black/50 p-2 rounded-full">❮</button>
            <button @click="active = active < noticias.length - 1 ? active + 1 : 0" aria-label="Próximo Slide" class="text-white bg-black/50 p-2 rounded-full">❯</button>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="mt-8 flex justify-center space-x-4">
        <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700" @click="categoria = 'todas'">Todas</button>
        <button class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-400" @click="categoria = 'legislacao'">Legislação</button>
        <button class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-400" @click="categoria = 'dicas'">Dicas</button>
        <button class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-400" @click="categoria = 'atualizacoes'">Atualizações</button>
      </div>

      <!-- Lista de Notícias -->
      <div class="mt-8 grid md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{ categoria: 'todas' }">
        <template x-for="noticia in [
            { title: 'Nova Regulamentação Aduaneira', desc: 'Novas regras para importação.', date: '15/02/2025', category: 'legislacao', slug: 'nova-regulamentacao' },
            { title: 'Dicas para Despachantes', desc: 'Agilize seus processos!', date: '10/02/2025', category: 'dicas', slug: 'dicas-despachantes' },
            { title: 'Atualização no Sistema', desc: 'Novas funcionalidades.', date: '05/02/2025', category: 'atualizacoes', slug: 'atualizacao-sistema' },
            { title: 'Taxas Aduaneiras', desc: 'Mudanças no ICMS.', date: '01/02/2025', category: 'legislacao', slug: 'taxas-aduaneiras' }
          ]" :key="noticia.title">
          
          <div x-show="categoria === 'todas' || categoria === noticia.category" class="p-4 bg-white shadow rounded-lg">
            <a :href="`/noticias/${noticia.slug}`" class="block">
              <h3 class="text-lg font-semibold text-blue-900" x-text="noticia.title"></h3>
              <p class="text-gray-700 text-sm mt-2" x-text="noticia.desc"></p>
              <span class="text-xs text-gray-500" x-text="noticia.date"></span>
            </a>
          </div>
        </template>
      </div>
    </div>
  </section>

  <!-- Rodapé com Notícias Rolantes -->
  <footer class="bg-blue-900 text-white py-4">
    <div class="container mx-auto px-4">
      <div class="overflow-hidden whitespace-nowrap">
        <div class="inline-block animate-marquee">
          <span class="mx-4">Destaque 1: Nova Regulamentação Aduaneira</span>
          <span class="mx-4">Destaque 2: Dicas para Despachantes</span>
          <span class="mx-4">Destaque 3: Atualização no Sistema Logigate</span>
        </div>
      </div>
    </div>
  </footer>

  <style>
    @keyframes marquee {
      0% { transform: translateX(100%); }
      100% { transform: translateX(-100%); }
    }
    .animate-marquee {
      animation: marquee 20s linear infinite;
    }
  </style>

  <!-- Perguntas Frequentes -->
  <section id="faq" class="relative container mx-auto py-16 px-4 overflow-hidden">
    <!-- Shapes no fundo -->
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-10 left-1/4 w-40 h-40 bg-blue-100 rounded-full opacity-50"></div>
      <div class="absolute bottom-10 right-1/4 w-56 h-56 bg-blue-200 rounded-full opacity-50"></div>
      <div class="absolute top-20 right-10 w-20 h-20 bg-blue-300 rounded-full opacity-40"></div>
    </div>

    <h2 class="text-3xl font-bold text-blue-900 mb-8 text-center relative z-10">Perguntas Frequentes</h2>

    <div class="max-w-2xl mx-auto space-y-4 relative z-10">
      <!-- Pergunta 1 -->
      <div x-data="{ open: false }" class="border border-gray-200 rounded-lg shadow-sm bg-white">
        <button @click="open = !open" class="w-full text-left p-4 flex justify-between items-center">
          <span class="font-semibold text-blue-900">O Logigate atende às normas aduaneiras locais?</span>
          <svg x-show="!open" class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
          </svg>
          <svg x-show="open" class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"></path>
          </svg>
        </button>
        <div x-show="open" class="p-4 text-gray-700">
          Sim! O Logigate é desenvolvido para estar sempre atualizado conforme as regulamentações locais e internacionais de comércio exterior.
        </div>
      </div>

      <!-- Pergunta 2 -->
      <div x-data="{ open: false }" class="border border-gray-200 rounded-lg shadow-sm bg-white">
        <button @click="open = !open" class="w-full text-left p-4 flex justify-between items-center">
          <span class="font-semibold text-blue-900">Como posso acessar o Logigate?</span>
          <svg x-show="!open" class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
          </svg>
          <svg x-show="open" class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"></path>
          </svg>
        </button>
        <div x-show="open" class="p-4 text-gray-700">
          O Logigate pode ser acessado de qualquer dispositivo conectado à internet, através do nosso portal web seguro.
        </div>
      </div>

      <!-- Pergunta 3 -->
      <div x-data="{ open: false }" class="border border-gray-200 rounded-lg shadow-sm bg-white">
        <button @click="open = !open" class="w-full text-left p-4 flex justify-between items-center">
          <span class="font-semibold text-blue-900">Existe suporte para integração com outros sistemas?</span>
          <svg x-show="!open" class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
          </svg>
          <svg x-show="open" class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"></path>
          </svg>
        </button>
        <div x-show="open" class="p-4 text-gray-700">
          Sim! O Logigate oferece APIs abertas para integração com ERPs, CRMs e outros sistemas corporativos.
        </div>
      </div>
    </div>
  </section>

  <!-- Contactos -->
  <section id="contactos" class="bg-gray-50 py-16">
    <div class="container mx-auto px-4">
      <!-- Título com animação de fade-up -->
      <h2 
        class="text-3xl font-bold text-blue-900 mb-8 text-center" 
        data-aos="fade-up" 
        data-aos-duration="800"
      >
        Contactos
      </h2>

      <div class="grid md:grid-cols-2 gap-8">
        <!-- Informações de Contato com animação de fade-left -->
        <div 
          class="bg-white p-6 rounded-lg shadow-md" 
          data-aos="fade-left" 
          data-aos-duration="800"
        >
          <h3 class="text-xl font-semibold text-blue-900 mb-4">Informações de Contato</h3>
          <ul class="space-y-4">
            <li class="flex items-center">
              <i class="fas fa-map-marker-alt text-blue-500 mr-3"></i>
              <span>Rua Amilcar Cabral nº 66 Luanda, Ingombota</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-phone-alt text-blue-500 mr-3"></i>
              <span><a href="tel:+244948242262"> +244 948 242 262</a></span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-envelope text-blue-500 mr-3"></i>
              <span><a href="mailto:geral@hongayetu.com">geral@hongayetu.com</a></span>
            </li>
          </ul>

          <!-- Redes Sociais com animação de fade-up -->
          <div 
            class="mt-6" 
            data-aos="fade-up" 
            data-aos-delay="200" 
            data-aos-duration="800"
          >
            <h4 class="text-lg font-semibold text-blue-900 mb-3">Siga-nos</h4>
            <div class="flex space-x-4">
              <a href="#" class="text-gray-600 hover:text-blue-500">
                <i class="fab fa-facebook-f text-xl"></i>
              </a>
              <a href="#" class="text-gray-600 hover:text-blue-500">
                <i class="fab fa-twitter text-xl"></i>
              </a>
              <a href="#" class="text-gray-600 hover:text-blue-500">
                <i class="fab fa-linkedin-in text-xl"></i>
              </a>
              <a href="#" class="text-gray-600 hover:text-blue-500">
                <i class="fab fa-instagram text-xl"></i>
              </a>
            </div>
          </div>

          <!-- Mapa com animação de fade-up -->
          <div 
            class="mt-6" 
            data-aos="fade-up" 
            data-aos-delay="400" 
            data-aos-duration="800"
          >
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13261.338516925976!2d13.227187731542303!3d-8.821766782606543!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x5f4c7b5ee3c3402d!2sHONGAYETU%2C+LDA!5e0!3m2!1spt-PT!2sao!4v1529436113669" 
            frameborder="0" style="border:0; width: 100%; height: 200px;" allowfullscreen="" loading="lazy"></iframe>
          </div>
        </div>

        <!-- Formulário de Contato com animação de fade-right -->
        <div 
          class="bg-white p-6 rounded-lg shadow-md" 
          data-aos="fade-right" 
          data-aos-duration="800"
        >
          <h3 class="text-xl font-semibold text-blue-900 mb-4">Envie-nos uma Mensagem</h3>
          <form id="contactForm" class="space-y-4">
            <!-- Campo Nome -->
            <div data-aos="fade-up" data-aos-delay="200" data-aos-duration="800">
              <label for="nome" class="block text-sm font-medium text-gray-700">Nome</label>
              <input 
                type="text" 
                id="nome" 
                name="nome" 
                required 
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
            <!-- Campo Email -->
            <div data-aos="fade-up" data-aos-delay="300" data-aos-duration="800">
              <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
              <input 
                type="email" 
                id="email" 
                name="email" 
                required 
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
            </div>
            <!-- Campo Mensagem -->
            <div data-aos="fade-up" data-aos-delay="400" data-aos-duration="800">
              <label for="mensagem" class="block text-sm font-medium text-gray-700">Mensagem</label>
              <textarea 
                id="mensagem" 
                name="mensagem" 
                rows="4" 
                required 
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              ></textarea>
            </div>
            <!-- Botão Enviar -->
            <div data-aos="fade-up" data-aos-delay="500" data-aos-duration="800">
              <button 
                type="submit" 
                class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                Enviar Mensagem
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Rodapé -->
  <footer class="bg-blue-900 text-white py-8">
    <div class="container mx-auto px-4 text-center">
      <p>&copy; 2023 Logigate. Todos os direitos reservados.</p>
    </div>
  </footer>

  <!-- Alpine.js -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x/dist/cdn.min.js" defer></script>

  <!-- Script para Efeito de Scroll -->
  <script>
    const header = document.getElementById('header');

    // Função para verificar o scroll
    function handleScroll() {
      if (window.scrollY > 50) {
        header.classList.add('gradient-bg', 'glass-effect');
        header.classList.remove('bg-transparent');
      } else {
        header.classList.remove('gradient-bg', 'glass-effect');
        header.classList.add('bg-transparent');
      }
    }

    // Adiciona o evento de scroll
    window.addEventListener('scroll', handleScroll);

    // Inicializa o estado do header
    handleScroll();
  </script>

  <!-- Scripts -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <script>
    // Inicializa AOS
    AOS.init({
      duration: 1000,
      once: true,
    });

   
    function toggleMenu() {
      const circularMenu = document.getElementById('circularMenu');
      circularMenu.classList.toggle('active');
    }

    // Fechar o menu ao clicar fora
    document.addEventListener('click', (event) => {
      const circularMenu = document.getElementById('circularMenu');
      const floatingButton = document.querySelector('.floating-button');
      if (!circularMenu.contains(event.target) && !floatingButton.contains(event.target)) {
        circularMenu.classList.remove('active');
      }
    });
  
  </script>

  <!-- Configuração do particles.js (particles.json) -->
  <script>
    // Configuração do particles.js
    particlesJS('particles-js', {
      "particles": {
        "number": {
          "value": 80,
          "density": {
            "enable": true,
            "value_area": 800
          }
        },
        "color": {
          "value": "#1E88E5" /* Azul claro para representar o mar/transporte */
        },
        "shape": {
          "type": "circle",
          "stroke": {
            "width": 0,
            "color": "#000000"
          },
          "polygon": {
            "nb_sides": 5
          }
        },
        "opacity": {
          "value": 0.5,
          "random": false,
          "anim": {
            "enable": false,
            "speed": 1,
            "opacity_min": 0.1,
            "sync": false
          }
        },
        "size": {
          "value": 3,
          "random": true,
          "anim": {
            "enable": false,
            "speed": 40,
            "size_min": 0.1,
            "sync": false
          }
        },
        "line_linked": {
          "enable": true,
          "distance": 150,
          "color": "#ffffff",
          "opacity": 0.4,
          "width": 1
        },
        "move": {
          "enable": true,
          "speed": 6,
          "direction": "none",
          "random": false,
          "straight": false,
          "out_mode": "out",
          "bounce": false,
          "attract": {
            "enable": false,
            "rotateX": 600,
            "rotateY": 1200
          }
        }
      },
      "interactivity": {
        "detect_on": "canvas",
        "events": {
          "onhover": {
            "enable": true,
            "mode": "repulse"
          },
          "onclick": {
            "enable": true,
            "mode": "push"
          },
          "resize": true
        },
        "modes": {
          "repulse": {
            "distance": 100,
            "duration": 0.4
          },
          "push": {
            "particles_nb": 4
          }
        }
      },
      "retina_detect": true
    });
  
  </script>

  <!-- Swiper.js -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    var swiper = new Swiper(".mySwiper", {
      loop: true,
      autoplay: { delay: 5000 },
      pagination: { el: ".swiper-pagination", clickable: true },
      navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
      breakpoints: {
        768: { slidesPerView: 2, spaceBetween: 20 },
        1024: { slidesPerView: 3, spaceBetween: 30 }
      }
    });
  </script>
</body>
</html>