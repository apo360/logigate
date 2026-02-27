<!DOCTYPE html>
<html lang="pt-AO">
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
      color: #333;
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
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0, 71, 171, 0.3);
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
    }
    
    .btn-outline:hover {
      background: var(--primary);
      color: white;
      transform: translateY(-2px);
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
  </style>
</head>
<body class="bg-gray-50">
  
  <!-- Navigation -->
  <nav class="fixed w-full z-50 transition-all duration-300 bg-white/90 backdrop-blur-md">
    <div class="container mx-auto px-4 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
          <div class="group">
            <img 
              src="{{ asset('dist/img/LOGIGATE.png') }}" 
              alt="LogiGate" 
              style="opacity: .8; max-width: 70px;" 
              class="hidden md:block group-hover:animate-spin transition-all duration-300"
            >
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Logi<span class="text-blue-600">Gate</span></h1>
            <p class="text-xs text-gray-500">Sistema Aduaneiro Inteligente</p>
          </div>
        </div>

        <!-- Desktop Menu -->
        <div class="hidden lg:flex items-center space-x-8">
          <a href="#home" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Início</a>
          <a href="#sobre" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Sobre</a>
          <a href="#funcionalidades" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Funcionalidades</a>
          <a href="#planos" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Planos</a>
          <a href="#clientes" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Clientes</a>
          <a href="#contactos" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Contactos</a>
          
          <div class="flex items-center space-x-4">
            <a href="{{ route('login') }}" class="btn-outline text-sm">Login</a>
            <a href="{{ route('checkout', 2) }}"" class="btn-primary text-sm">Começar Grátis</a>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <button id="mobileMenuButton" class="lg:hidden text-gray-700">
          <i class="fas fa-bars text-2xl"></i>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="mobile-menu lg:hidden fixed inset-y-0 right-0 w-64 bg-white shadow-2xl p-8">
      <button id="closeMobileMenu" class="absolute top-6 right-6 text-gray-700">
        <i class="fas fa-times text-2xl"></i>
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
          <a href="{{ route('checkout', 2) }}"" class="btn-primary w-full block text-center">Começar Grátis</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section id="home" class="section pt-32 pb-20 bg-gradient-to-br from-blue-50 to-white relative overflow-hidden">
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
          
          <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
            Automatize sua
            <span class="gradient-text">Gestão Aduaneira</span>
          </h1>
          
          <p class="text-xl text-gray-600 mb-10 leading-relaxed">
            Sistema completo para despachantes e transitários. Reduza custos, elimine erros e acelere seus processos aduaneiros com tecnologia de ponta.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 mb-12">
            <a href="#planos" class="btn-primary text-lg">
              <i class="fas fa-rocket mr-2"></i>Começar Agora
            </a>
            <a href="#demo" class="btn-outline text-lg">
              <i class="fas fa-play-circle mr-2"></i>Ver Demonstração
            </a>
          </div>
          
          <!-- Stats -->
          <div class="grid grid-cols-3 gap-6">
            <div>
              <div class="text-3xl font-bold text-blue-600">98%</div>
              <div class="text-gray-600 text-sm">Satisfação</div>
            </div>
            <div>
              <div class="text-3xl font-bold text-blue-600">24-72h</div>
              <div class="text-gray-600 text-sm">Processos</div>
            </div>
            <div>
              <div class="text-3xl font-bold text-blue-600">50+</div>
              <div class="text-gray-600 text-sm">Clientes Ativos</div>
            </div>
          </div>
        </div>
        
        <!-- Right Content -->
        <div data-aos="fade-left" data-aos-delay="200" class="relative">
          <div class="relative z-10">
            <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                 alt="Dashboard Logigate" 
                 class="rounded-3xl shadow-2xl">
          </div>
          
          <!-- Floating Cards -->
          <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl w-64" data-aos="fade-up" data-aos-delay="400">
            <div class="flex items-center space-x-3 mb-3">
              <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check text-green-600"></i>
              </div>
              <div>
                <div class="font-bold">Processo Concluído</div>
                <div class="text-sm text-gray-500">Despacho #4587</div>
              </div>
            </div>
          </div>
          
          <div class="absolute -top-6 -right-6 bg-white p-6 rounded-2xl shadow-xl w-64" data-aos="fade-up" data-aos-delay="600">
            <div class="flex items-center space-x-3 mb-3">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-ship text-blue-600"></i>
              </div>
              <div>
                <div class="font-bold">Carga Liberada</div>
                <div class="text-sm text-gray-500">Porto de Luanda</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Sobre -->
  <section id="sobre" class="section bg-white relative">
    <div class="wave-divider">
      <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="currentColor" class="text-gray-50"></path>
      </svg>
    </div>
    
    <div class="container mx-auto px-4 lg:px-8">
      <div class="text-center mb-16" data-aos="fade-up">
        <span class="text-blue-600 font-semibold">SOBRE O LOGIGATE</span>
        <h2 class="text-4xl font-bold text-gray-900 mt-4 mb-6">Transformando a Logística Aduaneira</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Desenvolvida pela Hongayetu LDA, somos a solução completa para gestão aduaneira em Angola.
        </p>
      </div>
      
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <!-- Left Side -->
        <div data-aos="fade-right">
          <div class="mb-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Nossa Missão</h3>
            <p class="text-gray-600 mb-6">
              Democratizar o acesso à tecnologia aduaneira de ponta, oferecendo uma plataforma robusta e acessível para empresas de todos os portes.
            </p>
          </div>
          
          <div class="space-y-6">
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-bolt text-blue-600 text-xl"></i>
              </div>
              <div>
                <h4 class="font-bold text-gray-900 mb-2">Processos Ágeis</h4>
                <p class="text-gray-600">Reduza o tempo de processamento em até 70% com automação inteligente.</p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-shield-alt text-green-600 text-xl"></i>
              </div>
              <div>
                <h4 class="font-bold text-gray-900 mb-2">Conformidade Total</h4>
                <p class="text-gray-600">Esteja sempre em conformidade com as regulamentações aduaneiras angolanas.</p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
              </div>
              <div>
                <h4 class="font-bold text-gray-900 mb-2">Insights Valiosos</h4>
                <p class="text-gray-600">Relatórios detalhados para tomada de decisões estratégicas.</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Right Side -->
        <div data-aos="fade-left" class="relative">
          <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
               alt="Team Collaboration" 
               class="rounded-3xl shadow-2xl">
          
          <!-- Experience Badge -->
          <div class="absolute -bottom-6 -right-6 bg-white p-8 rounded-3xl shadow-2xl w-72">
            <div class="text-center">
              <div class="text-5xl font-bold text-blue-600 mb-2">3+</div>
              <div class="font-bold text-gray-900 mb-2">Anos de Experiência</div>
              <div class="text-gray-600">No mercado aduaneiro angolano</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Funcionalidades -->
  <section id="funcionalidades" class="section bg-gray-50">
    <div class="container mx-auto px-4 lg:px-8">
      <div class="text-center mb-16" data-aos="fade-up">
        <span class="text-blue-600 font-semibold">FUNCIONALIDADES</span>
        <h2 class="text-4xl font-bold text-gray-900 mt-4 mb-6">Tudo que você precisa em uma plataforma</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Sistema modular que atende todas as necessidades do seu negócio aduaneiro.
        </p>
      </div>
      
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Gestão Aduaneira -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
          <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6">
            <i class="fas fa-ship text-blue-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-4">Gestão Aduaneira</h3>
          <p class="text-gray-600 mb-6">
            Controle completo de processos aduaneiros, desde o registro até a liberação de cargas.
          </p>
          <ul class="space-y-3">
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Despacho automatizado</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Calculadora de impostos</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Rastreamento em tempo real</span>
            </li>
          </ul>
        </div>
        
        <!-- Gestão Financeira -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
          <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mb-6">
            <i class="fas fa-chart-pie text-green-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-4">Gestão Financeira</h3>
          <p class="text-gray-600 mb-6">
            Controle financeiro completo com integração bancária e relatórios detalhados.
          </p>
          <ul class="space-y-3">
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Contas a pagar/receber</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Fluxo de caixa</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Integração bancária</span>
            </li>
          </ul>
        </div>
        
        <!-- Contabilidade -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
          <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mb-6">
            <i class="fas fa-calculator text-purple-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-4">Contabilidade</h3>
          <p class="text-gray-600 mb-6">
            Sistema contábil integrado com geração automática de lançamentos.
          </p>
          <ul class="space-y-3">
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Balancetes automáticos</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Demonstrações financeiras</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Conciliação bancária</span>
            </li>
          </ul>
        </div>
        
        <!-- Recursos Humanos -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
          <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mb-6">
            <i class="fas fa-users text-orange-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-4">Recursos Humanos</h3>
          <p class="text-gray-600 mb-6">
            Gestão completa de colaboradores, folha de pagamento e benefícios.
          </p>
          <ul class="space-y-3">
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Folha de pagamento</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Controle de férias</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Gestão de benefícios</span>
            </li>
          </ul>
        </div>
        
        <!-- Pauta Aduaneira -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
          <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mb-6">
            <i class="fas fa-file-invoice text-red-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-4">Pauta Aduaneira</h3>
          <p class="text-gray-600 mb-6">
            Consulta integrada à pauta aduaneira com atualizações automáticas.
          </p>
          <ul class="space-y-3">
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Consulta por NCM/SH</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Histórico de alterações</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Alertas de mudanças</span>
            </li>
          </ul>
        </div>
        
        <!-- Marketplace -->
        <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
          <div class="w-16 h-16 bg-teal-100 rounded-2xl flex items-center justify-center mb-6">
            <i class="fas fa-store text-teal-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-4">Marketplace</h3>
          <p class="text-gray-600 mb-6">
            Plataforma integrada para conexão entre clientes e fornecedores.
          </p>
          <ul class="space-y-3">
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Catálogo de serviços</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Solicitação de cotações</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-3"></i>
              <span>Avaliação de fornecedores</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Como Funciona -->
  <section class="section bg-white">
    <div class="container mx-auto px-4 lg:px-8">
      <div class="text-center mb-16" data-aos="fade-up">
        <span class="text-blue-600 font-semibold">COMO FUNCIONA</span>
        <h2 class="text-4xl font-bold text-gray-900 mt-4 mb-6">Implementação em 4 Passos Simples</h2>
      </div>
      
      <div class="max-w-4xl mx-auto">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="w-20 h-20 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i class="fas fa-user-plus text-blue-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-3">1. Cadastro</h3>
            <p class="text-gray-600">Crie sua conta e complete o perfil da empresa</p>
          </div>
          
          <div class="text-center" data-aos="fade-up" data-aos-delay="200">
            <div class="w-20 h-20 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i class="fas fa-cog text-green-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-3">2. Configuração</h3>
            <p class="text-gray-600">Personalize o sistema conforme suas necessidades</p>
          </div>
          
          <div class="text-center" data-aos="fade-up" data-aos-delay="300">
            <div class="w-20 h-20 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i class="fas fa-graduation-cap text-purple-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-3">3. Treinamento</h3>
            <p class="text-gray-600">Nossa equipe treina sua equipe gratuitamente</p>
          </div>
          
          <div class="text-center" data-aos="fade-up" data-aos-delay="400">
            <div class="w-20 h-20 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <i class="fas fa-rocket text-orange-600 text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-900 mb-3">4. Produção</h3>
            <p class="text-gray-600">Comece a usar o sistema com suporte 24/7</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Planos -->
  <section id="planos" class="section bg-gradient-to-b from-white to-blue-50">
    <div class="container mx-auto px-4 lg:px-8">
      <div class="text-center mb-16" data-aos="fade-up">
        <span class="text-blue-600 font-semibold">PLANOS E PREÇOS</span>
        <h2 class="text-4xl font-bold text-gray-900 mt-4 mb-6">Escolha o Plano Ideal para o Seu Negócio</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Oferecemos flexibilidade para empresas de todos os tamanhos. Teste grátis por 30 dias.
        </p>
      </div>
      
      <!-- Billing Toggle Modalidade de Pagamento-->
      <div class="flex justify-center mb-12" data-aos="fade-up">
        <div class="bg-gray-100 rounded-2xl p-1 inline-flex">
          <button id="monthlyToggle" class="px-6 py-3 rounded-xl font-semibold bg-blue-600 text-white">Mensal</button>
          <button id="SemestreToggle" class="px-6 py-3 rounded-xl font-semibold text-gray-700">Semestral <span class="text-blue-600">(1 meses grátis)</span></button>
          <button id="annualToggle" class="px-6 py-3 rounded-xl font-semibold text-gray-700">Anual <span class="text-green-600">(2 meses grátis)</span></button>
        </div>
      </div>
      
      <!-- Pricing Cards -->
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
          @php $delay = 100; @endphp
          @php use Illuminate\Support\Str; @endphp

          @foreach($planos as $pl)
              <div 
                  class="pricing-card {{ $pl->is_popular ? 'popular' : '' }}"
                  data-aos="fade-up"
                  data-aos-delay="{{ $delay }}"
              >
                  <div class="text-center mb-8">
                      <h3 class="text-2xl font-bold text-gray-900 mb-2">
                          {{ $pl->nome }}
                      </h3>

                      <p class="text-gray-600 mb-6">
                          {{ Str::limit($pl->descricao, 50) }}
                      </p>

                      {{-- Preço mensal --}}
                      <div class="price-monthly">
                          <div class="text-4xl font-bold text-gray-900">
                              {{ number_format($pl->preco_mensal, 2, ',', '.') }}
                              <span class="text-xl">AOA</span>
                          </div>
                          <div class="text-gray-500">por mês</div>
                      </div>

                      {{-- Preço semestral --}}
                      <div class="price-semestre hidden">
                          <div class="text-4xl font-bold text-gray-900">
                              {{ number_format($pl->preco_semestral, 2, ',', '.') }}
                              <span class="text-xl">AOA</span>
                          </div>
                          <div class="text-gray-500">
                              por semestre ({{ number_format($pl->preco_semestral / 6, 2, ',', '.') }} AOA/mês)
                          </div>
                      </div>

                      {{-- Preço anual --}}
                      <div class="price-annual hidden">
                          <div class="text-4xl font-bold text-gray-900">
                              {{ number_format($pl->preco_anual, 2, ',', '.') }}
                              <span class="text-xl">AOA</span>
                          </div>
                          <div class="text-gray-500">
                              por ano ({{ number_format($pl->preco_anual / 12, 2, ',', '.') }} AOA/mês)
                          </div>
                      </div>
                  </div>

                  {{-- Itens do plano --}}
                  <ul class="space-y-4 mb-8">
                      @foreach($pl->itemplano as $item)
                          <li class="flex items-center {{ $item->icon === 'fa-times' ? 'opacity-50' : '' }}">
                              <i class="fas {{ $item->icon }} {{ $item->text_color }} mr-3"></i>
                              <span>{{ $item->item }}</span>
                          </li>
                      @endforeach
                  </ul>

                  {{-- Botão --}}
                  <form method="GET" action="{{ route('register', $pl->id) }}">
                      <input type="hidden" name="modalidade" class="billing-cycle" value="monthly">
                      <input type="hidden" name="plano" class="" value="{{$pl->id}}">
                      <button type="submit" class="w-full {{ $pl->is_free ? 'btn-outline' : 'btn-primary' }}">
                        {{ $pl->is_free ? 'Começar Grátis' : 'Escolher Plano' }}
                      </button>
                  </form>
              </div>

              @php $delay += 100; @endphp
          @endforeach
      </div>

    </div>
  </section>

  <!-- Clientes -->
  <section id="clientes" class="section bg-white">
    <div class="container mx-auto px-4 lg:px-8">
      <div class="text-center mb-16" data-aos="fade-up">
        <span class="text-blue-600 font-semibold">NOSSOS CLIENTES</span>
        <h2 class="text-4xl font-bold text-gray-900 mt-4 mb-6">Empresas que confiam na Logigate</h2>
      </div>
      
      <!-- Testimonials -->
      <div class="max-w-4xl mx-auto mb-16" data-aos="fade-up">
        <div class="swiper testimonialsSwiper">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <div class="testimonial-card">
                <div class="flex items-center mb-6">
                  <div class="w-16 h-16 rounded-full overflow-hidden mr-4">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" 
                         alt="Cliente" class="w-full h-full object-cover">
                  </div>
                  <div>
                    <h4 class="font-bold text-gray-900">Carlos Mendes</h4>
                    <p class="text-gray-600">CEO - TransLog Angola</p>
                  </div>
                </div>
                <p class="text-gray-700 italic text-lg">
                  "A Logigate transformou completamente nossa operação. Reduzimos o tempo de processamento em 60% e eliminamos erros manuais. A equipe de suporte é excepcional!"
                </p>
              </div>
            </div>
            
            <div class="swiper-slide">
              <div class="testimonial-card">
                <div class="flex items-center mb-6">
                  <div class="w-16 h-16 rounded-full overflow-hidden mr-4">
                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" 
                         alt="Cliente" class="w-full h-full object-cover">
                  </div>
                  <div>
                    <h4 class="font-bold text-gray-900">Ana Silva</h4>
                    <p class="text-gray-600">Gerente de Logística - GlobalTrade</p>
                  </div>
                </div>
                <p class="text-gray-700 italic text-lg">
                  "A integração com a pauta aduaneira automatizada nos poupa horas de trabalho manual todos os dias. O retorno sobre o investimento foi imediato."
                </p>
              </div>
            </div>
            
            <div class="swiper-slide">
              <div class="testimonial-card">
                <div class="flex items-center mb-6">
                  <div class="w-16 h-16 rounded-full overflow-hidden mr-4">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" 
                         alt="Cliente" class="w-full h-full object-cover">
                  </div>
                  <div>
                    <h4 class="font-bold text-gray-900">João Pereira</h4>
                    <p class="text-gray-600">Diretor Financeiro - Cargo Express</p>
                  </div>
                </div>
                <p class="text-gray-700 italic text-lg">
                  "O sistema contábil integrado nos permite ter um controle financeiro muito mais preciso. As demonstrações financeiras são geradas automaticamente."
                </p>
              </div>
            </div>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
      
      <!-- Client Logos -->
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8 items-center" data-aos="fade-up">
        <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
          <span class="text-gray-700 font-bold text-xl">CLIENTE 1</span>
        </div>
        <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
          <span class="text-gray-700 font-bold text-xl">CLIENTE 2</span>
        </div>
        <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
          <span class="text-gray-700 font-bold text-xl">CLIENTE 3</span>
        </div>
        <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
          <span class="text-gray-700 font-bold text-xl">CLIENTE 4</span>
        </div>
        <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
          <span class="text-gray-700 font-bold text-xl">CLIENTE 5</span>
        </div>
        <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center h-24">
          <span class="text-gray-700 font-bold text-xl">CLIENTE 6</span>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="section bg-gradient-to-r from-blue-600 to-blue-800 text-white relative overflow-hidden">
    <div class="absolute inset-0">
      <div class="absolute top-10 left-10 w-64 h-64 bg-white/10 rounded-full"></div>
      <div class="absolute bottom-10 right-10 w-96 h-96 bg-white/5 rounded-full"></div>
    </div>
    
    <div class="container mx-auto px-4 lg:px-8 relative z-10">
      <div class="max-w-4xl mx-auto text-center" data-aos="fade-up">
        <h2 class="text-4xl font-bold mb-6">Pronto para transformar sua operação aduaneira?</h2>
        <p class="text-xl mb-10 opacity-90">
          Experimente grátis por 31 dias. Sem compromisso, sem cartão de crédito.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="#planos" class="btn-primary bg-white text-blue-600 hover:bg-gray-100 text-lg">
            <i class="fas fa-play-circle mr-2"></i>Começar Teste Grátis
          </a>
          <a href="#demo" class="btn-outline border-white text-white hover:bg-white/10 text-lg">
            <i class="fas fa-calendar-alt mr-2"></i>Agendar Demonstração
          </a>
        </div>
        
        <p class="mt-8 text-sm opacity-80">
          <i class="fas fa-check-circle mr-2"></i>Suporte incluído durante o teste
          <i class="fas fa-check-circle mx-4"></i>Dados protegidos e seguros
          <i class="fas fa-check-circle mx-4"></i>Cancelamento a qualquer momento
        </p>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="section bg-gray-50">
    <div class="container mx-auto px-4 lg:px-8">
      <div class="text-center mb-16" data-aos="fade-up">
        <span class="text-blue-600 font-semibold">PERGUNTAS FREQUENTES</span>
        <h2 class="text-4xl font-bold text-gray-900 mt-4 mb-6">Tire suas dúvidas</h2>
      </div>
      
      <div class="max-w-3xl mx-auto" data-aos="fade-up">
        <div class="space-y-6">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <button class="faq-question w-full text-left p-6 flex justify-between items-center">
              <span class="font-semibold text-gray-900 text-lg">Como funciona o período de teste?</span>
              <i class="fas fa-chevron-down text-blue-600"></i>
            </button>
            <div class="faq-answer p-6 pt-0 hidden">
              <p class="text-gray-600">
                Oferecemos 14 dias gratuitos com acesso completo a todas as funcionalidades. Não solicitamos cartão de crédito e você pode cancelar a qualquer momento durante o período de teste.
              </p>
            </div>
          </div>
          
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <button class="faq-question w-full text-left p-6 flex justify-between items-center">
              <span class="font-semibold text-gray-900 text-lg">A Logigate atende às normas aduaneiras angolanas?</span>
              <i class="fas fa-chevron-down text-blue-600"></i>
            </button>
            <div class="faq-answer p-6 pt-0 hidden">
              <p class="text-gray-600">
                Sim! Nossa plataforma é desenvolvida especificamente para o mercado angolano e está sempre atualizada com as últimas regulamentações da Alfândega de Angola. Temos uma equipe dedicada que monitora as mudanças legislativas.
              </p>
            </div>
          </div>
          
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <button class="faq-question w-full text-left p-6 flex justify-between items-center">
              <span class="font-semibold text-gray-900 text-lg">Preciso de treinamento para usar o sistema?</span>
              <i class="fas fa-chevron-down text-blue-600"></i>
            </button>
            <div class="faq-answer p-6 pt-0 hidden">
              <p class="text-gray-600">
                Oferecemos treinamento gratuito para todos os planos. Para planos Empresarial e Customizado, incluímos treinamento presencial e materiais personalizados. O sistema é intuitivo, mas garantimos que sua equipe estará totalmente capacitada.
              </p>
            </div>
          </div>
          
          <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <button class="faq-question w-full text-left p-6 flex justify-between items-center">
              <span class="font-semibold text-gray-900 text-lg">Como é feito o suporte técnico?</span>
              <i class="fas fa-chevron-down text-blue-600"></i>
            </button>
            <div class="faq-answer p-6 pt-0 hidden">
              <p class="text-gray-600">
                Oferecemos múltiplos canais de suporte: email, chat online, telefone e WhatsApp. Para planos superiores, oferecemos suporte 24/7 com SLA de resposta garantido. Temos uma equipe de especialistas aduaneiros pronta para ajudar.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contactos -->
  <section id="contactos" class="section bg-white">
    <div class="container mx-auto px-4 lg:px-8">
      <div class="grid lg:grid-cols-2 gap-12">
        <!-- Contact Info -->
        <div data-aos="fade-right">
          <h2 class="text-4xl font-bold text-gray-900 mb-6">Entre em Contacto</h2>
          <p class="text-xl text-gray-600 mb-10">
            Tem dúvidas ou precisa de uma solução personalizada? Nossa equipe está pronta para ajudar.
          </p>
          
          <div class="space-y-8">
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-map-marker-alt text-blue-600"></i>
              </div>
              <div>
                <h4 class="font-bold text-gray-900 mb-2">Endereço</h4>
                <p class="text-gray-600">Rua Amilcar Cabral nº 66<br>Luanda, Ingombota<br>Angola</p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-phone-alt text-green-600"></i>
              </div>
              <div>
                <h4 class="font-bold text-gray-900 mb-2">Telefone</h4>
                <p class="text-gray-600">
                  <a href="tel:+244948242262" class="hover:text-blue-600">+244 948 242 262</a><br>
                  Segunda a Sexta: 8h às 18h
                </p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4">
              <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-envelope text-purple-600"></i>
              </div>
              <div>
                <h4 class="font-bold text-gray-900 mb-2">Email</h4>
                <p class="text-gray-600">
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
              <a href="#" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-blue-100 transition-colors">
                <i class="fab fa-facebook-f text-gray-700"></i>
              </a>
              <a href="#" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-blue-100 transition-colors">
                <i class="fab fa-linkedin-in text-gray-700"></i>
              </a>
              <a href="#" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-blue-100 transition-colors">
                <i class="fab fa-instagram text-gray-700"></i>
              </a>
              <a href="#" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-blue-100 transition-colors">
                <i class="fab fa-youtube text-gray-700"></i>
              </a>
            </div>
          </div>
        </div>
        
        <!-- Contact Form -->
        <div data-aos="fade-left" class="bg-white rounded-3xl shadow-xl p-8">
          <h3 class="text-2xl font-bold text-gray-900 mb-6">Envie-nos uma mensagem</h3>
          
          <form id="contactForm" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label class="block text-gray-700 mb-2">Nome completo *</label>
                <input type="text" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
              </div>
              <div>
                <label class="block text-gray-700 mb-2">Empresa</label>
                <input type="text" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
              </div>
            </div>
            
            <div>
              <label class="block text-gray-700 mb-2">Email *</label>
              <input type="email" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
            </div>
            
            <div>
              <label class="block text-gray-700 mb-2">Telefone *</label>
              <input type="tel" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
            </div>
            
            <div>
              <label class="block text-gray-700 mb-2">Assunto *</label>
              <select class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                <option value="">Selecione um assunto</option>
                <option>Demonstração do sistema</option>
                <option>Informações sobre planos</option>
                <option>Suporte técnico</option>
                <option>Parcerias</option>
                <option>Outro</option>
              </select>
            </div>
            
            <div>
              <label class="block text-gray-700 mb-2">Mensagem *</label>
              <textarea rows="4" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"></textarea>
            </div>
            
            <button type="submit" class="btn-primary w-full text-lg">
              <i class="fas fa-paper-plane mr-2"></i>Enviar Mensagem
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-white py-12">
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
            Transformando a gestão aduaneira em Angola com tecnologia de ponta e inovação constante.
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
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Status do Sistema</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Política de Privacidade</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-bold text-lg mb-6">Newsletter</h4>
          <p class="text-gray-400 mb-4">
            Receba as últimas novidades sobre legislação aduaneira e atualizações do sistema.
          </p>
          <form class="flex">
            <input type="email" placeholder="Seu email" class="px-4 py-3 rounded-l-xl w-full text-gray-900 outline-none">
            <button type="submit" class="bg-blue-600 px-4 rounded-r-xl hover:bg-blue-700 transition-colors">
              <i class="fas fa-paper-plane"></i>
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

    mobileMenuButton.addEventListener('click', () => {
      mobileMenu.classList.add('active');
      document.body.style.overflow = 'hidden';
    });

    closeMobileMenu.addEventListener('click', () => {
      mobileMenu.classList.remove('active');
      document.body.style.overflow = 'auto';
    });

    // Close mobile menu when clicking on links
    document.querySelectorAll('#mobileMenu a').forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
        document.body.style.overflow = 'auto';
      });
    });

    // Pricing Toggle
const monthlyToggle   = document.getElementById('monthlyToggle');
const semestreToggle  = document.getElementById('SemestreToggle'); // mantém o ID do HTML
const annualToggle    = document.getElementById('annualToggle');

const monthlyPrices   = document.querySelectorAll('.price-monthly');
const semestrePrices  = document.querySelectorAll('.price-semestre');
const annualPrices    = document.querySelectorAll('.price-annual');

// Todos os inputs hidden dos formulários
const cycleInputs = document.querySelectorAll('.billing-cycle');

function setCycle(cycle) {
    cycleInputs.forEach(input => {
        input.value = cycle;
    });
}

// Estado inicial
setCycle('monthly');

// Mensal
monthlyToggle.addEventListener('click', () => {
    monthlyToggle.classList.add('bg-blue-600', 'text-white');
    semestreToggle.classList.remove('bg-blue-600', 'text-white');
    annualToggle.classList.remove('bg-blue-600', 'text-white');

    monthlyPrices.forEach(el => el.classList.remove('hidden'));
    semestrePrices.forEach(el => el.classList.add('hidden'));
    annualPrices.forEach(el => el.classList.add('hidden'));

    setCycle('monthly');
});

// Semestral
semestreToggle.addEventListener('click', () => {
    semestreToggle.classList.add('bg-blue-600', 'text-white');
    monthlyToggle.classList.remove('bg-blue-600', 'text-white');
    annualToggle.classList.remove('bg-blue-600', 'text-white');

    semestrePrices.forEach(el => el.classList.remove('hidden'));
    monthlyPrices.forEach(el => el.classList.add('hidden'));
    annualPrices.forEach(el => el.classList.add('hidden'));

    setCycle('semestral');
});

// Anual
annualToggle.addEventListener('click', () => {
    annualToggle.classList.add('bg-blue-600', 'text-white');
    monthlyToggle.classList.remove('bg-blue-600', 'text-white');
    semestreToggle.classList.remove('bg-blue-600', 'text-white');

    annualPrices.forEach(el => el.classList.remove('hidden'));
    monthlyPrices.forEach(el => el.classList.add('hidden'));
    semestrePrices.forEach(el => el.classList.add('hidden'));

    setCycle('annual');
});


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
        const answer = button.nextElementSibling;
        const icon = button.querySelector('i');
        
        // Toggle current answer
        answer.classList.toggle('hidden');
        icon.classList.toggle('fa-chevron-down');
        icon.classList.toggle('fa-chevron-up');
        
        // Close other answers
        document.querySelectorAll('.faq-question').forEach(otherButton => {
          if (otherButton !== button) {
            const otherAnswer = otherButton.nextElementSibling;
            const otherIcon = otherButton.querySelector('i');
            otherAnswer.classList.add('hidden');
            otherIcon.classList.remove('fa-chevron-up');
            otherIcon.classList.add('fa-chevron-down');
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

    // Form submission
    document.getElementById('contactForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Simple validation
      const requiredFields = this.querySelectorAll('[required]');
      let isValid = true;
      
      requiredFields.forEach(field => {
        if (!field.value.trim()) {
          isValid = false;
          field.classList.add('border-red-500');
        } else {
          field.classList.remove('border-red-500');
        }
      });
      
      if (isValid) {
        // Here you would typically send the form data to your server
        alert('Mensagem enviada com sucesso! Entraremos em contacto em breve.');
        this.reset();
      } else {
        alert('Por favor, preencha todos os campos obrigatórios.');
      }
    });

    // Navbar scroll effect
    window.addEventListener('scroll', () => {
      const navbar = document.querySelector('nav');
      if (window.scrollY > 100) {
        navbar.classList.add('shadow-lg');
      } else {
        navbar.classList.remove('shadow-lg');
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