<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Alpine.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @stack('head')
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div x-data="{ sidebarOpen: false }" class="flex">
            <!-- Mobile menu button -->
            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-4 fixed top-0 left-0 z-50 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <!-- Overlay -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"></div>
            <!-- Sidebar -->
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" class="w-64 bg-white shadow-lg fixed md:relative inset-y-0 left-0 transform transition-transform duration-200 ease-in-out z-50 flex flex-col">
                <div class="p-4 flex items-center justify-between border-b">
                    <h1 class="text-xl font-bold text-indigo-600">Logigate</h1>
                    <button @click="sidebarOpen = false" class="md:hidden focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <nav class="flex-1 mt-4">
                    <ul>
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100 rounded transition">
                                <i class="fas fa-home mr-2"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users') }}" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100 rounded transition">
                                <i class="fas fa-users mr-2"></i> Users
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.countries') }}" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100 rounded transition">
                                <i class="fas fa-globe mr-2"></i> Countries
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.ports') }}" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100 rounded transition">
                                <i class="fas fa-anchor mr-2"></i> Ports
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.products') }}" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100 rounded transition">
                                <i class="fas fa-box mr-2"></i> Products
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.categories') }}" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100 rounded transition">
                                <i class="fas fa-tags mr-2"></i> Categories
                            </a>
                        </li>
                        <li>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100 rounded transition">
                                <i class="fas fa-shopping-cart mr-2"></i> Orders
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="p-4 border-t text-xs text-gray-400">
                    &copy; {{ date('Y') }} Logigate
                </div>
            </aside>
        </div>
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-lg p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-indigo-700">@yield('title', 'Admin')</h2>
                        <!-- Breadcrumbs -->
                        <nav class="flex mt-2" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 text-sm text-gray-500">
                                <li class="inline-flex items-center">
                                    <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Home</a>
                                </li>
                                @hasSection('breadcrumb')
                                    <li>
                                        <span class="mx-2">/</span>
                                    </li>
                                    @yield('breadcrumb')
                                @endif
                            </ol>
                        </nav>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="#" class="text-gray-700 hover:text-indigo-600 relative">
                            <i class="fas fa-bell"></i>
                            <!-- Example notification dot -->
                            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500"></span>
                        </a>
                        <!-- User Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="text-gray-700 hover:text-indigo-600 focus:outline-none">
                                <i class="fas fa-user"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100">Perfil</a>
                                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-indigo-100">Configurações</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-indigo-100">Sair</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Main -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>