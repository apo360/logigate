{{-- resources/views/marketplace/index.blade.php --}}
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace Logigate - Encontre Despachantes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0047AB;
            --primary-dark: #003580;
            --secondary: #00B4D8;
        }
        
        .despachante-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,71,171,0.1);
        }
        
        .despachante-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0,71,171,0.15);
            border-color: var(--primary);
        }
        
        .verificado-badge {
            background: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .especialidade-tag {
            background: #f0f9ff;
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .search-box {
            background: white;
            border-radius: 60px;
            padding: 8px;
            box-shadow: 0 20px 40px rgba(0,71,171,0.15);
        }
        
        .search-input {
            border: none;
            outline: none;
            padding: 16px 24px;
            font-size: 1.1rem;
            width: 100%;
            background: transparent;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header (igual) -->
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

    <main class="pt-24">
        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto text-center">
                    <h1 class="text-4xl md:text-5xl font-bold mb-6">
                        Encontre o <span class="text-yellow-300">Despachante</span> Ideal
                    </h1>
                    <p class="text-xl mb-8 opacity-90">
                        Conectamos importadores e exportadores aos melhores despachantes aduaneiros de Angola
                    </p>
                    
                    <!-- Search -->
                    <div class="max-w-2xl mx-auto search-box flex flex-col md:flex-row">
                        <input type="text" 
                               id="searchInput"
                               placeholder="Pesquisar por nome, especialidade ou localização..." 
                               class="search-input flex-grow">
                        <button id="searchBtn" class="bg-blue-600 text-white px-8 py-3 rounded-full hover:bg-blue-700 transition m-2 md:m-0">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filtros Rápidos -->
        <section class="py-12">
            <div class="container mx-auto px-4">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Filtrar por Especialidade</h2>
                <div class="flex flex-wrap gap-3" id="filtros">
                    <button class="filter-chip active" data-filtro="todos">Todos</button>
                    <button class="filter-chip" data-filtro="eletronicos">📱 Eletrônicos</button>
                    <button class="filter-chip" data-filtro="alimentos">🥩 Alimentos</button>
                    <button class="filter-chip" data-filtro="maquinas">⚙️ Máquinas</button>
                    <button class="filter-chip" data-filtro="veiculos">🚗 Veículos</button>
                    <button class="filter-chip" data-filtro="farmaceuticos">💊 Farmacêuticos</button>
                </div>
            </div>
        </section>

        <!-- Lista de Despachantes -->
        <section class="py-12">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">
                        Despachantes em Angola
                    </h2>
                    <select id="orderBy" class="border rounded-lg px-4 py-2">
                        <option value="relevancia">Mais Relevantes</option>
                        <option value="avaliacao">Melhor Avaliação</option>
                        <option value="experiencia">Mais Experientes</option>
                    </select>
                </div>
                
                <div id="despachantes" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Cards serão inseridos via JS -->
                </div>
                
                <div class="text-center mt-8">
                    <button id="loadMore" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition">
                        Carregar Mais
                    </button>
                </div>
            </div>
        </section>

        <!-- Como Funciona -->
        <section class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Como Conectar com Despachantes</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-search text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="font-bold text-xl mb-2">1. Pesquise</h3>
                        <p class="text-gray-600">Encontre despachantes por especialidade, localização ou nome</p>
                    </div>
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-envelope text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="font-bold text-xl mb-2">2. Contacte</h3>
                        <p class="text-gray-600">Envie uma mensagem diretamente pelo sistema</p>
                    </div>
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-star text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="font-bold text-xl mb-2">3. Avalie</h3>
                        <p class="text-gray-600">Deixe sua avaliação após o serviço prestado</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal de Contacto -->
    <div id="contactModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start mb-6">
                <h3 class="text-2xl font-bold" id="modalDespachanteNome"></h3>
                <button onclick="fecharModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form id="contactForm" class="space-y-4">
                <input type="hidden" id="despachanteId">
                
                <div>
                    <label class="block font-medium mb-2">Mensagem</label>
                    <textarea id="mensagem" rows="5" required 
                              class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500"
                              placeholder="Descreva sua necessidade..."></textarea>
                </div>
                
                <button type="submit" class="w-full btn-primary">
                    Enviar Mensagem
                </button>
            </form>
        </div>
    </div>

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

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();

        const API_BASE = '/api/v1/marketplace';

        // Carregar despachantes
        async function loadDespachantes(filtro = 'todos', page = 1) {
            try {
                let url = `${API_BASE}/despachantes?page=${page}`;
                if (filtro !== 'todos') {
                    url += `&especialidade=${filtro}`;
                }
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    displayDespachantes(data.data);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }

        function displayDespachantes(despachantes) {
            const container = document.getElementById('despachantes');
            
            container.innerHTML = despachantes.map(d => `
                <div class="despachante-card">
                    <div class="flex items-start mb-4">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            ${d.logotipo ? 
                                `<img src="${d.logotipo}" class="w-full h-full rounded-full object-cover">` : 
                                `<span class="text-2xl font-bold text-blue-600">${d.nome_empresa?.charAt(0) || d.user?.name?.charAt(0)}</span>`
                            }
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-xl font-bold">${d.nome_empresa || d.user?.name}</h3>
                                ${d.verificado ? '<span class="verificado-badge"><i class="fas fa-check-circle mr-1"></i>Verificado</span>' : ''}
                            </div>
                            <div class="flex items-center text-yellow-500 mb-2">
                                ${'★'.repeat(Math.round(d.media_avaliacoes))}
                                ${'☆'.repeat(5 - Math.round(d.media_avaliacoes))}
                                <span class="text-gray-500 text-sm ml-2">(${d.total_avaliacoes} avaliações)</span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-gray-600 mb-4">${d.descricao.substring(0, 120)}...</p>
                    
                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-map-marker-alt text-blue-600 w-5"></i>
                            <span>${d.areas_atuacao?.join(', ') || 'Angola'}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-tag text-blue-600 w-5"></i>
                            <div class="flex flex-wrap gap-2">
                                ${d.especialidades?.slice(0,3).map(esp => 
                                    `<span class="especialidade-tag">${esp}</span>`
                                ).join('')}
                                ${d.especialidades?.length > 3 ? `<span class="especialidade-tag">+${d.especialidades.length-3}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button onclick="verPerfil(${d.id})" 
                                class="flex-1 btn-outline text-sm">
                            Ver Perfil
                        </button>
                        <button onclick="abrirContacto(${d.id}, '${d.nome_empresa || d.user?.name}')" 
                                class="flex-1 btn-primary text-sm">
                            Contactar
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function verPerfil(id) {
            window.location.href = `/marketplace/despachante/${id}`;
        }

        function abrirContacto(id, nome) {
            @guest
                window.location.href = "{{ route('login') }}";
                return;
            @endguest
            
            document.getElementById('despachanteId').value = id;
            document.getElementById('modalDespachanteNome').textContent = nome;
            document.getElementById('contactModal').classList.add('flex');
        }

        function fecharModal() {
            document.getElementById('contactModal').classList.remove('flex');
        }

        // Filtros
        document.querySelectorAll('.filter-chip').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-chip').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                loadDespachantes(this.dataset.filtro);
            });
        });

        // Search
        document.getElementById('searchBtn').addEventListener('click', async () => {
            const termo = document.getElementById('searchInput').value;
            if (termo.length < 2) return;
            
            try {
                const response = await fetch(`${API_BASE}/despachantes/busca?q=${encodeURIComponent(termo)}`);
                const data = await response.json();
                if (data.success) displayDespachantes(data.data);
            } catch (error) {
                console.error(error);
            }
        });

        // Contact Form
        document.getElementById('contactForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const despachanteId = document.getElementById('despachanteId').value;
            const mensagem = document.getElementById('mensagem').value;
            
            try {
                const response = await fetch(`${API_BASE}/despachantes/${despachanteId}/contactar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ mensagem })
                });
                
                if (response.ok) {
                    alert('Mensagem enviada com sucesso!');
                    fecharModal();
                }
            } catch (error) {
                alert('Erro ao enviar mensagem');
            }
        });

        // Inicializar
        loadDespachantes();
    </script>
</body>
</html>