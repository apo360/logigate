<nav x-data="{ open: false }" class="border-gray-100 main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Primary Navigation Menu -->
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i> <span class="brand-text font-weight-light">Logi<b>Gate</b></span>
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <div class="navbar-search-block">
                <form class="form-inline">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-navbar" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <li class="nav-item">
            <!-- Settings Dropdown -->
            <div class="ms-3 relative">
                <x-dropdown align="right" width="48">
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
                        <!-- Account Management -->
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
                        <x-dropdown-link href="{{ route('profile.show') }}">
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

                        <div class="border-t border-gray-200"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}">
                                {{ __('Sair') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </li>
    </ul>
</nav>
