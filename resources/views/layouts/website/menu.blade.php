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
          <a href="{{ route('consultar.pauta') }}" class="hover:text-blue-300 transition-all flex items-center space-x-2 underline-effect">
            <i class="fas fa-file-alt" aria-hidden="true"></i> <!-- Ícone do Font Awesome -->
            <span>Pauta Aduaneira</span>
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