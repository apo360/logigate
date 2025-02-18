<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transitário | Logigate</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Outros metadados e scripts -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Estilos personalizados -->
    <style>
        /* Transição suave para o menu lateral */
        #sidebar {
            transition: width 0.3s ease-in-out;
        }
        /* Estilo quando o menu está minimizado */
        #sidebar.minimized {
            width: 64px; /* Largura reduzida para caber apenas os ícones */
        }
        #sidebar.minimized .menu-text {
            display: none; /* Oculta o texto dos itens do menu */
        }
        #sidebar.minimized .logo img {
            display: none; /* Oculta o logo */
        }
        #sidebar.minimized .menu-toggle-icon {
            margin-left: 12px; /* Ajusta a posição do ícone do hambúrguer */
        }
        /* Alinhamento dos ícones no estado minimizado */
        #sidebar.minimized nav ul li a {
            justify-content: center; /* Centraliza os ícones */
            padding: 8px; /* Reduz o padding para melhorar o espaçamento */
        }
        #sidebar.minimized nav ul li a i {
            margin-right: 0; /* Remove a margem direita do ícone */
        }
        /* Submenus no estado minimizado */
        #sidebar.minimized nav ul ul {
            display: none; /* Oculta submenus no estado minimizado */
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Container Principal -->
    <div class="flex">
        <!-- Menu Lateral (Aside) -->
        <aside id="sidebar" class="bg-gray-800 text-white w-64 min-h-screen fixed md:relative">
            <!-- Logo -->
            <div class="p-4 border-b border-gray-700 logo">
                <img src="https://via.placeholder.com/150x50.png?text=Logigate" alt="Logo" class="w-full">
            </div>
            <!-- Menu de Navegação -->
            <nav class="p-4">
                <ul>
                    <!-- Dashboard -->
                    <li class="mb-2">
                        <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>

                    <!-- Funcionários -->
                    <li class="mb-2" x-data="{ open: false }">
                        <a href="#" @click="open = !open" class="flex items-center p-2 hover:bg-gray-700 rounded">
                            <i class="fas fa-users mr-2"></i>
                            <span class="menu-text">Funcionários</span>
                            <i :class="open ? 'fas fa-chevron-up ml-auto' : 'fas fa-chevron-down ml-auto'"></i>
                        </a>
                        <ul x-show="open" class="ml-4 mt-2">
                            <li class="mb-2">
                                <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
                                    <i class="fas fa-user mr-2"></i>
                                    <span class="menu-text">Funcionários</span>
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
                                    <i class="fas fa-truck mr-2"></i>
                                    <span class="menu-text">Motoristas</span>
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
                                    <i class="fas fa-user-cog mr-2"></i>
                                    <span class="menu-text">Usuários Sys</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Transportes -->
                    <li class="mb-2" x-data="{ open: false }">
                        <a href="#" @click="open = !open" class="flex items-center p-2 hover:bg-gray-700 rounded">
                            <i class="fas fa-truck-moving mr-2"></i>
                            <span class="menu-text">Transportes</span>
                            <i :class="open ? 'fas fa-chevron-up ml-auto' : 'fas fa-chevron-down ml-auto'"></i>
                        </a>
                        <ul x-show="open" class="ml-4 mt-2">
                            <li class="mb-2">
                                <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
                                    <i class="fas fa-search mr-2"></i>
                                    <span class="menu-text">Pesquisar</span>
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
                                    <i class="fas fa-file-alt mr-2"></i>
                                    <span class="menu-text">Registo</span>
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
                                    <i class="fas fa-tools mr-2"></i>
                                    <span class="menu-text">Manutenções</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Operações -->
                    <li class="mb-2" x-data="{ open: false }">
                        <a href="#" @click="open = !open" class="flex items-center p-2 hover:bg-gray-700 rounded">
                            <i class="fas fa-cogs mr-2"></i>
                            <span class="menu-text">Operações</span>
                            <i :class="open ? 'fas fa-chevron-up ml-auto' : 'fas fa-chevron-down ml-auto'"></i>
                        </a>
                        <ul x-show="open" class="ml-4 mt-2">
                            <li class="mb-2">
                                <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <span class="menu-text">Rastreamento</span>
                                </a>
                            </li>
                            <li class="mb-2" x-data="{ open: false }">
                                <a href="#" @click="open = !open" class="flex items-center p-2 hover:bg-gray-700 rounded">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <span class="menu-text">Planeamento</span>
                                    <i :class="open ? 'fas fa-chevron-up ml-auto' : 'fas fa-chevron-down ml-auto'"></i>
                                </a>
                                <ul x-show="open" class="ml-4 mt-2">
                                    <li class="mb-2">
                                        <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
                                            <i class="fas fa-calendar-day mr-2"></i>
                                            <span class="menu-text">Agenda</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <!-- Relatórios -->
                    <li class="mb-2">
                        <a href="#" class="flex items-center p-2 hover:bg-gray-700 rounded">
                            <i class="fas fa-chart-bar mr-2"></i>
                            <span class="menu-text">Relatórios</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Conteúdo Principal -->
        <div class="flex-1">
            <!-- Header -->
            <header>
                @include('layouts.partials.header')
            </header>

            <!-- Conteúdo -->
            <main class="pt-4">
                <div class="bg-white p-6 rounded-lg shadow">
                    @yield('content')
                </div>
            </main>

            <!-- Footer -->
            <footer class="p-2">
                @include('layouts.partials.footer')
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // jQuery para alternar o menu lateral entre normal e minimizado
        $(document).ready(function() {
            $('#menu-toggle').click(function() {
                $('#sidebar').toggleClass('minimized');
            });
        });
    </script>
</body>
</html>