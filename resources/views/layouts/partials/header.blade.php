<!-- resources/views/layouts/partials/header.blade.php -->
<div class="flex justify-between items-center bg-white shadow-md p-2 w-full top-0 z-50">
    <!-- Título e Barra de Pesquisa -->
    <div class="flex items-center space-x-4">
        <div class="p-4">
            <button id="menu-toggle" class="text-black focus:outline-none menu-toggle-icon">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        <!-- Título -->
        <span class="text-xl font-semibold text-gray-800">Logi<b class="text-blue-600">Gate</b></span>

        <!-- Barra de Pesquisa -->
        <div class="relative">
            <input
                type="text"
                placeholder="Pesquisar..."
                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
    </div>

    <!-- Ferramentas e Configurações -->
    <div class="flex items-center space-x-6">
        <!-- Botão de Notificações -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                <i class="fas fa-bell text-xl"></i>
                <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1">3</span>
            </button>
            <!-- Dropdown de Notificações -->
            
        </div>

        <!-- Botão de Configurações -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                <i class="fas fa-cog text-xl"></i>
            </button>
            <!-- Dropdown de Configurações -->
            
        </div>

        <!-- Ferramentas de Layout -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                <i class="fas fa-th-large text-xl"></i>
            </button>
            <!-- Dropdown de Ferramentas -->
            
        </div>

        <!-- Menu do Usuário -->
        <x-dropdown align="right" width="50">
            <x-slot name="trigger">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                        <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </button>
                @else
                    <span class="inline-flex rounded-md">
                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                            {{ Auth::user()->name }}
                            <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </span>
                @endif
            </x-slot>

            <x-slot name="content">
                <!-- Links do Dropdown -->
                @foreach(auth()->user()->empresas as $empresa)
                    <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ __($empresa->Empresa) }}
                    </div>
                    <x-dropdown-link href="{{ route('empresas.edit', $empresa->id) }}">
                        {{ __('Perfil Empresa') }}
                    </x-dropdown-link>
                    <x-dropdown-link href="{{ route('subscribe.view', $empresa->id) }}">
                        {{ __('Subscrição') }}
                    </x-dropdown-link>
                @endforeach
                <x-dropdown-link href="{{ route('usuarios.index') }}">
                    {{ __('Usuarios') }}
                </x-dropdown-link>
                <x-dropdown-link href="{{ route('profile.show') }}">
                    {{ __('Alterar Senha') }}
                </x-dropdown-link>
                <x-dropdown-link href="{{ route('roles.index') }}">
                    {{ __('Regras') }}
                </x-dropdown-link>
                <x-dropdown-link href="{{ route('permissions.index') }}">
                    {{ __('Permissões') }}
                </x-dropdown-link>
                <x-dropdown-link href="{{ route('empresa.migracao') }}">
                    {{ __('Migração') }}
                </x-dropdown-link>
                <x-dropdown-link href="{{ route('empresa.cambio') }}">
                    {{ __('Cambio')}}
                </x-dropdown-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-dropdown-link href="{{ route('api-tokens.index') }}">
                        {{ __('API Tokens') }}
                    </x-dropdown-link>
                @endif

                <!-- Divisor -->
                <div class="border-t border-gray-200"></div>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Sair') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</div>
