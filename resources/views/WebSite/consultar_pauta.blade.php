<!DOCTYPE html>
<html lang="pt-AO">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- SEO Optimized -->
  <title>Logigate | Consulta Pauta Aduaneira - Angola</title>
  <meta name="description" content="Consulte a pauta aduaneira angolana de forma rápida e eficiente. Pesquise por código NCM/SH, descrição e obtenha informações detalhadas sobre impostos e requisitos.">
  <meta name="keywords" content="pauta aduaneira Angola, consulta NCM, classificação fiscal, impostos importação, código SH, despacho aduaneiro">
  
  <!-- Open Graph -->
  <meta property="og:title" content="Logigate - Consulta Pauta Aduaneira">
  <meta property="og:description" content="Consulte gratuitamente a pauta aduaneira angolana">
  <meta property="og:image" content="https://aduaneiro.hongayetu.com/images/og-pauta.jpg">
  <meta property="og:url" content="https://aduaneiro.hongayetu.com/consultar/pauta">
  
  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Logigate - Pauta Aduaneira Angola">
  <meta name="twitter:description" content="Consulta gratuita da pauta aduaneira">
  
  <!-- Favicon -->
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <meta name="theme-color" content="#0047AB">
  
  <!-- Preload Critical Resources -->
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" as="style">
  <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style">
  
  <!-- CSS -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
      color: #1E293B;
      overflow-x: hidden;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    
    .glass-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
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
    
    .float-animation {
      animation: float 6s ease-in-out infinite;
    }
    
    /* Search Box */
    .search-box {
      background: white;
      border-radius: 60px;
      padding: 8px;
      box-shadow: 0 20px 40px rgba(0, 71, 171, 0.15);
      transition: all 0.3s ease;
    }
    
    .search-box:focus-within {
      box-shadow: 0 20px 60px rgba(0, 71, 171, 0.3);
      transform: translateY(-2px);
    }
    
    .search-input {
      border: none;
      outline: none;
      padding: 16px 24px;
      font-size: 1.1rem;
      width: 100%;
      background: transparent;
    }
    
    .search-button {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white;
      border: none;
      padding: 16px 32px;
      border-radius: 50px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      white-space: nowrap;
    }
    
    .search-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0, 71, 171, 0.3);
    }
    
    /* Result Card */
    .result-card {
      background: white;
      border-radius: 20px;
      padding: 24px;
      margin-bottom: 16px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      cursor: pointer;
      border: 1px solid rgba(0, 71, 171, 0.1);
    }
    
    .result-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 60px rgba(0, 71, 171, 0.15);
      border-color: var(--primary);
    }
    
    /* Tag de Imposto */
    .tax-tag {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      display: inline-block;
    }
    
    /* Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }
    
    .modal.active {
      display: flex;
    }
    
    .modal-content {
      background: white;
      border-radius: 30px;
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      animation: slideUp 0.3s ease;
    }
    
    @keyframes slideUp {
      from {
        transform: translateY(50px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
    
    /* Loading Spinner */
    .spinner {
      border: 3px solid rgba(0, 71, 171, 0.1);
      border-top: 3px solid var(--primary);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    /* Quick Filters */
    .filter-chip {
      background: white;
      padding: 8px 20px;
      border-radius: 30px;
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--dark);
      cursor: pointer;
      transition: all 0.3s ease;
      border: 1px solid rgba(0, 71, 171, 0.2);
    }
    
    .filter-chip:hover {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
      transform: translateY(-2px);
    }
    
    .filter-chip.active {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }
    
    /* Navigation */
    nav {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
    }
    
    /* Mobile Menu */
    .mobile-menu {
      transform: translateX(100%);
      transition: transform 0.3s ease;
    }
    
    .mobile-menu.active {
      transform: translateX(0);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .search-box {
        border-radius: 30px;
      }
      
      .search-button {
        padding: 12px 24px;
      }
    }
    
    /* Particles Background */
    #particles-js {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }
    
    /* Breadcrumb */
    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.9rem;
    }
    
    .breadcrumb a {
      color: white;
      text-decoration: none;
    }
    
    .breadcrumb a:hover {
      text-decoration: underline;
    }
    
    /* Estatísticas Cards */
    .stat-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      padding: 20px;
      text-align: center;
      color: white;
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 4px;
    }
    
    .stat-label {
      font-size: 0.9rem;
      opacity: 0.9;
    }
    
    /* Skip to content link */
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
  @vite(['resources/js/pauta-marketplace.js'])
</head>
<body class="pauta-page">
  <!-- Skip to content link -->
  <a href="#main-content" class="skip-link">Saltar para o conteúdo principal</a>

  <!-- Particles Background -->
  <div id="particles-js"></div>

  <!-- Navigation -->
  <nav class="fixed w-full z-50 transition-all duration-300" aria-label="Navegação principal">
    <div class="container mx-auto px-4 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
          <div class="group">
            <img src="{{ asset('dist/img/LOGIGATE.png') }}" alt="LogiGate - Sistema Aduaneiro Inteligente" 
              style="opacity: .8; max-width: 70px;" class="hidden md:block group-hover:animate-spin transition-all duration-300" >
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Logi<span class="text-blue-600">Gate</span></h1>
            <p class="text-xs text-gray-600">Pauta Aduaneira</p>
          </div>
        </div>

        <!-- Desktop Menu -->
        <div class="hidden lg:flex items-center space-x-8">
          <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
            <i class="fas fa-home mr-1"></i>Início
          </a>
          <a href="{{ route('consultar.pauta') }}" class="text-blue-600 font-medium transition-colors">
            <i class="fas fa-file-alt mr-1"></i>Pauta Aduaneira
          </a>
          <a href="{{ route('marketplace') }}" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
            <i class="fas fa-store mr-1"></i>Marketplace
          </a>
          <a href="{{ route('consultar.licenciamento') }}" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
            <i class="fas fa-file-signature mr-1"></i>Licenciamento
          </a>
          
          <div class="flex items-center space-x-4">
            @if (Route::has('login'))
              @auth
                <a href="{{ url('/dashboard') }}" class="btn-outline text-sm">
                  <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                </a>
              @else
                <a href="{{ route('login') }}" class="btn-outline text-sm">
                  <i class="fas fa-sign-in-alt mr-1"></i>Acesso
                </a>
                <a href="{{ route('register') }}" class="btn-primary text-sm">
                  <i class="fas fa-user-plus mr-1"></i>Registar
                </a>
              @endauth
            @endif
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
        <a href="{{ route('home') }}" class="block text-gray-700 hover:text-blue-600 font-medium text-lg">
          <i class="fas fa-home mr-2"></i>Início
        </a>
        <a href="{{ route('consultar.pauta') }}" class="block text-blue-600 font-medium text-lg">
          <i class="fas fa-file-alt mr-2"></i>Pauta Aduaneira
        </a>
        <a href="{{ route('marketplace') }}" class="block text-gray-700 hover:text-blue-600 font-medium text-lg">
          <i class="fas fa-store mr-2"></i>Marketplace
        </a>
        <a href="{{ route('consultar.licenciamento') }}" class="block text-gray-700 hover:text-blue-600 font-medium text-lg">
          <i class="fas fa-file-signature mr-2"></i>Licenciamento
        </a>
        
        <div class="pt-8 space-y-4">
          @if (Route::has('login'))
            @auth
              <a href="{{ url('/dashboard') }}" class="btn-outline w-full block text-center">
                <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
              </a>
            @else
              <a href="{{ route('login') }}" class="btn-outline w-full block text-center">
                <i class="fas fa-sign-in-alt mr-1"></i>Acesso
              </a>
              <a href="{{ route('register') }}" class="btn-primary w-full block text-center">
                <i class="fas fa-user-plus mr-1"></i>Registar
              </a>
            @endauth
          @endif
        </div>
      </div>
    </div>
  </nav>

  <main id="main-content">
    @include('WebSite.partials.pauta-content')
  </main>

  <!-- Detail Modal -->
  <div id="detailModal" class="modal @if($selection) active @endif" role="dialog" aria-modal="true" aria-labelledby="modalTitle" data-details-endpoint="{{ url('/api/v1/pauta/detalhes') }}">
    <div class="modal-content">
      <div class="p-8">
        <div class="flex justify-between items-start mb-6">
          <h2 class="text-2xl font-bold text-gray-900" id="modalTitle" tabindex="-1">{{ $selection ? 'Código: '.$selection['codigo'] : '' }}</h2>
          <a data-pauta-close href="{{ route('consultar.pauta', \Illuminate\Support\Arr::only($filters, ['q','tipo','page','per_page'])) }}" aria-label="Fechar detalhes" class="text-gray-500 hover:text-gray-700 transition">
            <span aria-hidden="true">×</span>
          </a>
        </div>
        <div class="pauta-detail-grid">
          <div id="modalContent" class="space-y-6">@include('WebSite.partials.pauta-detail')</div>
          <aside id="pauta-marketplace-guide" class="pauta-guide" @if(!$directory) hidden @endif aria-live="polite" data-endpoint="{{ route('marketplace.guide') }}" data-marketplace="{{ route('marketplace') }}">
            @if($directory)
              <h3>Despachantes com experiência nesta mercadoria</h3>
              @if($directory['error'] ?? false)<p>Não foi possível consultar o histórico público. Pode tentar novamente ou explorar o marketplace.</p>@endif
              @forelse($directory['profiles'] as $profile)
                <article><h4>{{ $profile->public_name }}</h4><p>{{ $profile->public_location ?: 'Localização pública não indicada' }}</p><p>{{ $profile->operations !== null ? 'Código exacto · actividade em '.$profile->active_months.' meses · '.$profile->operations.' processos concluídos.' : 'Especialidade declarada. Sem histórico público disponível neste período.' }}</p></article>
              @empty @unless($directory['error'] ?? false)<p>Sem histórico público autorizado disponível. Isso não significa ausência de experiência profissional.</p>@endunless @endforelse
              @php
                $guideContext = \Illuminate\Support\Arr::only($filters, ['months','location','recurrence']);
                foreach (['q','tipo','page','per_page'] as $key) { if (isset($filters[$key]) && $filters[$key] !== '') $guideContext['pauta_'.$key] = $filters[$key]; }
              @endphp
              <a href="{{ route('marketplace', array_merge($guideContext, ['pauta_id' => $selection['id'], 'codigo' => $selection['codigo'], 'mercadoria_descricao' => $selection['descricao']])) }}">Ver todos no marketplace →</a>
              <p>Histórico registado no LogiGate de {{ $directory['start']->format('d/m/Y') }} a {{ $directory['end']->copy()->subDay()->format('d/m/Y') }}. O guia não valida oficialmente a classificação pautal nem garante prazos.</p>
            @endif
          </aside>
        </div>
        <noscript><p>A consulta e a pesquisa estão disponíveis sem JavaScript.</p></noscript>
      </div>
    </div>
  </div>

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
              <p class="text-gray-400 text-sm">Pauta Aduaneira Angola</p>
            </div>
          </div>
          <p class="text-gray-400">
            Consulta gratuita da pauta aduaneira angolana. Informações atualizadas conforme legislação vigente.
          </p>
        </div>
        
        <div>
          <h4 class="font-bold text-lg mb-6">Links Rápidos</h4>
          <ul class="space-y-3">
            <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition-colors">Início</a></li>
            <li><a href="{{ route('consultar.pauta') }}" class="text-gray-400 hover:text-white transition-colors">Pauta Aduaneira</a></li>
            <li><a href="{{ route('marketplace') }}" class="text-gray-400 hover:text-white transition-colors">Marketplace</a></li>
            <li><a href="#contactos" class="text-gray-400 hover:text-white transition-colors">Contactos</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-bold text-lg mb-6">Legal</h4>
          <ul class="space-y-3">
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Termos de Uso</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Política de Privacidade</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Aviso Legal</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-bold text-lg mb-6">Suporte</h4>
          <ul class="space-y-3">
            <li class="flex items-center">
              <i class="fas fa-phone-alt text-blue-500 mr-3"></i>
              <a href="tel:+244948242262" class="text-gray-400 hover:text-white">+244 948 242 262</a>
            </li>
            <li class="flex items-center">
              <i class="fas fa-envelope text-blue-500 mr-3"></i>
              <a href="mailto:geral@hongayetu.com" class="text-gray-400 hover:text-white">geral@hongayetu.com</a>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="border-t border-gray-800 pt-8">
        <p class="text-center text-gray-400">
          &copy; 2024 Logigate by Hongayetu LDA. Todos os direitos reservados.
        </p>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  
  <script>
    // Initialize AOS
    const pautaReducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (window.AOS && !pautaReducedMotion) AOS.init({
      duration: 1000,
      once: true,
      offset: 100
    });
    else document.documentElement.classList.add('pauta-static');

    // Optional visual libraries must not block the existing public search.
    if (typeof particlesJS === 'function' && !pautaReducedMotion) particlesJS('particles-js', {
      particles: {
        number: { value: 80, density: { enable: true, value_area: 800 } },
        color: { value: "#ffffff" },
        shape: { type: "circle" },
        opacity: { value: 0.5, random: false },
        size: { value: 3, random: true },
        line_linked: { enable: true, distance: 150, color: "#ffffff", opacity: 0.4, width: 1 },
        move: { enable: true, speed: 6, direction: "none", random: false, straight: false, out_mode: "out" }
      },
      interactivity: {
        detect_on: "canvas",
        events: { onhover: { enable: true, mode: "repulse" }, onclick: { enable: true, mode: "push" } },
        modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
      },
      retina_detect: true
    });

    // API Configuration
    const API_BASE = '/api/v1';

    // Mobile Menu
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

      document.querySelectorAll('#mobileMenu a').forEach(link => {
        link.addEventListener('click', () => {
          mobileMenu.classList.remove('active');
          mobileMenu.setAttribute('hidden', '');
          mobileMenuButton.setAttribute('aria-expanded', 'false');
          document.body.style.overflow = 'auto';
        });
      });
    }

  </script>
</body>
</html>
