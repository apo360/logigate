<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- SEO Optimized -->
  <title>Logigate | Sistema de Gestão Aduaneira Inteligente - Angola</title>
  <meta name="description" content="Automatize processos aduaneiros, reduza custos e aumente a eficiência com o sistema completo da Logigate. Solução ideal para despachantes e transitários em Angola.">
  <meta name="keywords" content="sistema aduaneiro Angola, gestão alfandegária, despacho aduaneiro, logística Angola, software aduaneiro, Hongayetu LDA">
  
  <!-- Open Graph -->
  <meta property="og:title" content="Logigate - Sistema Completo de Gestão Aduaneira">
  <meta property="og:description" content="Transforme sua operação aduaneira com tecnologia de ponta. Experimente grátis!">
  <meta property="og:image" content="https://aduaneiro.hongayetu.com/images/og-image.jpg">
  <meta property="og:url" content="https://aduaneiro.hongayetu.com">
  <meta property="og:type" content="website">
  
  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Logigate - Gestão Aduaneira Inteligente">
  <meta name="twitter:description" content="Automatize seus processos aduaneiros com eficiência">
  
  <!-- Favicon -->
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  <meta name="theme-color" content="#0047AB">
  
  <!-- Preload Critical Resources -->
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" as="style">
  <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
  
  <!-- CSS -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  
  <style>
    :root {
      --primary: #0047AB;
      --primary-dark: #003580;
      --secondary: #00B4D8;
      --accent: #FF6B35;
      --light: #F8F9FA;
      --dark: #1A1A2E;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      color: #1E293B; /* Melhor contraste */
      overflow-x: hidden;
    }
    
    h1, h2, h3, h4 {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
    }
    
    /* Glass Effect */
    .glass {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .glass-dark {
      background: rgba(26, 26, 46, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* Gradient Text */
    .gradient-text {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    /* Custom Animations */
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }
    
    @keyframes pulse-glow {
      0%, 100% { box-shadow: 0 0 20px rgba(0, 71, 171, 0.3); }
      50% { box-shadow: 0 0 40px rgba(0, 71, 171, 0.6); }
    }
    
    .float-animation {
      animation: float 6s ease-in-out infinite;
    }
    
    .pulse-glow {
      animation: pulse-glow 3s ease-in-out infinite;
    }
    
    /* Custom Button Styles */
    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white;
      padding: 14px 32px;
      border-radius: 12px;
      font-weight: 600;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      border: none;
      cursor: pointer;
      display: inline-block;
      text-align: center;
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0, 71, 171, 0.3);
    }
    
    .btn-primary:focus-visible {
      outline: 3px solid var(--secondary);
      outline-offset: 2px;
    }
    
    .btn-primary:before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s ease;
    }
    
    .btn-primary:hover:before {
      left: 100%;
    }
    
    .btn-outline {
      background: transparent;
      color: var(--primary);
      border: 2px solid var(--primary);
      padding: 12px 30px;
      border-radius: 12px;
      font-weight: 600;
      transition: all 0.3s ease;
      cursor: pointer;
      display: inline-block;
      text-align: center;
    }
    
    .btn-outline:hover {
      background: var(--primary);
      color: white;
      transform: translateY(-2px);
    }
    
    .btn-outline:focus-visible {
      outline: 3px solid var(--secondary);
      outline-offset: 2px;
    }
    
    /* Section Spacing */
    .section {
      padding: 100px 0;
      position: relative;
    }
    
    /* Custom Card */
    .feature-card {
      background: white;
      border-radius: 20px;
      padding: 40px 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      transition: all 0.4s ease;
      height: 100%;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(0, 71, 171, 0.1);
    }
    
    .feature-card:hover {
      transform: translateY(-15px);
      box-shadow: 0 20px 60px rgba(0, 71, 171, 0.15);
    }
    
    .feature-card:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
    }
    
    /* Stats Counter */
    .stat-card {
      background: linear-gradient(135deg, rgba(0, 71, 171, 0.1), rgba(0, 180, 216, 0.1));
      border-radius: 20px;
      padding: 30px;
      text-align: center;
      transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
      transform: scale(1.05);
    }
    
    .counter {
      font-size: 3.5rem;
      font-weight: 700;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      line-height: 1;
    }
    
    /* Testimonial Card */
    .testimonial-card {
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
      position: relative;
    }
    
    .testimonial-card:before {
      content: '"';
      position: absolute;
      top: 20px;
      left: 30px;
      font-size: 80px;
      color: var(--primary);
      opacity: 0.1;
      font-family: serif;
    }
    
    /* Pricing Card */
    .pricing-card {
      background: white;
      border-radius: 25px;
      padding: 40px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      transition: all 0.4s ease;
      position: relative;
      overflow: hidden;
      border: 2px solid transparent;
    }
    
    .pricing-card.popular {
      border-color: var(--primary);
      transform: scale(1.05);
    }
    
    .pricing-card.popular:before {
      content: 'Mais Popular';
      position: absolute;
      top: 20px;
      right: -35px;
      background: var(--accent);
      color: white;
      padding: 8px 40px;
      transform: rotate(45deg);
      font-size: 14px;
      font-weight: 600;
    }
    
    .pricing-card:hover {
      transform: translateY(-15px);
      box-shadow: 0 30px 80px rgba(0, 71, 171, 0.2);
    }
    
    /* Timeline */
    .timeline-item {
      position: relative;
      padding-left: 60px;
      margin-bottom: 40px;
    }
    
    .timeline-item:before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
    }
    
    .timeline-item:nth-child(1):before { content: '1'; }
    .timeline-item:nth-child(2):before { content: '2'; }
    .timeline-item:nth-child(3):before { content: '3'; }
    .timeline-item:nth-child(4):before { content: '4'; }
    
    /* Mobile Menu */
    .mobile-menu {
      transform: translateX(100%);
      transition: transform 0.3s ease;
    }
    
    .mobile-menu.active {
      transform: translateX(0);
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .section {
        padding: 60px 0;
      }
      
      h1 {
        font-size: 2.5rem;
      }
      
      h2 {
        font-size: 2rem;
      }
      
      .pricing-card.popular {
        transform: scale(1);
      }
    }
    
    /* Wave Divider */
    .wave-divider {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      overflow: hidden;
      line-height: 0;
    }
    
    .wave-divider svg {
      position: relative;
      display: block;
      width: calc(100% + 1.3px);
      height: 100px;
    }

    /* Melhor contraste para texto */
    .text-gray-600 {
      color: #475569 !important;
    }

    /* Skip to content link - acessibilidade */
    .skip-link {
      position: absolute;
      left: -9999px;
      top: 0;
      background: var(--primary);
      color: white;
      padding: 1rem;
      text-decoration: none;
      z-index: 9999;
    }
    
    .skip-link:focus {
      left: 0;
    }
  </style>
</head>
<body class="bg-gray-50">
  <!-- Skip to content link - acessibilidade -->
  <a href="#main-content" class="skip-link">Saltar para o conteúdo principal</a>
  
  <!-- Navigation -->
  <nav class="fixed w-full z-50 transition-all duration-300 bg-white/90 backdrop-blur-md" aria-label="Navegação principal">
    <div class="container mx-auto px-4 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
          <div class="group">
            <img 
              src="{{ asset('dist/img/LOGIGATE.png') }}" 
              alt="LogiGate - Sistema Aduaneiro Inteligente" 
              style="opacity: .8; max-width: 70px;" 
              class="hidden md:block group-hover:animate-spin transition-all duration-300"
            >
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Logi<span class="text-blue-600">Gate</span></h1>
            <p class="text-xs text-gray-600">Sistema Aduaneiro Inteligente</p>
          </div>
        </div>

        <!-- Desktop Menu -->
        <div class="hidden lg:flex items-center space-x-8">
          <a href="#home" class="text-gray-700 hover:text-blue-600 font-medium transition-colors" aria-current="page">Início</a>
          <a href="#sobre" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Sobre</a>
          <a href="#funcionalidades" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Funcionalidades</a>
          <a href="#planos" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Planos</a>
          <a href="#clientes" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Clientes</a>
          <a href="#contactos" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Contactos</a>
          
          <div class="flex items-center space-x-4">
            <a href="{{ route('login') }}" class="btn-outline text-sm">Login</a>
            <a href="{{ route('checkout', 2) }}" class="btn-primary text-sm">Começar Grátis</a>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <button id="mobileMenuButton" class="lg:hidden text-gray-700" aria-label="Abrir menu" aria-expanded="false" aria-controls="mobileMenu">
          <i class="fas fa-bars text-2xl" aria-hidden="true"></i>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="mobile-menu lg:hidden fixed inset-y-0 right-0 w-64 bg-white shadow-2xl p-8" aria-label="Menu móvel" hidden>
      <button id="closeMobileMenu" class="absolute top-6 right-6 text-gray-700" aria-label="Fechar menu">
        <i class="fas fa-times text-2xl" aria-hidden="true"></i>
      </button>
      
      <div class="mt-16 space-y-6">
        <a href="#home" class="block text-gray-700 hover:text-blue-600 font-medium text-lg">Início</a>
        <a href="#sobre" class="block text-gray-700 hover:text-blue-600 font-medium text-lg">Sobre</a>
        <a href="#funcionalidades" class="block text-gray-700 hover:text-blue-600 font-medium text-lg">Funcionalidades</a>
        <a href="#planos" class="block text-gray-700 hover:text-blue-600 font-medium text-lg">Planos</a>
        <a href="#clientes" class="block text-gray-700 hover:text-blue-600 font-medium text-lg">Clientes</a>
        <a href="#contactos" class="block text-gray-700 hover:text-blue-600 font-medium text-lg">Contactos</a>
        
        <div class="pt-8 space-y-4">
          <a href="{{ route('login') }}" class="btn-outline w-full block text-center">Login</a>
          <a href="{{ route('checkout', 2) }}" class="btn-primary w-full block text-center">Começar Grátis</a>
        </div>
      </div>
    </div>
  </nav>

  <main id="main-content">
    <!-- Hero Section -->
    <section id="home" class="section pt-32 pb-20 bg-gradient-to-br from-blue-50 to-white relative overflow-hidden" aria-labelledby="hero-heading">
      <!-- Background Elements -->
      <div class="absolute top-20 right-10 w-72 h-72 bg-blue-100 rounded-full opacity-20"></div>
      <div class="absolute bottom-20 left-10 w-96 h-96 bg-blue-200 rounded-full opacity-10"></div>
      
      <div class="container mx-auto px-4 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <!-- Left Content -->
          <div data-aos="fade-right" data-aos-delay="100">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-100 text-blue-700 mb-6">
              <span class="font-semibold">🚀 Plataforma Revolucionária</span>
            </div>
            
            <h1 id="hero-heading" class="text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
              Automatize a sua
              <span class="gradient-text">Gestão Aduaneira</span>
            </h1>
            
            <p class="text-xl text-gray-700 mb-10 leading-relaxed">
              Sistema completo para despachantes e transitários. Reduza custos, elimine erros e acelere os seus processos aduaneiros com tecnologia de ponta.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 mb-12">
              <a href="#planos" class="btn-primary text-lg">
                <i class="fas fa-rocket mr-2" aria-hidden="true"></i>Começar Agora
              </a>
              <a href="#demo" class="btn-outline text-lg">
                <i class="fas fa-play-circle mr-2" aria-hidden="true"></i>Ver Demonstração
              </a>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-6">
              <div>
                <div class="text-3xl font-bold text-blue-600">98%</div>
                <div class="text-gray-700 text-sm">Satisfação</div>
              </div>
              <div>
                <div class="text-3xl font-bold text-blue-600">24-72h</div>
                <div class="text-gray-700 text-sm">Processos</div>
              </div>
              <div>
                <div class="text-3xl font-bold text-blue-600">50+</div>
                <div class="text-gray-700 text-sm">Clientes Ativos</div>
              </div>
            </div>
          </div>
          
          <!-- Right Content -->
          <div data-aos="fade-left" data-aos-delay="200" class="relative">
            <div class="relative z-10">
              <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                   alt="Dashboard Logigate - Interface do sistema de gestão aduaneira" 
                   class="rounded-3xl shadow-2xl">
            </div>
            
            <!-- Floating Cards -->
            <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl w-64" data-aos="fade-up" data-aos-delay="400">
              <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-check text-green-600" aria-hidden="true"></i>
                </div>
                <div>
                  <div class="font-bold">Processo Concluído</div>
                  <div class="text-sm text-gray-600">Despacho #4587</div>
                </div>
              </div>
            </div>
            
            <div class="absolute -top-6 -right-6 bg-white p-6 rounded-2xl shadow-xl w-64" data-aos="fade-up" data-aos-delay="600">
              <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-ship text-blue-600" aria-hidden="true"></i>
                </div>
                <div>
                  <div class="font-bold">Carga Liberada</div>
                  <div class="text-sm text-gray-600">Porto de Luanda</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sobre -->
    <section id="sobre" class="section bg-white relative" aria-labelledby="sobre-heading">
      <div class="wave-divider">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
          <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="currentColor" class="text-gray-50"></path>
        </svg>
      </div>
      
      <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
          <span class="text-blue-600 font-semibold">SOBRE O LOGIGATE</span>
          <h2 id="sobre-heading" class="text-4xl font-bold text-gray-900 mt-4 mb-6">Transformando a Logística Aduaneira</h2>
          <p class="text-xl text-gray-700 max-w-3xl mx-auto">
            Desenvolvida pela Hongayetu LDA, somos a solução completa para gestão aduaneira em Angola.
          </p>
        </div>
        
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <!-- Left Side -->
          <div data-aos="fade-right">
            <div class="mb-8">
              <h3 class="text-2xl font-bold text-gray-900 mb-4">Nossa Missão</h3>
              <p class="text-gray-700 mb-6">
                Democratizar o acesso à tecnologia aduaneira de ponta, oferecendo uma plataforma robusta e acessível para empresas de todos os portes.
              </p>
            </div>
            
            <div class="space-y-6">
              <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-bolt text-blue-600 text-xl" aria-hidden="true"></i>
                </div>
                <div>
                  <h4 class="font-bold text-gray-900 mb-2">Processos Ágeis</h4>
                  <p class="text-gray-700">Reduza o tempo de processamento em até 70% com automação inteligente.</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-shield-alt text-green-600 text-xl" aria-hidden="true"></i>
                </div>
                <div>
                  <h4 class="font-bold text-gray-900 mb-2">Conformidade Total</h4>
                  <p class="text-gray-700">Esteja sempre em conformidade com as regulamentações aduaneiras angolanas.</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-chart-line text-purple-600 text-xl" aria-hidden="true"></i>
                </div>
                <div>
                  <h4 class="font-bold text-gray-900 mb-2">Insights Valiosos</h4>
                  <p class="text-gray-700">Relatórios detalhados para tomada de decisões estratégicas.</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Right Side -->
          <div data-aos="fade-left" class="relative">
            <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                 alt="Equipa Logigate em reunião de trabalho" 
                 class="rounded-3xl shadow-2xl">
            
            <!-- Experience Badge -->
            <div class="absolute -bottom-6 -right-6 bg-white p-8 rounded-3xl shadow-2xl w-72">
              <div class="text-center">
                <div class="text-5xl font-bold text-blue-600 mb-2">3+</div>
                <div class="font-bold text-gray-900 mb-2">Anos de Experiência</div>
                <div class="text-gray-700">No mercado aduaneiro angolano</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Funcionalidades -->
    <section id="funcionalidades" class="section bg-gray-50" aria-labelledby="funcionalidades-heading">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
          <span class="text-blue-600 font-semibold">FUNCIONALIDADES</span>
          <h2 id="funcionalidades-heading" class="text-4xl font-bold text-gray-900 mt-4 mb-6">Tudo o que precisa numa plataforma</h2>
          <p class="text-xl text-gray-700 max-w-3xl mx-auto">
            Sistema modular que atende todas as necessidades do seu negócio aduaneiro.
          </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <!-- Gestão Aduaneira -->
          <article class="feature-card" data-aos="fade-up" data-aos-delay="100">
            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6">
              <i class="fas fa-ship text-blue-600 text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">Gestão Aduaneira</h3>
            <p class="text-gray-700 mb-6">
              Controle completo de processos aduaneiros, desde o registo até à liberação de cargas.
            </p>
            <ul class="space-y-3">
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Despacho automatizado</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Calculadora de impostos</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Rastreamento em tempo real</span>
              </li>
            </ul>
          </article>
          
          <!-- Gestão Financeira -->
          <article class="feature-card" data-aos="fade-up" data-aos-delay="200">
            <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mb-6">
              <i class="fas fa-chart-pie text-green-600 text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">Gestão Financeira</h3>
            <p class="text-gray-700 mb-6">
              Controle financeiro completo com integração bancária e relatórios detalhados.
            </p>
            <ul class="space-y-3">
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Contas a pagar/receber</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Fluxo de caixa</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Integração bancária</span>
              </li>
            </ul>
          </article>
          
          <!-- Contabilidade -->
          <article class="feature-card" data-aos="fade-up" data-aos-delay="300">
            <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mb-6">
              <i class="fas fa-calculator text-purple-600 text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">Contabilidade</h3>
            <p class="text-gray-700 mb-6">
              Sistema contábil integrado com geração automática de lançamentos.
            </p>
            <ul class="space-y-3">
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Balancetes automáticos</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Demonstrações financeiras</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Conciliação bancária</span>
              </li>
            </ul>
          </article>
          
          <!-- Recursos Humanos -->
          <article class="feature-card" data-aos="fade-up" data-aos-delay="400">
            <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mb-6">
              <i class="fas fa-users text-orange-600 text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">Recursos Humanos</h3>
            <p class="text-gray-700 mb-6">
              Gestão completa de colaboradores, folha de pagamento e benefícios.
            </p>
            <ul class="space-y-3">
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Folha de pagamento</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Controlo de férias</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Gestão de benefícios</span>
              </li>
            </ul>
          </article>
          
          <!-- Pauta Aduaneira -->
          <article class="feature-card" data-aos="fade-up" data-aos-delay="500">
            <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mb-6">
              <i class="fas fa-file-invoice text-red-600 text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">Pauta Aduaneira</h3>
            <p class="text-gray-700 mb-6">
              Consulta integrada à pauta aduaneira com atualizações automáticas. <a href="{{ route('consultar.pauta')}}">Clica Aqui!</a>
            </p>
            <ul class="space-y-3">
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Consulta por NCM/SH</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Histórico de alterações</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Alertas de mudanças</span>
              </li>
            </ul>
          </article>
          
          <!-- Marketplace -->
          <article class="feature-card" data-aos="fade-up" data-aos-delay="600">
            <div class="w-16 h-16 bg-teal-100 rounded-2xl flex items-center justify-center mb-6">
              <i class="fas fa-store text-teal-600 text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-4">Marketplace</h3>
            <p class="text-gray-700 mb-6">
              Plataforma integrada para conexão entre clientes e fornecedores. <a href="{{ route('marketplace')}}">Clica Aqui!</a>
            </p>
            <ul class="space-y-3">
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Catálogo de serviços</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Solicitação de cotações</span>
              </li>
              <li class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3" aria-hidden="true"></i>
                <span>Avaliação de fornecedores</span>
              </li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <!-- Como Funciona -->
    <section class="section bg-white" aria-labelledby="como-funciona-heading">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
          <span class="text-blue-600 font-semibold">COMO FUNCIONA</span>
          <h2 id="como-funciona-heading" class="text-4xl font-bold text-gray-900 mt-4 mb-6">Implementação em 4 Passos Simples</h2>
        </div>
        
        <div class="max-w-4xl mx-auto">
          <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center" data-aos="fade-up" data-aos-delay="100">
              <div class="w-20 h-20 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-user-plus text-blue-600 text-2xl" aria-hidden="true"></i>
              </div>
              <h3 class="font-bold text-gray-900 mb-3">1. Registo</h3>
              <p class="text-gray-700">Crie a sua conta e complete o perfil da empresa</p>
            </div>
            
            <div class="text-center" data-aos="fade-up" data-aos-delay="200">
              <div class="w-20 h-20 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-cog text-green-600 text-2xl" aria-hidden="true"></i>
              </div>
              <h3 class="font-bold text-gray-900 mb-3">2. Configuração</h3>
              <p class="text-gray-700">Personalize o sistema conforme as suas necessidades</p>
            </div>
            
            <div class="text-center" data-aos="fade-up" data-aos-delay="300">
              <div class="w-20 h-20 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-graduation-cap text-purple-600 text-2xl" aria-hidden="true"></i>
              </div>
              <h3 class="font-bold text-gray-900 mb-3">3. Formação</h3>
              <p class="text-gray-700">A nossa equipa forma a sua equipa gratuitamente</p>
            </div>
            
            <div class="text-center" data-aos="fade-up" data-aos-delay="400">
              <div class="w-20 h-20 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-rocket text-orange-600 text-2xl" aria-hidden="true"></i>
              </div>
              <h3 class="font-bold text-gray-900 mb-3">4. Produção</h3>
              <p class="text-gray-700">Comece a usar o sistema com suporte 24/7</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Planos -->
    <section id="planos" class="section bg-gradient-to-b from-white to-blue-50" aria-labelledby="planos-heading">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
          <span class="text-blue-600 font-semibold">PLANOS E PREÇOS</span>
          <h2 id="planos-heading" class="text-4xl font-bold text-gray-900 mt-4 mb-6">Escolha o Plano Ideal para o Seu Negócio</h2>
          <p class="text-xl text-gray-700 max-w-3xl mx-auto">
            Oferecemos flexibilidade para empresas de todos os tamanhos. Teste grátis por 30 dias.
          </p>
        </div>
        
        <!-- Billing Toggle Modalidade de Pagamento-->
        <div class="flex justify-center mb-12" data-aos="fade-up">
          <div class="bg-gray-100 rounded-2xl p-1 inline-flex" role="tablist" aria-label="Modalidades de pagamento">
            <button id="monthlyToggle" class="px-6 py-3 rounded-xl font-semibold bg-blue-600 text-white" role="tab" aria-selected="true" aria-controls="monthly-prices">Mensal</button>
            <button id="semestreToggle" class="px-6 py-3 rounded-xl font-semibold text-gray-700" role="tab" aria-selected="false" aria-controls="semester-prices">Semestral <span class="text-blue-600">(1 mês grátis)</span></button>
            <button id="annualToggle" class="px-6 py-3 rounded-xl font-semibold text-gray-700" role="tab" aria-selected="false" aria-controls="annual-prices">Anual <span class="text-green-600">(2 meses grátis)</span></button>
          </div>
        </div>
        
        <!-- Pricing Cards -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          @php $delay = 100; @endphp
          @php use Illuminate\Support\Str; @endphp

          @foreach($planos as $index => $pl)
            @php
              $planId = 'plan-' . $index;
            @endphp
            <article 
              class="pricing-card {{ $pl->is_popular ? 'popular' : '' }}"
              data-aos="fade-up"
              data-aos-delay="{{ $delay }}"
              aria-labelledby="{{ $planId }}-title"
            >
              <div class="text-center mb-8">
                <h3 id="{{ $planId }}-title" class="text-2xl font-bold text-gray-900 mb-2">
                  {{ $pl->nome }}
                </h3>

                <p class="text-gray-700 mb-6">
                  {{ Str::limit($pl->descricao, 50) }}
                </p>

                {{-- Preço mensal --}}
                <div class="price-monthly" id="monthly-{{ $planId }}" role="tabpanel" aria-labelledby="monthlyToggle">
                  <div class="text-4xl font-bold text-gray-900">
                    {{ number_format($pl->preco_mensal, 2, ',', '.') }}
                    <span class="text-xl">AOA</span>
                  </div>
                  <div class="text-gray-600">por mês</div>
                </div>

                {{-- Preço semestral --}}
                <div class="price-semestre hidden" id="semester-{{ $planId }}" role="tabpanel" aria-labelledby="semestreToggle">
                  <div class="text-4xl font-bold text-gray-900">
                    {{ number_format($pl->preco_semestral, 2, ',', '.') }}
                    <span class="text-xl">AOA</span>
                  </div>
                  <div class="text-gray-600">
                    por semestre ({{ number_format($pl->preco_semestral / 6, 2, ',', '.') }} AOA/mês)
                  </div>
                </div>

                {{-- Preço anual --}}
                <div class="price-annual hidden" id="annual-{{ $planId }}" role="tabpanel" aria-labelledby="annualToggle">
                  <div class="text-4xl font-bold text-gray-900">
                    {{ number_format($pl->preco_anual, 2, ',', '.') }}
                    <span class="text-xl">AOA</span>
                  </div>
                  <div class="text-gray-600">
                    por ano ({{ number_format($pl->preco_anual / 12, 2, ',', '.') }} AOA/mês)
                  </div>
                </div>
              </div>

              {{-- Itens do plano --}}
              <ul class="space-y-4 mb-8">
                @foreach($pl->itemplano as $item)
                  <li class="flex items-center {{ $item->icon === 'fa-times' ? 'opacity-50' : '' }}">
                    <i class="fas {{ $item->icon }} {{ $item->text_color }} mr-3" aria-hidden="true"></i>
                    <span>{{ $item->item }}</span>
                  </li>
                @endforeach
              </ul>

              {{-- Botão --}}
              <form method="GET" action="{{ route('register', $pl->id) }}">
                <input type="hidden" name="modalidade" class="billing-cycle" value="monthly">
                <input type="hidden" name="plano" value="{{ $pl->id }}">
                <button type="submit" class="w-full {{ $pl->is_free ? 'btn-outline' : 'btn-primary' }}">
                  {{ $pl->is_free ? 'Começar Grátis' : 'Escolher Plano' }}
                </button>
              </form>
            </article>

            @php $delay += 100; @endphp
          @endforeach
        </div>
      </div>
    </section>

    <!-- Clientes -->
    <section id="clientes" class="section bg-white" aria-labelledby="clientes-heading">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
          <span class="text-blue-600 font-semibold">OS NOSSOS CLIENTES</span>
          <h2 id="clientes-heading" class="text-4xl font-bold text-gray-900 mt-4 mb-6">Empresas que confiam na Logigate</h2>
        </div>
        
        <!-- Testimonials -->
        <div class="max-w-4xl mx-auto mb-16" data-aos="fade-up">
          <div class="swiper testimonialsSwiper">
            <div class="swiper-wrapper">
              <div class="swiper-slide">
                <article class="testimonial-card">
                  <div class="flex items-center mb-6">
                    <div class="w-16 h-16 rounded-full overflow-hidden mr-4">
                      <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" 
                           alt="Carlos Mendes, CEO da TransLog Angola" class="w-full h-full object-cover">
                    </div>
                    <div>
                      <h4 class="font-bold text-gray-900">Carlos Mendes</h4>
                      <p class="text-gray-600">CEO - TransLog Angola</p>
                    </div>
                  </div>
                  <blockquote class="text-gray-700 italic text-lg">
                    "A Logigate transformou completamente a nossa operação. Reduzimos o tempo de processamento em 60% e eliminámos erros manuais. A equipa de suporte é excecional!"
                  </blockquote>
                </article>
              </div>
              
              <div class="swiper-slide">
                <article class="testimonial-card">
                  <div class="flex items-center mb-6">
                    <div class="w-16 h-16 rounded-full overflow-hidden mr-4">
                      <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" 
                           alt="Ana Silva, Gerente de Logística da GlobalTrade" class="w-full h-full object-cover">
                    </div>
                    <div>
                      <h4 class="font-bold text-gray-900">Ana Silva</h4>
                      <p class="text-gray-600">Gerente de Logística - GlobalTrade</p>
                    </div>
                  </div>
                  <blockquote class="text-gray-700 italic text-lg">
                    "A integração com a pauta aduaneira automatizada poupa-nos horas de trabalho manual todos os dias. O retorno sobre o investimento foi imediato."
                  </blockquote>
                </article>
              </div>
              
              <div class="swiper-slide">
                <article class="testimonial-card">
                  <div class="flex items-center mb-6">
                    <div class="w-16 h-16 rounded-full overflow-hidden mr-4">
                      <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" 
                           alt="João Pereira, Diretor Financeiro da Cargo Express" class="w-full h-full object-cover">
                    </div>
                    <div>
                      <h4 class="font-bold text-gray-900">João Pereira</h4>
                      <p class="text-gray-600">Diretor Financeiro - Cargo Express</p>
                    </div>
                  </div>
                  <blockquote class="text-gray-700 italic text-lg">
                    "O sistema contábil integrado permite-nos ter um controlo financeiro muito mais preciso. As demonstrações financeiras são geradas automaticamente."
                  </blockquote>
                </article>
              </div>
            </div>
            <div class="swiper-pagination"></div>
          </div>
        </div>
        
        <!-- Client Logos -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8 items-center" data-aos="fade-up">
          <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
            <img src="https://via.placeholder.com/120x60?text=CLIENTE+1" alt="Logotipo do Cliente 1" class="max-w-full max-h-full object-contain">
          </div>
          <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
            <img src="https://via.placeholder.com/120x60?text=CLIENTE+2" alt="Logotipo do Cliente 2" class="max-w-full max-h-full object-contain">
          </div>
          <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
            <img src="https://via.placeholder.com/120x60?text=CLIENTE+3" alt="Logotipo do Cliente 3" class="max-w-full max-h-full object-contain">
          </div>
          <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
            <img src="https://via.placeholder.com/120x60?text=CLIENTE+4" alt="Logotipo do Cliente 4" class="max-w-full max-h-full object-contain">
          </div>
          <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
            <img src="https://via.placeholder.com/120x60?text=CLIENTE+5" alt="Logotipo do Cliente 5" class="max-w-full max-h-full object-contain">
          </div>
          <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
            <img src="https://via.placeholder.com/120x60?text=CLIENTE+6" alt="Logotipo do Cliente 6" class="max-w-full max-h-full object-contain">
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="section bg-gradient-to-r from-blue-600 to-blue-800 text-white relative overflow-hidden" aria-labelledby="cta-heading">
      <div class="absolute inset-0">
        <div class="absolute top-10 left-10 w-64 h-64 bg-white/10 rounded-full"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-white/5 rounded-full"></div>
      </div>
      
      <div class="container mx-auto px-4 lg:px-8 relative z-10">
        <div class="max-w-4xl mx-auto text-center" data-aos="fade-up">
          <h2 id="cta-heading" class="text-4xl font-bold mb-6">Pronto para transformar a sua operação aduaneira?</h2>
          <p class="text-xl mb-10 opacity-90">
            Experimente grátis por 30 dias. Sem compromisso, sem cartão de crédito.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#planos" class="btn-primary bg-white text-blue-600 hover:bg-gray-100 text-lg">
              <i class="fas fa-play-circle mr-2" aria-hidden="true"></i>Começar Teste Grátis
            </a>
            <a href="#demo" class="btn-outline border-white text-white hover:bg-white/10 text-lg">
              <i class="fas fa-calendar-alt mr-2" aria-hidden="true"></i>Agendar Demonstração
            </a>
          </div>
          
          <p class="mt-8 text-sm opacity-90">
            <i class="fas fa-check-circle mr-2" aria-hidden="true"></i>Suporte incluído durante o teste
            <i class="fas fa-check-circle mx-4" aria-hidden="true"></i>Dados protegidos e seguros
            <i class="fas fa-check-circle mx-4" aria-hidden="true"></i>Cancelamento a qualquer momento
          </p>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="section bg-gray-50" aria-labelledby="faq-heading">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
          <span class="text-blue-600 font-semibold">PERGUNTAS FREQUENTES</span>
          <h2 id="faq-heading" class="text-4xl font-bold text-gray-900 mt-4 mb-6">Tire as suas dúvidas</h2>
        </div>
        
        <div class="max-w-3xl mx-auto" data-aos="fade-up">
          <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
              <button class="faq-question w-full text-left p-6 flex justify-between items-center" aria-expanded="false" aria-controls="faq-1-answer">
                <span class="font-semibold text-gray-900 text-lg">Como funciona o período de teste?</span>
                <i class="fas fa-chevron-down text-blue-600" aria-hidden="true"></i>
              </button>
              <div id="faq-1-answer" class="faq-answer p-6 pt-0 hidden">
                <p class="text-gray-700">
                  Oferecemos 30 dias gratuitos com acesso completo a todas as funcionalidades. Não solicitamos cartão de crédito e pode cancelar a qualquer momento durante o período de teste.
                </p>
              </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
              <button class="faq-question w-full text-left p-6 flex justify-between items-center" aria-expanded="false" aria-controls="faq-2-answer">
                <span class="font-semibold text-gray-900 text-lg">A Logigate atende às normas aduaneiras angolanas?</span>
                <i class="fas fa-chevron-down text-blue-600" aria-hidden="true"></i>
              </button>
              <div id="faq-2-answer" class="faq-answer p-6 pt-0 hidden">
                <p class="text-gray-700">
                  Sim! A nossa plataforma é desenvolvida especificamente para o mercado angolano e está sempre atualizada com as últimas regulamentações da Alfândega de Angola. Temos uma equipa dedicada que monitoriza as mudanças legislativas.
                </p>
              </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
              <button class="faq-question w-full text-left p-6 flex justify-between items-center" aria-expanded="false" aria-controls="faq-3-answer">
                <span class="font-semibold text-gray-900 text-lg">Preciso de formação para usar o sistema?</span>
                <i class="fas fa-chevron-down text-blue-600" aria-hidden="true"></i>
              </button>
              <div id="faq-3-answer" class="faq-answer p-6 pt-0 hidden">
                <p class="text-gray-700">
                  Oferecemos formação gratuita para todos os planos. Para planos Empresarial e Personalizado, incluímos formação presencial e materiais personalizados. O sistema é intuitivo, mas garantimos que a sua equipa estará totalmente capacitada.
                </p>
              </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
              <button class="faq-question w-full text-left p-6 flex justify-between items-center" aria-expanded="false" aria-controls="faq-4-answer">
                <span class="font-semibold text-gray-900 text-lg">Como é feito o suporte técnico?</span>
                <i class="fas fa-chevron-down text-blue-600" aria-hidden="true"></i>
              </button>
              <div id="faq-4-answer" class="faq-answer p-6 pt-0 hidden">
                <p class="text-gray-700">
                  Oferecemos múltiplos canais de suporte: email, chat online, telefone e WhatsApp. Para planos superiores, oferecemos suporte 24/7 com SLA de resposta garantido. Temos uma equipa de especialistas aduaneiros pronta para ajudar.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contactos -->
    <section id="contactos" class="section bg-white" aria-labelledby="contactos-heading">
      <div class="container mx-auto px-4 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12">
          <!-- Contact Info -->
          <div data-aos="fade-right">
            <h2 id="contactos-heading" class="text-4xl font-bold text-gray-900 mb-6">Entre em Contacto</h2>
            <p class="text-xl text-gray-700 mb-10">
              Tem dúvidas ou precisa de uma solução personalizada? A nossa equipa está pronta para ajudar.
            </p>
            
            <div class="space-y-8">
              <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-map-marker-alt text-blue-600" aria-hidden="true"></i>
                </div>
                <div>
                  <h4 class="font-bold text-gray-900 mb-2">Endereço</h4>
                  <p class="text-gray-700">Rua Amilcar Cabral nº 66<br>Luanda, Ingombota<br>Angola</p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-phone-alt text-green-600" aria-hidden="true"></i>
                </div>
                <div>
                  <h4 class="font-bold text-gray-900 mb-2">Telefone</h4>
                  <p class="text-gray-700">
                    <a href="tel:+244948242262" class="hover:text-blue-600">+244 948 242 262</a><br>
                    Segunda a Sexta: 8h às 18h
                  </p>
                </div>
              </div>
              
              <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-envelope text-purple-600" aria-hidden="true"></i>
                </div>
                <div>
                  <h4 class="font-bold text-gray-900 mb-2">Email</h4>
                  <p class="text-gray-700">
                    <a href="mailto:geral@hongayetu.com" class="hover:text-blue-600">geral@hongayetu.com</a><br>
                    Resposta em até 24h
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Social Media -->
            <div class="mt-12">
              <h4 class="font-bold text-gray-900 mb-4">Siga-nos</h4>
              <div class="flex space-x-4">
                <a href="#" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-blue-100 transition-colors" aria-label="Facebook">
                  <i class="fab fa-facebook-f text-gray-700" aria-hidden="true"></i>
                </a>
                <a href="#" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-blue-100 transition-colors" aria-label="LinkedIn">
                  <i class="fab fa-linkedin-in text-gray-700" aria-hidden="true"></i>
                </a>
                <a href="#" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-blue-100 transition-colors" aria-label="Instagram">
                  <i class="fab fa-instagram text-gray-700" aria-hidden="true"></i>
                </a>
                <a href="#" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-blue-100 transition-colors" aria-label="YouTube">
                  <i class="fab fa-youtube text-gray-700" aria-hidden="true"></i>
                </a>
              </div>
            </div>
          </div>
          
          <!-- Contact Form -->
          <div data-aos="fade-left" class="bg-white rounded-3xl shadow-xl p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Envie-nos uma mensagem</h3>
            
            <form id="contactForm" action="{{ route('contact.send') }}" method="POST" class="space-y-6">
              @csrf
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label for="nome" class="block text-gray-700 mb-2">Nome completo *</label>
                  <input type="text" id="nome" name="nome" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                </div>
                <div>
                  <label for="empresa" class="block text-gray-700 mb-2">Empresa</label>
                  <input type="text" id="empresa" name="empresa" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                </div>
              </div>
              
              <div>
                <label for="email" class="block text-gray-700 mb-2">Email *</label>
                <input type="email" id="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
              </div>
              
              <div>
                <label for="telefone" class="block text-gray-700 mb-2">Telefone *</label>
                <input type="tel" id="telefone" name="telefone" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
              </div>
              
              <div>
                <label for="assunto" class="block text-gray-700 mb-2">Assunto *</label>
                <select id="assunto" name="assunto" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                  <option value="">Selecione um assunto</option>
                  <option value="demonstracao">Demonstração do sistema</option>
                  <option value="informacoes">Informações sobre planos</option>
                  <option value="suporte">Suporte técnico</option>
                  <option value="parcerias">Parcerias</option>
                  <option value="outro">Outro</option>
                </select>
              </div>
              
              <div>
                <label for="mensagem" class="block text-gray-700 mb-2">Mensagem *</label>
                <textarea id="mensagem" name="mensagem" rows="4" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"></textarea>
              </div>
              
              <button type="submit" class="btn-primary w-full text-lg">
                <i class="fas fa-paper-plane mr-2" aria-hidden="true"></i>Enviar Mensagem
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-900 text-white py-12" aria-label="Rodapé">
    <div class="container mx-auto px-4 lg:px-8">
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
        <div>
          <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
              <span class="font-bold text-white">LG</span>
            </div>
            <div>
              <h3 class="text-xl font-bold">LogiGate</h3>
              <p class="text-gray-400 text-sm">Sistema Aduaneiro Inteligente</p>
            </div>
          </div>
          <p class="text-gray-400">
            A transformar a gestão aduaneira em Angola com tecnologia de ponta e inovação constante.
          </p>
        </div>
        
        <div>
          <h4 class="font-bold text-lg mb-6">Links Rápidos</h4>
          <ul class="space-y-3">
            <li><a href="#home" class="text-gray-400 hover:text-white transition-colors">Início</a></li>
            <li><a href="#sobre" class="text-gray-400 hover:text-white transition-colors">Sobre</a></li>
            <li><a href="#funcionalidades" class="text-gray-400 hover:text-white transition-colors">Funcionalidades</a></li>
            <li><a href="#planos" class="text-gray-400 hover:text-white transition-colors">Planos</a></li>
            <li><a href="#contactos" class="text-gray-400 hover:text-white transition-colors">Contactos</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-bold text-lg mb-6">Recursos</h4>
          <ul class="space-y-3">
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Documentação</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">API</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Estado do Sistema</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Política de Privacidade</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-bold text-lg mb-6">Newsletter</h4>
          <p class="text-gray-400 mb-4">
            Receba as últimas novidades sobre legislação aduaneira e atualizações do sistema.
          </p>
          <form class="flex" action="{{ route('newsletter.subscribe') }}" method="POST">
            @csrf
            <label for="newsletter-email" class="sr-only">Email para newsletter</label>
            <input type="email" id="newsletter-email" name="email" placeholder="O seu email" class="px-4 py-3 rounded-l-xl w-full text-gray-900 outline-none" required>
            <button type="submit" class="bg-blue-600 px-4 rounded-r-xl hover:bg-blue-700 transition-colors" aria-label="Subscrever newsletter">
              <i class="fas fa-paper-plane" aria-hidden="true"></i>
            </button>
          </form>
        </div>
      </div>
      
      <div class="border-t border-gray-800 pt-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-400 mb-4 md:mb-0">
            &copy; 2024 Logigate by Hongayetu LDA. Todos os direitos reservados.
          </p>
          <div class="text-gray-400">
            <span class="mr-4">Registo Comercial: 1234567890</span>
            <span>NIF: 5001234567</span>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
  
  <script>
    // Initialize AOS
    AOS.init({
      duration: 1000,
      once: true,
      offset: 100
    });

    // Mobile Menu Toggle
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const closeMobileMenu = document.getElementById('closeMobileMenu');
    const mobileMenu = document.getElementById('mobileMenu');

    if (mobileMenuButton && closeMobileMenu && mobileMenu) {
      mobileMenuButton.addEventListener('click', () => {
        mobileMenu.classList.add('active');
        mobileMenu.removeAttribute('hidden');
        mobileMenuButton.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
      });

      closeMobileMenu.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
        mobileMenu.setAttribute('hidden', '');
        mobileMenuButton.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = 'auto';
      });

      // Close mobile menu when clicking on links
      document.querySelectorAll('#mobileMenu a').forEach(link => {
        link.addEventListener('click', () => {
          mobileMenu.classList.remove('active');
          mobileMenu.setAttribute('hidden', '');
          mobileMenuButton.setAttribute('aria-expanded', 'false');
          document.body.style.overflow = 'auto';
        });
      });
    }

    // Pricing Toggle
    const monthlyToggle = document.getElementById('monthlyToggle');
    const semestreToggle = document.getElementById('semestreToggle');
    const annualToggle = document.getElementById('annualToggle');

    const monthlyPrices = document.querySelectorAll('.price-monthly');
    const semestrePrices = document.querySelectorAll('.price-semestre');
    const annualPrices = document.querySelectorAll('.price-annual');

    // Todos os inputs hidden dos formulários
    const cycleInputs = document.querySelectorAll('.billing-cycle');

    function setCycle(cycle) {
      cycleInputs.forEach(input => {
        input.value = cycle;
      });
    }

    function updateTabs(activeTab) {
      [monthlyToggle, semestreToggle, annualToggle].forEach(tab => {
        tab.classList.remove('bg-blue-600', 'text-white');
        tab.classList.add('text-gray-700');
        tab.setAttribute('aria-selected', 'false');
      });
      
      activeTab.classList.add('bg-blue-600', 'text-white');
      activeTab.classList.remove('text-gray-700');
      activeTab.setAttribute('aria-selected', 'true');
    }

    // Estado inicial
    setCycle('monthly');

    // Mensal
    if (monthlyToggle) {
      monthlyToggle.addEventListener('click', () => {
        updateTabs(monthlyToggle);

        monthlyPrices.forEach(el => el.classList.remove('hidden'));
        semestrePrices.forEach(el => el.classList.add('hidden'));
        annualPrices.forEach(el => el.classList.add('hidden'));

        setCycle('monthly');
      });
    }

    // Semestral
    if (semestreToggle) {
      semestreToggle.addEventListener('click', () => {
        updateTabs(semestreToggle);

        semestrePrices.forEach(el => el.classList.remove('hidden'));
        monthlyPrices.forEach(el => el.classList.add('hidden'));
        annualPrices.forEach(el => el.classList.add('hidden'));

        setCycle('semestral');
      });
    }

    // Anual
    if (annualToggle) {
      annualToggle.addEventListener('click', () => {
        updateTabs(annualToggle);

        annualPrices.forEach(el => el.classList.remove('hidden'));
        monthlyPrices.forEach(el => el.classList.add('hidden'));
        semestrePrices.forEach(el => el.classList.add('hidden'));

        setCycle('annual');
      });
    }

    // Initialize Swiper
    const testimonialsSwiper = new Swiper('.testimonialsSwiper', {
      loop: true,
      autoplay: {
        delay: 5000,
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      breakpoints: {
        640: {
          slidesPerView: 1,
          spaceBetween: 20,
        },
        768: {
          slidesPerView: 2,
          spaceBetween: 30,
        },
        1024: {
          slidesPerView: 2,
          spaceBetween: 30,
        },
      },
    });

    // FAQ Accordion
    document.querySelectorAll('.faq-question').forEach(button => {
      button.addEventListener('click', () => {
        const answer = document.getElementById(button.getAttribute('aria-controls'));
        const icon = button.querySelector('i');
        const isExpanded = button.getAttribute('aria-expanded') === 'true';
        
        // Toggle current answer
        if (answer) {
          answer.classList.toggle('hidden');
          button.setAttribute('aria-expanded', !isExpanded);
          
          if (icon) {
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
          }
        }
        
        // Close other answers
        document.querySelectorAll('.faq-question').forEach(otherButton => {
          if (otherButton !== button) {
            const otherAnswerId = otherButton.getAttribute('aria-controls');
            const otherAnswer = document.getElementById(otherAnswerId);
            const otherIcon = otherButton.querySelector('i');
            
            if (otherAnswer && !otherAnswer.classList.contains('hidden')) {
              otherAnswer.classList.add('hidden');
              otherButton.setAttribute('aria-expanded', 'false');
              
              if (otherIcon) {
                otherIcon.classList.remove('fa-chevron-up');
                otherIcon.classList.add('fa-chevron-down');
              }
            }
          }
        });
      });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 80,
            behavior: 'smooth'
          });
        }
      });
    });

    // Form submission handling with fetch API
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Disable button and show loading
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>A enviar...';
            
            // Remove previous error messages
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            document.querySelectorAll('.border-red-500').forEach(el => {
                el.classList.remove('border-red-500');
            });
            
            try {
                const response = await fetch('/api/v1/contact/send', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Success message
                    showNotification('success', data.message || 'Mensagem enviada com sucesso!');
                    this.reset();
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = document.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('border-red-500');
                                
                                // Add error message
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'error-message text-red-500 text-sm mt-1';
                                errorDiv.textContent = data.errors[field][0];
                                input.parentNode.appendChild(errorDiv);
                            }
                        });
                        
                        showNotification('error', 'Por favor, corrija os erros no formulário.');
                    } else {
                        showNotification('error', data.message || 'Erro ao enviar mensagem.');
                    }
                }
            } catch (error) {
                console.error('Erro:', error);
                showNotification('error', 'Erro de ligação. Por favor, verifique a sua internet.');
            } finally {
                // Restore button
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        });
    }

    // Newsletter form submission
    const newsletterForm = document.querySelector('footer form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonHtml = submitButton.innerHTML;
            
            const email = emailInput.value.trim();
            
            // Validação básica
            if (!email || !isValidEmail(email)) {
                showNotification('error', 'Por favor, insira um email válido.');
                emailInput.focus();
                return;
            }
            
            // Disable button and show loading
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                
                const response = await fetch('/api/v1/newsletter/subscribe', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification('success', data.message || 'Confirmação enviada! Verifique o seu email.');
                    emailInput.value = ''; // Limpar campo
                    
                    // Opcional: guardar no localStorage que já subscreveu
                    localStorage.setItem('newsletter_subscribed', 'true');
                } else {
                    if (data.errors && data.errors.email) {
                        showNotification('error', data.errors.email[0]);
                    } else {
                        showNotification('error', data.message || 'Erro ao subscrever.');
                    }
                }
            } catch (error) {
                console.error('Erro:', error);
                showNotification('error', 'Erro de ligação. Por favor, tente novamente.');
            } finally {
                // Restore button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonHtml;
            }
        });
    }

    // Função auxiliar para validar email
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Função para mostrar notificações
    function showNotification(type, message) {
        // Remove existing notifications
        const existingNotification = document.querySelector('.notification-toast');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification-toast fixed top-24 right-6 z-50 px-6 py-4 rounded-xl shadow-2xl transform transition-all duration-500 translate-x-0 ${
            type === 'success' ? 'bg-green-50 border-l-4 border-green-500 text-green-800' : 'bg-red-50 border-l-4 border-red-500 text-red-800'
        }`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} text-2xl mr-4"></i>
                <div>
                    <p class="font-semibold">${type === 'success' ? 'Sucesso!' : 'Erro!'}</p>
                    <p class="text-sm">${message}</p>
                </div>
                <button class="ml-6 text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Navbar scroll effect
    window.addEventListener('scroll', () => {
      const navbar = document.querySelector('nav');
      if (navbar) {
        if (window.scrollY > 100) {
          navbar.classList.add('shadow-lg');
        } else {
          navbar.classList.remove('shadow-lg');
        }
      }
    });

    // Counter animation
    function animateCounter(element, target, duration) {
      let start = 0;
      const increment = target / (duration / 16);
      const timer = setInterval(() => {
        start += increment;
        element.textContent = Math.floor(start);
        if (start >= target) {
          element.textContent = target;
          clearInterval(timer);
        }
      }, 16);
    }

    // Initialize counters when in view
    const observerOptions = {
      threshold: 0.5
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const counters = entry.target.querySelectorAll('.counter');
          counters.forEach(counter => {
            const target = parseInt(counter.textContent);
            animateCounter(counter, target, 2000);
          });
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);
  </script>
</body>
</html>