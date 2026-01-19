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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512" crossorigin="anonymous" />

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

<body class="h-full font-sans antialiased">

    <!-- Mobile Background Overlay -->
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-20 bg-black bg-opacity-40 lg:hidden" @click="sidebarOpen = false"> </div>

    <div class="flex h-screen overflow-hidden">

        {{-- ======================== SIDEBAR ======================== --}}
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 bg-logigate-dark text-white shadow-xl
                   transform transition-transform duration-300 lg:translate-x-0 lg:static"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

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
                <div class="font-semibold text-logigate-dark dark:text-gray-200">
                    {{ $header ?? 'Painel Administrativo' }}
                </div>

                {{-- RIGHT MENU --}}
                <div class="flex items-center space-x-5">

                    {{-- 🔍 SEARCH BAR --}}
                    <div x-data="{ open: false, query: '' }" class="relative">
                        <button @click="open = !open"
                                class="p-2 rounded-full bg-logigate-primary/10 text-logigate-primary hover:bg-logigate-primary/20">
                            <i class="fa fa-search"></i>
                        </button>

                        <div x-show="open" x-transition
                            @click.away="open = false"
                            class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 shadow-lg rounded-xl p-3 z-40">

                            <input type="text" x-model="query"
                                placeholder="Pesquisar..."
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700
                                        focus:ring-logigate-primary focus:border-logigate-primary
                                        dark:bg-gray-900 dark:text-white">

                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                Pressione <span class="font-bold">Enter</span> para pesquisar
                            </div>

                        </div>
                    </div>


                    {{-- 🟢 SUBSCRIÇÃO --}}
                    @php
                        use Carbon\Carbon;
                        use Illuminate\Support\Str;

                        $empresa = auth()->user()->empresas->first();
                        $sub = $empresa?->subscricoes()->latest('data_expiracao')->first();

                        $start = $sub?->data_inicio ? Carbon::parse($sub->data_inicio) : now();
                        $end   = $sub?->data_expiracao ? Carbon::parse($sub->data_expiracao) : now();
                        $curr  = now();

                        $totalDays = max($start->diffInDays($end), 1);
                        $daysLeft  = $curr->diffInDays($end, false);

                        $usedPercent = min(100, max(0, (($totalDays - max($daysLeft, 0)) / $totalDays) * 100));
                        $remainingPercent = 100 - intval($usedPercent);

                    @endphp

                    {{-- PIE DESIGN PRO --}}
                    <div 
                        class="flex items-center gap-4 bg-white dark:bg-gray-900 p-4 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-800"
                        x-data="{ percent: 0, hover:false }"
                        x-init="setTimeout(() => percent = {{ $remainingPercent }}, 300)"
                    >

                        <!-- PIE CONTAINER -->
                        <div class="relative"
                            :class="{
                                'w-16 h-16': true,     /* tamanho médio */
                                'animate-[pulse-glow_3s_ease-in-out_infinite]': percent < 20,  /* pulsar se crítico */
                            }">

                            <!-- SVG PIE -->
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">

                                <!-- trilho -->
                                <path
                                    class="text-gray-300 dark:text-gray-700"
                                    stroke-width="3"
                                    stroke="currentColor"
                                    fill="none"
                                    d="
                                        M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831
                                    "
                                ></path>

                                <!-- gradiente -->
                                <defs>
                                    <linearGradient id="pieGradient" x1="1" y1="0" x2="0" y2="1">
                                        <stop offset="0%"   stop-color="#2fe6a7"/>
                                        <stop offset="50%"  stop-color="#16b0f8"/>
                                        <stop offset="100%" stop-color="#8752ff"/>
                                    </linearGradient>
                                </defs>

                                <!-- progresso -->
                                <path
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    fill="none"
                                    :stroke-dasharray="percent + ', 100'"
                                    stroke="url(#pieGradient)"
                                    class="transition-all duration-[1200ms] ease-out"
                                    d="
                                        M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831
                                    "
                                ></path>
                            </svg>

                            <!-- TEXTO NO CENTRO -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">

                                @if($daysLeft >= 0)
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ $daysLeft }}d
                                    </span>

                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">
                                        left
                                    </span>

                                @else
                                    <span class="text-base font-extrabold text-red-600 animate-pulse">
                                        Exp
                                    </span>
                                @endif

                            </div>

                        </div>

                        <!-- INFO -->
                        <div class="flex flex-col justify-center">

                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Subscrição
                            </div>

                            @if ($daysLeft >= 0)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    expira {{ $end->format('d/m/Y') }}
                                </div>
                            @else
                                <div class="text-xs text-red-500">
                                    Expirada há {{ abs($daysLeft) }} dias
                                </div>
                            @endif

                            <!-- Tooltip -->
                            <div class="text-[11px] mt-1 text-gray-400 dark:text-gray-500">
                                {{ $remainingPercent }}% restante
                            </div>
                        </div>
                    </div>
                    {{-- FIM PIE DESIGN PRO --}}

                    {{-- 🔔 NOTIFICAÇÕES --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="relative p-2 rounded-full bg-logigate-primary/10 text-logigate-primary hover:bg-logigate-primary/20">
                            <i class="fa fa-bell"></i>

                            {{-- Badge --}}
                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center">
                                3
                            </span>
                        </button>

                        <div x-show="open" x-transition @click.away="open = false" class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 shadow-xl rounded-xl py-2 z-40">

                            <h4 class="px-4 py-2 font-semibold text-gray-700 dark:text-gray-200 text-sm">
                                Notificações
                            </h4>

                            <div class="max-h-64 overflow-y-auto">

                                {{-- Placeholder — substitui por Livewire futuramente --}}
                                <div class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        Fatura expira em breve
                                    </div>
                                    <div class="text-xs text-gray-400">há 2 horas</div>
                                </div>

                                <div class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        Novo utilizador registado
                                    </div>
                                    <div class="text-xs text-gray-400">há 1 dia</div>
                                </div>

                            </div>
                        </div>
                    </div>


                    {{-- 🌙 DARK MODE --}}
                    <button @click="toggleDarkMode"
                            class="p-2 bg-logigate-secondary/10 text-logigate-secondary
                                hover:bg-logigate-secondary/40">
                        <i class="fa" :class="isDarkMode ? 'fa-sun' : 'fa-moon'"></i>
                    </button>


                    {{-- 👤 USER DROPDOWN --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="flex items-center space-x-2 group">

                            <img src="{{ Auth::user()->profile_photo_url }}"
                                class="h-9 w-9 rounded-full border border-gray-300 dark:border-gray-700 group-hover:border-logigate-primary transition">

                            <span class="text-gray-700 dark:text-gray-200 font-medium group-hover:text-logigate-primary">
                                {{ Auth::user()->name }}
                            </span>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-3 w-60 bg-white dark:bg-gray-900 shadow-xl rounded-xl z-40 overflow-hidden">

                            {{-- PROFILE --}}
                            <a href="{{ route('profile.show') }}"
                            class="block px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fa fa-user mr-2 text-logigate-primary"></i>
                                Perfil
                            </a>

                            {{-- EMPRESAS --}}
                            @foreach(auth()->user()->empresas as $empresa)
                                <div class="px-4 py-1 text-[10px] text-gray-400 uppercase">
                                    {{ $empresa->Empresa }}
                                </div>

                                <a href="{{ route('empresas.edit', $empresa->id) }}"
                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                                    Perfil da Empresa
                                </a>

                                <a href="{{ route('subscribe.view', $empresa->id) }}"
                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                                    Subscrição
                                </a>
                            @endforeach

                            {{-- GESTÃO --}}
                            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>

                            <a href="{{ route('usuarios.index') }}"
                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                            Usuários
                            </a>

                            <a href="{{ route('roles.index') }}"
                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                            Regras
                            </a>

                            <a href="{{ route('permissions.index') }}"
                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                            Permissões
                            </a>

                            <a href="{{ route('empresa.migracao') }}"
                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                            Migração
                            </a>

                            {{-- LOGOUT --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="w-full text-left px-4 py-3 text-red-600 hover:bg-red-50 dark:hover:bg-gray-700">
                                    <i class="fa fa-power-off mr-2"></i> Sair
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
