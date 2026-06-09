<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100 dark:bg-gray-900">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Dynamic Page Title --}}
    <title>@yield('title', config('app.name', 'Logigate Aduaneiro'))</title>

    <meta name="description" content="{{ $page_description ?? 'Sistema de Gestão de Processos Aduaneiros' }}">
    <meta name="keywords" content="{{ $page_keywords ?? 'Sistema, Gestão, Logistica, Aduaneira, Despachantes, Processos' }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('dist/img/LOGIGATE.png') }}">

    <!-- Font Awesome (leve e necessária) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    
    {{-- Custom components CSS (usar quando necessário) --}}
    @stack('styles')

    <!-- PALETA LOGIGATE -->
    <style>
        :root {
            --logigate-primary:   #0057D9;
            --logigate-secondary: #008CFF;
            --logigate-tertiary:  #15C9E8;
            --logigate-dark:      #002F6C;
        }

        .bg-logigate-primary    { background-color: var(--logigate-primary); }
        .bg-logigate-secondary  { background-color: var(--logigate-secondary); }
        .bg-logigate-tertiary   { background-color: var(--logigate-tertiary); }
        .bg-logigate-dark       { background-color: var(--logigate-dark); }

        .text-logigate-primary   { color: var(--logigate-primary); }
        .text-logigate-secondary { color: var(--logigate-secondary); }
        .text-logigate-tertiary  { color: var(--logigate-tertiary); }
        .text-logigate-dark      { color: var(--logigate-dark); }

        .border-logigate-primary { border-color: var(--logigate-primary); }

        /* // Animação de pulso para alerta crítico */
        @keyframes pulse-glow {
            0%   { box-shadow: 0 0 0px rgba(255, 0, 0, 0.0); }
            50%  { box-shadow: 0 0 12px rgba(255, 0, 0, 0.5); }
            100% { box-shadow: 0 0 0px rgba(255, 0, 0, 0.0); }
        }

    </style>

</head>

<body class="h-full font-sans antialiased"
    x-data="{ sidebarOpen: false, isMobile: () => window.innerWidth < 1024,
            closeSidebarOnMobile() {
                if (this.isMobile()) this.sidebarOpen = false;
            }
        }"
        @resize.window="if (!isMobile()) sidebarOpen = false">

    <!-- Overlay simplificado -->
    <div x-show="sidebarOpen && isMobile()" 
        x-transition.opacity
        class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden" 
        @click="sidebarOpen = false">
    </div>

    <div class="flex h-screen overflow-hidden">
        {{-- ======================== SIDEBAR ======================== --}}
        <aside
            x-show="sidebarOpen || !isMobile()"
            x-transition:enter="transition-transform duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition-transform duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-30 w-64 bg-logigate-dark text-white shadow-xl
                   lg:translate-x-0 lg:static lg:z-0"
            @click.away="closeSidebarOnMobile()">

            <!-- LOGO -->
            <div class="h-16 flex items-center px-5 border-b border-white/10">
                <img src="{{ asset('dist/img/LOGIGATE.png') }}"
                     class="h-9 mr-3 rounded">
                <span class="text-lg font-semibold tracking-wide">Logigate</span>
            </div>

            <!-- MENU DINÂMICO -->
            <div class="px-3 py-4 space-y-1">
                <livewire:menu-dinamico />
            </div>
        </aside>

        {{-- ======================== MAIN PANEL ======================== --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- ======================== TOPBAR ======================== --}}
            <header class="h-16 bg-white dark:bg-gray-700 shadow flex items-center justify-between px-4">

                {{-- Hamburger (mobile) --}}
                <button class="lg:hidden text-gray-700 dark:text-gray-300"
                        @click="sidebarOpen = true">
                    <i class="fa fa-bars text-2xl"></i>
                </button>

                {{-- Page Title / Custom Header --}}
                <div class="min-w-0 truncate font-semibold text-logigate-dark dark:text-gray-200">
                    {{ $header ?? 'Painel Administrativo' }}
                </div>

                {{-- RIGHT MENU --}}
                <div class="flex shrink-0 items-center space-x-2 sm:space-x-4">

                    {{-- 🔍 SEARCH SHORTCUTS --}}
                    <div x-data="{ open: false, query: '' }" class="relative">
                        <button @click="open = !open"
                                class="p-2 rounded-lg bg-logigate-primary/10 text-logigate-primary hover:bg-logigate-primary/20 focus:outline-none focus:ring-2 focus:ring-logigate-primary/40"
                                aria-label="Abrir atalhos de pesquisa">
                            <i class="fa fa-search"></i>
                        </button>

                        <div x-show="open" x-transition
                            @click.away="open = false"
                            class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-3 z-40">

                            <input type="text" x-model="query"
                                placeholder="Pesquisar no menu..."
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                                        focus:ring-logigate-primary focus:border-logigate-primary
                                        dark:bg-gray-900 dark:text-white">

                            <div class="mt-3 grid gap-2 text-sm">
                                <a href="{{ route('processos.index') }}" class="rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Processos
                                </a>
                                <a href="{{ route('customers.index') }}" class="rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Clientes
                                </a>
                                <a href="{{ route('licenciamentos.index') }}" class="rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700">
                                    Licenciamentos
                                </a>
                            </div>

                            <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                Pesquisa global será activada quando o endpoint estiver disponível.
                            </div>

                        </div>
                    </div>

                    <div class="ml-auto hidden md:block">
                        <livewire:subscription-widget />
                    </div>

                    {{-- 🔔 NOTIFICAÇÕES --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="relative p-2 rounded-lg bg-logigate-primary/10 text-logigate-primary hover:bg-logigate-primary/20 focus:outline-none focus:ring-2 focus:ring-logigate-primary/40"
                                aria-label="Abrir notificações">
                            <i class="fa fa-bell"></i>
                        </button>

                        <div x-show="open" x-transition @click.away="open = false" class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 shadow-xl rounded-lg py-2 z-40">

                            <h4 class="px-4 py-2 font-semibold text-gray-700 dark:text-gray-200 text-sm">
                                Notificações
                            </h4>

                            <div class="max-h-64 overflow-y-auto">
                                <div class="px-4 py-5 text-sm text-gray-500 dark:text-gray-400">
                                    Sem notificações novas.
                                </div>

                            </div>
                        </div>
                    </div>


                    {{-- 👤 USER DROPDOWN --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="flex items-center space-x-2 group">

                            <img src="{{ Auth::user()->profile_photo_url }}"
                                class="h-9 w-9 rounded-full border border-gray-300 dark:border-gray-700 group-hover:border-logigate-primary transition">

                            <span class="hidden max-w-32 truncate text-gray-700 dark:text-gray-200 font-medium group-hover:text-logigate-primary sm:inline">
                                {{ Auth::user()->name }}
                            </span>
                        </button>

                        @php
                            $currentEmpresa = auth()->user()->empresas->first();
                            $dropdownBase = 'flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700';
                            $dropdownActive = ' bg-logigate-primary/10 font-semibold text-logigate-primary dark:bg-gray-800';
                        @endphp

                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-3 w-72 bg-white dark:bg-gray-900 shadow-xl rounded-xl z-40 overflow-hidden">

                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                                @if($currentEmpresa)
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $currentEmpresa->Empresa }}</p>
                                @endif
                            </div>

                            <div class="py-1">
                                <a href="{{ route('profile.show') }}" class="{{ $dropdownBase }} {{ request()->routeIs('profile.show') ? $dropdownActive : '' }}">
                                    <i class="fa fa-user w-4 text-logigate-primary"></i>
                                    {{ __('Minha Conta') }}
                                </a>

                                @if($currentEmpresa)
                                    <a href="{{ route('empresas.edit', $currentEmpresa->id) }}" class="{{ $dropdownBase }} {{ request()->routeIs('empresas.*') ? $dropdownActive : '' }}">
                                        <i class="fa fa-building w-4 text-logigate-primary"></i>
                                        {{ __('Empresa') }}
                                    </a>
                                @endif

                                <a href="{{ route('usuarios.index') }}" class="{{ $dropdownBase }} {{ request()->routeIs('usuarios.*', 'users.*', 'roles.*', 'permissions.*') ? $dropdownActive : '' }}">
                                    <i class="fa fa-users-gear w-4 text-logigate-primary"></i>
                                    {{ __('Usuários e Permissões') }}
                                </a>
                            </div>

                            <div class="border-t border-gray-200 py-1 dark:border-gray-700">
                                <a href="{{ route('billing.plans') }}" class="{{ $dropdownBase }} {{ request()->routeIs('billing.*') ? $dropdownActive : '' }}">
                                    <i class="fa fa-credit-card w-4 text-logigate-primary"></i>
                                    {{ __('Subscrição e Pagamentos') }}
                                </a>
                            </div>

                            <div class="border-t border-gray-200 py-1 dark:border-gray-700">
                                <a href="{{ route('configuracoes.index') }}" class="{{ $dropdownBase }} {{ request()->routeIs('configuracoes.*') ? $dropdownActive : '' }}">
                                    <i class="fa fa-gear w-4 text-logigate-primary"></i>
                                    {{ __('Configurações') }}
                                </a>

                                <a href="{{ route('logs.index') }}" class="{{ $dropdownBase }} {{ request()->routeIs('logs.*') ? $dropdownActive : '' }}">
                                    <i class="fa fa-shield-halved w-4 text-logigate-primary"></i>
                                    {{ __('Segurança & Auditoria') }}
                                </a>

                                <a href="{{ route('empresa.migracao') }}" class="{{ $dropdownBase }} {{ request()->routeIs('empresa.migracao') ? $dropdownActive : '' }}">
                                    <i class="fa fa-file-import w-4 text-logigate-primary"></i>
                                    {{ __('Migrações / Importações') }}
                                </a>
                            </div>

                            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-200 dark:border-gray-700">
                                @csrf
                                <button class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-gray-700">
                                    <i class="fa fa-power-off w-4"></i>
                                    {{ __('Sair') }}
                                </button>
                            </form>

                        </div>
                    </div>

                </div>

            </header>

            {{-- ======================== PAGE CONTENT ======================== --}}
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-logigate-tertiary/90">

                @include('components.validation-errors')
                @include('components.validation-success')

                {{ $slot }}

            </main>

            {{-- ======================== FOOTER ======================== --}}
            <footer class="p-4 bg-white dark:bg-logigate-tertiary border-t border-logigate-primary/20
                           text-center text-sm text-logigate-dark dark:text-white">
                © {{ date('Y') }} LOGIGATE — Todos os direitos reservados.
            </footer>

        </div>
    </div>

    @livewireScripts

    {{-- Modal global para Quick Create --}}
    <livewire:modals.quick-create-modal />

    @stack('scripts')

    <x-ui.toast />

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', ({ type, message }) => {
                window.dispatchEvent(new CustomEvent('toast-show', {
                    detail: { type, message }
                }));
            });

            // Debug: ver todos os eventos LiveWire
            Livewire.hook('message.processed', (message, component) => {
                if (message.updateQueue) {
                    message.updateQueue.forEach(update => {
                        if (update.type === 'fireEvent') {
                            console.log('LiveWire Event:', update.payload);
                        }
                    });
                }
            });
        });
    </script>

    <script>
        function layoutState() {
            return {
                sidebarOpen: false,

                // DARK MODE
                isDarkMode: localStorage.getItem('darkMode') === 'true',

                toggleDarkMode() {
                    this.isDarkMode = !this.isDarkMode;
                    localStorage.setItem('darkMode', this.isDarkMode);
                }
            }
        }
    </script>

</body>
</html>
