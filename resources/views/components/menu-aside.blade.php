<aside class="main-sidebar inset-y-0 left-0 w-64 bg-gray-800 text-white shadow-lg elevation-4">
    <!-- Brand Logo -->
    <a href="https://logigate.ao" class="brand-link">
        <img src="{{ asset('dist/img/LOGIGATE.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Logi<strong>Gate</strong></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav>
            <ul class="space-y-2">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('dashboard') }}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-tachometer-alt text-gray-400"></i>
                        <span class="ml-3">{{ __('Dashboard') }}</span>
                    </a>
                </li>

                @php
                    use App\Models\PlanoModulo;
                    use App\Models\Menu;

                    $empresa = auth()->user()->empresas->first(); // Empresa associada ao usuário autenticado

                    // Buscar planos ativos dessa empresa
                    $planosAtivos = $empresa->subscricoes()
                        ->where('status', 'ATIVA')
                        ->pluck('plano_id');

                    // Buscar módulos associados aos planos ativos
                    $modulosAtivos = PlanoModulo::whereIn('plano_id', $planosAtivos)
                        ->pluck('modulo_id');

                    // Buscar menus pertencentes a esses módulos
                    $menus = Menu::whereIn('module_id', $modulosAtivos)
                        ->orderBy('order_priority')
                        ->get()
                        ->groupBy('module_id');

                    // Menus principais (sem pai)
                    $menusPrincipais = $menus->flatMap(fn($moduleMenus) =>
                        $moduleMenus->whereNull('parent_id')
                    );

                    $totalMenusPrincipais = $menusPrincipais->count();

                    // Verifica se a rota atual pertence a algum menu ativo
                    $isActive = $menusPrincipais->pluck('route')->contains(fn($route) => request()->routeIs($route));
                @endphp

                @if($modulosAtivos->count() === 1 || $totalMenusPrincipais <= 10)
                    @foreach($menusPrincipais as $menuPrincipal)
                        <li x-data="{ open: {{ request()->routeIs($menuPrincipal->route) ? 'true' : 'false' }} }">
                            <a href="{{ $menuPrincipal->route == '#' ? '#' : route($menuPrincipal->route) }}"
                            class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200 {{ request()->routeIs($menuPrincipal->route) ? 'bg-gray-700' : '' }}"
                            @if($menuPrincipal->children->count() > 0)
                                @click.prevent="open = !open"
                            @endif >
                                <i class="{{ $menuPrincipal->icon }} text-gray-400"></i>
                                <span class="ml-3">{{ __($menuPrincipal->menu_name) }}</span>
                                @if ($menuPrincipal->children->count() > 0)
                                    <i :class="open ? 'fas fa-angle-down' : 'fas fa-angle-left'" class="ml-auto text-gray-400 transition-all duration-200"></i>
                                @endif
                            </a>
                            @if ($menuPrincipal->children->count() > 0)
                                <ul x-show="open" x-transition class="ml-6 mt-2 space-y-2">
                                    @foreach($menuPrincipal->children as $submenu)
                                        <li x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
                                            <a href="{{ $submenu->route == '#' ? '#' : route($submenu->route) }}"
                                                class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200 {{ request()->routeIs($submenu->route) ? 'bg-gray-700' : '' }}"
                                                @if($submenu->children->count() > 0)
                                                    @click.prevent="open = !open"
                                                @endif >
                                                    <i class="{{ $submenu->icon }} text-gray-400"></i>
                                                    <span class="ml-3">{{ __($submenu->menu_name) }}</span>
                                                    @if ($submenu->children->count() > 0)
                                                        <i :class="open ? 'fas fa-angle-down' : 'fas fa-angle-left'" class="ml-auto text-gray-400 transition-all duration-200"></i>
                                                    @endif
                                            </a>
                                            @if ($submenu->children->count() > 0)
                                                <ul x-show="open" x-transition class="ml-6 mt-2 space-y-2">
                                                    @foreach($submenu->children as $sub_submenu)
                                                        <li>
                                                            <a href="{{ route($sub_submenu->route) }}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200 {{ request()->routeIs($sub_submenu->route) ? 'bg-gray-700' : '' }}">
                                                                <i class="{{ $sub_submenu->icon }} text-gray-400"></i>
                                                                <span class="ml-3">{{ __($sub_submenu->menu_name) }}</span>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                @else
                    @foreach($menus as $moduleId => $menusDoModulo)
                        @php
                            $modulo = \App\Models\Modulo::find($moduleId);
                            // Verifica se algum menu desse módulo está ativo para abrir o dropdown automaticamente
                            $isActive = $menusDoModulo->pluck('route')->contains(fn($route) => request()->routeIs($route));
                        @endphp
                        <li x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
                            <a href="#" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200"
                                @if($submenu->children->count() > 0)
                                                    @click.prevent="open = !open"
                                                @endif >
                                <span class="ml-3">{{ __($modulo->module_name) }}</span>
                                <i :class="open ? 'fas fa-angle-down' : 'fas fa-angle-left'" class="ml-auto text-gray-400 transition-all duration-200"></i>
                            </a>
                            <ul x-show="open" x-transition class="ml-6 mt-2 space-y-2">
                                @foreach($menusDoModulo as $menu)
                                    @if(is_null($menu->parent_id))
                                        <li>
                                            <a href="{{ route($menu->route) }}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200 {{ request()->routeIs($menu->route) ? 'bg-gray-700' : '' }}">
                                                <i class="{{ $menu->icon }} text-gray-400"></i>
                                                <span class="ml-3">{{ __($menu->menu_name) }}</span>
                                            </a>
                                            @php
                                                $submenus = $menu->submenus;
                                            @endphp
                                            @if($submenus->count() > 0)
                                                <ul class="ml-6 mt-2 space-y-2">
                                                    @foreach($submenus as $submenu)
                                                        <li>
                                                            <a href="{{ route($submenu->route) }}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200 {{ request()->routeIs($submenu->route) ? 'bg-gray-700' : '' }}">
                                                                <i class="{{ $submenu->icon }} text-gray-400"></i>
                                                                <span class="ml-3">{{ __($submenu->menu_name) }}</span>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                @endif

                <!-- Relatórios -->
                <li>
                    <a href="{{ route('relatorio.licenciamento')}}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-file-pdf text-gray-400"></i>
                        <span class="ml-3">Relatórios</span>
                    </a>
                </li>

                <!-- Arquivos -->
                <li>
                    <a href="{{ route('arquivos.index')}}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-file-alt text-gray-400"></i>
                        <span class="ml-3">Arquivos</span>
                    </a>
                </li>

                <!-- Divisor -->
                <hr class="border-gray-700 my-4">

                <!-- APIs -->
                <li x-data="{ open: false }">
                    <a href="#" @click.prevent="open = !open" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-link text-gray-400"></i>
                        <span class="ml-3">APIs</span>
                        <i :class="open ? 'fas fa-angle-down' : 'fas fa-angle-left'" class="ml-auto text-gray-400 transition-all duration-200"></i>
                    </a>
                    <ul x-show="open" x-transition class="ml-6 mt-2 space-y-2">
                        <li>
                            <a href="#" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                <i class="fas fa-gear text-gray-400"></i>
                                <span class="ml-3">AGT</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                <i class="fab fa-whatsapp text-gray-400"></i>
                                <span class="ml-3">Whatsapp Empresa</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>