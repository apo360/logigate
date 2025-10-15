<nav x-data="{ open: false, notificationsOpen: false }" class="bg-white border-b border-gray-100 shadow-sm fixed w-full top-0 z-50">
    <!-- Container Principal -->
    <div class="max-w-7xl mx-auto px-12 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo e Menu Hambúrguer -->
            <div class="flex items-center">
                <!-- Botão do Menu Hambúrguer (para mobile) -->
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <!-- Logo -->
                <a href="#" class="ml-4 flex items-center">
                    <span class="text-xl font-semibold text-gray-800">Logi<b class="text-blue-600">Gate</b></span>
                </a>
            </div>

            <!-- Barra de Pesquisa -->
            <div class="flex items-center">
                <form class="hidden md:flex">
                    <div class="relative">
                        <input type="search" placeholder="Pesquisar..." class="w-64 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="absolute right-0 top-0 mt-2 mr-3 text-gray-500 hover:text-blue-600">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Barra de tempo da subscrição -->
            <div class="w-full max-w-md bg-white p-4 rounded-2xl shadow-md">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600 font-medium">Tempo de subscrição</span>

                    @php
                        use Carbon\Carbon;
                        use Illuminate\Support\Str;

                        $empresa = auth()->user()->empresas->first();
                        $subscricao = $empresa?->subscricoes()->latest('data_expiracao')->first();

                        $startDate = $subscricao?->data_inicio ? Carbon::parse($subscricao->data_inicio) : Carbon::now();
                        $endDate   = $subscricao?->data_expiracao ? Carbon::parse($subscricao->data_expiracao) : Carbon::now();
                        $currentDate = Carbon::now();

                        $totalDays = max($startDate->diffInDays($endDate), 1); // evita divisão por zero
                        $daysLeft  = $currentDate->diffInDays($endDate, false); // negativo se expirado

                        $progress = max(0, min(100, 100 - (($totalDays - max($daysLeft, 0)) / $totalDays) * 100));
                    @endphp

                    @if ($daysLeft >= 0)
                        <span class="text-sm text-gray-500"
                            title="Expira em {{ $endDate->format('d/m/Y') }}">
                            {{ $daysLeft }} {{ Str::plural('dia', $daysLeft) }} restantes
                        </span>
                    @else
                        <span class="text-sm text-red-500"
                            title="Expirou em {{ $endDate->format('d/m/Y') }}">
                            Expirada há {{ abs($daysLeft) }} {{ Str::plural('dia', abs($daysLeft)) }}
                        </span>
                    @endif
                </div>

                <!-- Barra de progresso -->
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div class="h-3 rounded-full transition-all duration-700 ease-in-out
                        {{ $daysLeft > 10 ? 'bg-green-500' : ($daysLeft > 5 ? 'bg-yellow-500' : 'bg-red-500') }}"
                        style="width: {{ $daysLeft < 0 ? '100%' : $progress . '%' }}">
                    </div>
                </div>

                <!-- Rodapé informativo -->
                <!-- <div class="flex justify-between text-xs text-gray-400 mt-2">
                    <span>Início: {{ $startDate->format('d/m/Y') }}</span>
                    <span>Fim: {{ $endDate->format('d/m/Y') }}</span>
                </div> -->
            </div>

            <!-- Menu de Notificações e Usuário -->
            <div class="flex items-center space-x-4">
                <!-- Menu de Notificações -->
                <div class="relative">
                    <button @click="notificationsOpen = !notificationsOpen" class="p-2 text-gray-500 hover:text-blue-600 focus:outline-none transition duration-150 ease-in-out">
                        <i class="fas fa-bell text-lg"></i>
                        <!-- Indicador de Notificações Não Lidas -->
                        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">3</span>
                    </button>

                    <!-- Dropdown de Notificações -->
                    <div x-show="notificationsOpen" @click.away="notificationsOpen = false" class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg z-50">
                        <div class="p-4">
                            <h3 class="text-sm font-semibold text-gray-800">Notificações</h3>
                            <div class="mt-2 space-y-2">
                                <!-- Exemplo de Notificação -->
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                                    Nova mensagem recebida
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                                    Atualização do sistema disponível
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg">
                                    Tarefa concluída com sucesso
                                </a>
                            </div>
                            <div class="mt-4">
                                <a href="#" class="block text-center text-sm text-blue-600 hover:text-blue-800">
                                    Ver todas as notificações
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menu do Usuário -->
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
    </div>
</nav>