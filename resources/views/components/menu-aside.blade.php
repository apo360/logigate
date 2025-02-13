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
                    $empresa = auth()->user()->empresas->first(); // Assumindo que a empresa está associada ao usuário autenticado
                    $modulosAtivados = $empresa->subscricoes()->where('status', 'ATIVA')->pluck('modulo_id');
                    $menus = \App\Models\Menu::whereIn('module_id', $modulosAtivados)->orderBy('order_priority')->get()->groupBy('module_id');
                    $menusPrincipais = $menus->flatMap(function($moduleMenus) {
                        return $moduleMenus->where('parent_id', null);
                    });
                    $totalMenusPrincipais = $menusPrincipais->count();
                @endphp

                @if($modulosAtivados->count() === 1 || $totalMenusPrincipais <= 10)
                    @foreach($menusPrincipais as $menuPrincipal)
                        <li>
                            <a href="{{ $menuPrincipal->route == '#' ? '#' : route($menuPrincipal->route) }}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200 {{ request()->routeIs($menuPrincipal->route) ? 'bg-gray-700' : '' }}">
                                <i class="{{ $menuPrincipal->icon }} text-gray-400"></i>
                                <span class="ml-3">{{ __($menuPrincipal->menu_name) }}</span>
                                @if ($menuPrincipal->children->count() > 0)
                                    <i class="fas fa-angle-left ml-auto text-gray-400"></i>
                                @endif
                            </a>
                            @if ($menuPrincipal->children->count() > 0)
                                <ul class="ml-6 mt-2 space-y-2">
                                    @foreach($menuPrincipal->children as $submenu)
                                        <li>
                                            <a href="{{ $submenu->route == '#' ? '#' : route($submenu->route) }}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200 {{ request()->routeIs($submenu->route) ? 'bg-gray-700' : '' }}">
                                                <i class="{{ $submenu->icon }} text-gray-400"></i>
                                                <span class="ml-3">{{ __($submenu->menu_name) }}</span>
                                                @if ($submenu->children->count() > 0)
                                                    <i class="fas fa-angle-left ml-auto text-gray-400"></i>
                                                @endif
                                            </a>
                                            @if ($submenu->children->count() > 0)
                                                <ul class="ml-6 mt-2 space-y-2">
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
                        @endphp
                        <li>
                            <a href="#" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                <span class="ml-3">{{ __($modulo->module_name) }}</span>
                                <i class="fas fa-angle-left ml-auto text-gray-400"></i>
                            </a>
                            <ul class="ml-6 mt-2 space-y-2">
                                @foreach($menusDoModulo as $menu)
                                    @if(is_null($menu->parent_id))
                                        <li>
                                            <a href="{{ route($menu->route) }}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200 {{ request()->routeIs($menu->route) ? 'bg-gray-700' : '' }}">
                                                <i class="{{ $menu->icon }} text-gray-400"></i>
                                                <span class="ml-3">{{ __($menu->menu_name) }}</span>
                                            </a>
                                            @php
                                                $submenus = $menu->submenus; // Assumindo que você tem uma relação definida para submenus
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

                <!-- Contabilidade -->
                <li>
                    <a href="#" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-coins text-gray-400"></i>
                        <span class="ml-3">{{ __('Contabilidade') }}</span>
                        <i class="fas fa-angle-left ml-auto text-gray-400"></i>
                    </a>
                    <ul class="ml-6 mt-2 space-y-2">
                        <li>
                            <a href="{{ route('customers.index') }}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200 {{ request()->routeIs('customers.index') ? 'bg-gray-700' : '' }}">
                                <i class="fas fa-file-invoice-dollar text-gray-400"></i>
                                <span class="ml-3">{{ __('Mapa de Impostos e Tarifas') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customers.listagem_cc') }}" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200 {{ request()->routeIs('customers.listagem_cc') ? 'bg-gray-700' : '' }}">
                                <i class="fas fa-file-alt text-gray-400"></i>
                                <span class="ml-3">{{ __('Pauta Aduaneira') }}</span>
                            </a>
                        </li>
                    </ul>
                </li>

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
                <li>
                    <a href="#" class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-link text-gray-400"></i>
                        <span class="ml-3">APIs</span>
                        <i class="fas fa-angle-left ml-auto text-gray-400"></i>
                    </a>
                    <ul class="ml-6 mt-2 space-y-2">
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