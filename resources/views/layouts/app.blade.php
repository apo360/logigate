<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100 dark:bg-gray-950">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Dynamic Page Title --}}
    <title>@yield('title', config('app.name', 'Logigate Aduaneiro'))</title>

    <meta name="description" content="{{ $page_description ?? 'Sistema de Gestão de Processos Aduaneiros' }}">
    <meta name="keywords" content="{{ $page_keywords ?? 'Sistema, Gestão, Logistica, Aduaneira, Despachantes, Processos' }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('dist/img/LOGIGATE.png') }}">

    <script>
        (function () {
            const storageKey = 'logigate-theme';
            const legacyStorageKey = 'darkMode';
            const root = document.documentElement;
            const systemQuery = window.matchMedia('(prefers-color-scheme: dark)');

            function readStorage(key) {
                try {
                    return localStorage.getItem(key);
                } catch (error) {
                    return null;
                }
            }

            function writeStorage(key, value) {
                try {
                    localStorage.setItem(key, value);
                } catch (error) {
                    return false;
                }

                return true;
            }

            function resolveStoredTheme() {
                const storedTheme = readStorage(storageKey);

                if (['light', 'dark', 'system'].includes(storedTheme)) {
                    return storedTheme;
                }

                const legacyTheme = readStorage(legacyStorageKey);

                if (legacyTheme === 'true' || legacyTheme === 'false') {
                    const migratedTheme = legacyTheme === 'true' ? 'dark' : 'light';
                    writeStorage(storageKey, migratedTheme);
                    return migratedTheme;
                }

                return 'system';
            }

            function applyTheme(theme) {
                const shouldUseDark = theme === 'dark' || (theme === 'system' && systemQuery.matches);

                root.classList.toggle('dark', shouldUseDark);
                root.style.colorScheme = shouldUseDark ? 'dark' : 'light';

                return shouldUseDark;
            }

            const initialTheme = resolveStoredTheme();
            applyTheme(initialTheme);

            window.logigateTheme = {
                storageKey,
                systemQuery,
                resolveStoredTheme,
                applyTheme,
                writeStorage,
            };

            window.layoutState = function () {
                return {
                    sidebarOpen: false,
                    theme: initialTheme,
                    isDark: root.classList.contains('dark'),

                    init() {
                        this.theme = window.logigateTheme.resolveStoredTheme();
                        this.applyTheme();

                        const syncSystemTheme = () => {
                            if (this.theme === 'system') {
                                this.applyTheme();
                            }
                        };

                        if (window.logigateTheme.systemQuery.addEventListener) {
                            window.logigateTheme.systemQuery.addEventListener('change', syncSystemTheme);
                        } else if (window.logigateTheme.systemQuery.addListener) {
                            window.logigateTheme.systemQuery.addListener(syncSystemTheme);
                        }
                    },

                    applyTheme() {
                        this.isDark = window.logigateTheme.applyTheme(this.theme);
                        window.logigateTheme.writeStorage(window.logigateTheme.storageKey, this.theme);
                    },

                    toggleTheme() {
                        this.theme = this.isDark ? 'light' : 'dark';
                        this.applyTheme();
                    },

                    closeSidebar() {
                        this.sidebarOpen = false;
                    },
                };
            };
        })();
    </script>

    <!-- Font Awesome (leve e necessária) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    {{-- Custom components CSS (usar quando necessário) --}}
    @stack('styles')
</head>

<body
    class="h-full overflow-hidden bg-gray-100 font-sans antialiased text-gray-900 dark:bg-gray-950 dark:text-gray-100"
    x-data="layoutState()"
    x-init="init()"
    x-on:keydown.escape.window="closeSidebar()"
>
    <!-- Overlay mobile -->
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-gray-950/60 backdrop-blur-sm lg:hidden"
        aria-hidden="true"
        x-on:click="closeSidebar()"
    ></div>

    <div class="flex h-dvh min-h-dvh overflow-hidden bg-gray-100 dark:bg-gray-950">
        {{-- ======================== SIDEBAR ======================== --}}
        <aside
            id="app-sidebar"
            class="fixed inset-y-0 left-0 z-50 flex w-64 shrink-0 -translate-x-full flex-col bg-logigate-dark text-white shadow-xl transition-transform duration-300 ease-in-out lg:static lg:z-auto lg:translate-x-0"
            x-bind:class="{ '!translate-x-0': sidebarOpen }"
            aria-label="Menu principal"
        >
            <!-- LOGO -->
            <div class="flex h-16 shrink-0 items-center border-b border-white/10 px-5">
                <img
                    src="{{ asset('dist/img/LOGIGATE.png') }}"
                    class="mr-3 h-9 rounded"
                    alt="Logigate"
                >
                <span class="text-lg font-semibold tracking-wide">Logigate</span>
            </div>

            <!-- MENU DINAMICO -->
            <nav class="min-h-0 flex-1 overflow-y-auto px-3 py-4" aria-label="Navegação principal">
                <livewire:menu-dinamico />
            </nav>
        </aside>

        {{-- ======================== MAIN PANEL ======================== --}}
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

            {{-- ======================== TOPBAR ======================== --}}
            <header class="flex h-16 shrink-0 items-center justify-between gap-3 border-b border-gray-200 bg-white px-3 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:px-4">

                {{-- Hamburger (mobile) --}}
                <button
                    type="button"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-gray-700 transition hover:bg-gray-100 hover:text-logigate-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-logigate-primary/60 dark:text-gray-300 dark:hover:bg-gray-800 lg:hidden"
                    x-on:click="sidebarOpen = true"
                    x-bind:aria-expanded="sidebarOpen.toString()"
                    aria-controls="app-sidebar"
                    aria-label="Abrir menu principal"
                >
                    <i class="fa fa-bars text-xl" aria-hidden="true"></i>
                </button>

                {{-- Page Title / Custom Header --}}
                <div class="min-w-0 flex-1 truncate text-sm font-semibold text-logigate-dark dark:text-gray-100 sm:text-base">
                    {{ $header ?? 'Painel Administrativo' }}
                </div>

                {{-- RIGHT MENU --}}
                <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">

                    {{-- SEARCH SHORTCUTS --}}
                    <div x-data="{ open: false, query: '' }" class="relative">
                        <button
                            type="button"
                            x-on:click="open = !open"
                            x-bind:aria-expanded="open.toString()"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-logigate-primary/10 text-logigate-primary transition hover:bg-logigate-primary/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-logigate-primary/60 dark:bg-logigate-secondary/15 dark:text-logigate-tertiary dark:hover:bg-logigate-secondary/25"
                            aria-label="Abrir atalhos de pesquisa"
                        >
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </button>

                        <div
                            x-cloak
                            x-show="open"
                            x-transition
                            x-on:click.away="open = false"
                            class="absolute right-0 z-50 mt-2 w-[calc(100vw-2rem)] max-w-xs rounded-lg border border-gray-200 bg-white p-3 shadow-lg dark:border-gray-700 dark:bg-gray-900 sm:w-72"
                        >
                            <input
                                type="text"
                                x-model="query"
                                placeholder="Pesquisar no menu..."
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-logigate-primary focus:ring-logigate-primary dark:border-gray-700 dark:bg-gray-950 dark:text-white"
                            >

                            <div class="mt-3 grid gap-2 text-sm">
                                <a href="{{ route('processos.index') }}" class="rounded-lg px-3 py-2 text-gray-700 transition hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-logigate-primary/60 dark:text-gray-200 dark:hover:bg-gray-800">
                                    Processos
                                </a>
                                <a href="{{ route('customers.index') }}" class="rounded-lg px-3 py-2 text-gray-700 transition hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-logigate-primary/60 dark:text-gray-200 dark:hover:bg-gray-800">
                                    Clientes
                                </a>
                                <a href="{{ route('licenciamentos.index') }}" class="rounded-lg px-3 py-2 text-gray-700 transition hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-logigate-primary/60 dark:text-gray-200 dark:hover:bg-gray-800">
                                    Licenciamentos
                                </a>
                            </div>

                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Pesquisa global será activada quando o endpoint estiver disponível.
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:block">
                        <livewire:subscription-widget />
                    </div>

                    {{-- DARK MODE --}}
                    <button
                        type="button"
                        x-on:click="toggleTheme()"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-700 transition hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-logigate-primary/60 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        x-bind:aria-label="isDark ? 'Activar modo claro' : 'Activar modo escuro'"
                    >
                        <i class="fa fa-moon" x-show="!isDark" aria-hidden="true"></i>
                        <i class="fa fa-sun" x-cloak x-show="isDark" aria-hidden="true"></i>
                    </button>

                    {{-- NOTIFICACOES --}}
                    <div x-data="{ open: false }" class="relative">
                        <button
                            type="button"
                            x-on:click="open = !open"
                            x-bind:aria-expanded="open.toString()"
                            class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg bg-logigate-primary/10 text-logigate-primary transition hover:bg-logigate-primary/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-logigate-primary/60 dark:bg-logigate-secondary/15 dark:text-logigate-tertiary dark:hover:bg-logigate-secondary/25"
                            aria-label="Abrir notificações"
                        >
                            <i class="fa fa-bell" aria-hidden="true"></i>
                        </button>

                        <div
                            x-cloak
                            x-show="open"
                            x-transition
                            x-on:click.away="open = false"
                            class="absolute right-0 z-50 mt-2 w-[calc(100vw-2rem)] max-w-xs overflow-hidden rounded-lg border border-gray-200 bg-white py-2 shadow-xl dark:border-gray-700 dark:bg-gray-900 sm:w-72"
                        >
                            <h4 class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Notificações
                            </h4>

                            <div class="max-h-64 overflow-y-auto">
                                <div class="px-4 py-5 text-sm text-gray-500 dark:text-gray-400">
                                    Sem notificações novas.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- USER DROPDOWN --}}
                    <div x-data="{ open: false }" class="relative">
                        <button
                            type="button"
                            x-on:click="open = !open"
                            x-bind:aria-expanded="open.toString()"
                            class="group flex h-10 items-center gap-2 rounded-lg px-1 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-logigate-primary/60 sm:px-2"
                            aria-label="Abrir menu do utilizador"
                        >
                            <img
                                src="{{ Auth::user()->profile_photo_url }}"
                                class="h-9 w-9 rounded-full border border-gray-300 transition group-hover:border-logigate-primary dark:border-gray-700"
                                alt="{{ Auth::user()->name }}"
                            >

                            <span class="hidden max-w-32 truncate font-medium text-gray-700 group-hover:text-logigate-primary dark:text-gray-200 sm:inline">
                                {{ Auth::user()->name }}
                            </span>
                        </button>

                        @php
                            $currentEmpresa = auth()->user()->empresas->first();
                            $dropdownBase = 'flex items-center gap-2 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-logigate-primary/60 dark:text-gray-200 dark:hover:bg-gray-800';
                            $dropdownActive = ' bg-logigate-primary/10 font-semibold text-logigate-primary dark:bg-logigate-primary/15 dark:text-logigate-tertiary';
                        @endphp

                        <div
                            x-cloak
                            x-show="open"
                            x-transition
                            x-on:click.away="open = false"
                            class="absolute right-0 z-50 mt-3 w-[calc(100vw-2rem)] max-w-xs overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900 sm:w-72"
                        >
                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                                @if($currentEmpresa)
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $currentEmpresa->Empresa }}</p>
                                @endif
                            </div>

                            <div class="py-1">
                                <a href="{{ route('profile.show') }}" class="{{ $dropdownBase }} {{ request()->routeIs('profile.show') ? $dropdownActive : '' }}">
                                    <i class="fa fa-user w-4 text-logigate-primary dark:text-logigate-tertiary" aria-hidden="true"></i>
                                    {{ __('Minha Conta') }}
                                </a>

                                @if($currentEmpresa)
                                    <a href="{{ route('empresas.edit', $currentEmpresa->id) }}" class="{{ $dropdownBase }} {{ request()->routeIs('empresas.*') ? $dropdownActive : '' }}">
                                        <i class="fa fa-building w-4 text-logigate-primary dark:text-logigate-tertiary" aria-hidden="true"></i>
                                        {{ __('Empresa') }}
                                    </a>
                                @endif

                                <a href="{{ route('usuarios.index') }}" class="{{ $dropdownBase }} {{ request()->routeIs('usuarios.*', 'users.*', 'roles.*', 'permissions.*') ? $dropdownActive : '' }}">
                                    <i class="fa fa-users-gear w-4 text-logigate-primary dark:text-logigate-tertiary" aria-hidden="true"></i>
                                    {{ __('Usuários e Permissões') }}
                                </a>

                                <a href="{{ route('admin.integracoes') }}" class="{{ $dropdownBase }} {{ request()->routeIs('admin.integracoes', 'integracoes.*') ? $dropdownActive : '' }}">
                                    <i class="fa fa-plug w-4 text-logigate-primary dark:text-logigate-tertiary" aria-hidden="true"></i>
                                    {{ __('Integrações') }}
                                </a>
                            </div>

                            <div class="border-t border-gray-200 py-1 dark:border-gray-700">
                                <a href="{{ route('billing.plans') }}" class="{{ $dropdownBase }} {{ request()->routeIs('billing.*') ? $dropdownActive : '' }}">
                                    <i class="fa fa-credit-card w-4 text-logigate-primary dark:text-logigate-tertiary" aria-hidden="true"></i>
                                    {{ __('Subscrição e Pagamentos') }}
                                </a>
                            </div>

                            <div class="border-t border-gray-200 py-1 dark:border-gray-700">
                                <a href="{{ route('configuracoes.index') }}" class="{{ $dropdownBase }} {{ request()->routeIs('configuracoes.*') ? $dropdownActive : '' }}">
                                    <i class="fa fa-gear w-4 text-logigate-primary dark:text-logigate-tertiary" aria-hidden="true"></i>
                                    {{ __('Configurações') }}
                                </a>

                                <a href="{{ route('logs.index') }}" class="{{ $dropdownBase }} {{ request()->routeIs('logs.*') ? $dropdownActive : '' }}">
                                    <i class="fa fa-shield-halved w-4 text-logigate-primary dark:text-logigate-tertiary" aria-hidden="true"></i>
                                    {{ __('Segurança & Auditoria') }}
                                </a>

                                <a href="{{ route('empresa.migracao') }}" class="{{ $dropdownBase }} {{ request()->routeIs('empresa.migracao') ? $dropdownActive : '' }}">
                                    <i class="fa fa-file-import w-4 text-logigate-primary dark:text-logigate-tertiary" aria-hidden="true"></i>
                                    {{ __('Migrações / Importações') }}
                                </a>
                            </div>

                            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-200 dark:border-gray-700">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-red-500 dark:text-red-400 dark:hover:bg-red-950/30"
                                >
                                    <i class="fa fa-power-off w-4" aria-hidden="true"></i>
                                    {{ __('Sair') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- ======================== PAGE CONTENT ======================== --}}
            <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden bg-gray-50 p-3 dark:bg-gray-950 sm:p-4 lg:p-6">

                @include('components.validation-errors')
                @include('components.validation-success')

                {{ $slot }}

            </main>

            {{-- ======================== FOOTER ======================== --}}
            <footer class="shrink-0 border-t border-gray-200 bg-white px-4 py-3 text-center text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                © {{ date('Y') }} LOGIGATE - Todos os direitos reservados.
            </footer>

        </div>
    </div>


    {{-- Modal global para Quick Create --}}
    <livewire:modals.quick-create-modal />

    <x-ui.toast />

    @livewireScripts

    @stack('scripts')

    <script>
        function registerLivewireToastBridge() {
            Livewire.on('toast', ({ type, message }) => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type, message }
                }));
            });
        }

        if (window.Livewire) {
            registerLivewireToastBridge();
        } else {
            document.addEventListener('livewire:init', registerLivewireToastBridge, { once: true });
        }
    </script>

</body>
</html>
