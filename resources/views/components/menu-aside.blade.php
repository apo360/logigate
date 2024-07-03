<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="https://logigate.ao" class="brand-link">
    <img src="{{ asset('dist/img/LOGIGATE.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">Logi<strong>Gate</strong></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
                {{ __('Dashboard') }}
                <i class="right fas fa-angle-left"></i>
            </p>
            </a>
        </li>

        @php
            $empresa = auth()->user()->empresas->first(); // Assumindo que a empresa está associada ao usuário autenticado
            $modulosAtivados = $empresa->subscricoes()->where('status', 'ATIVA')->pluck('modulo_id');
            $menus = \App\Models\Menu::whereIn('module_id', $modulosAtivados)
                        ->orderBy('order_priority')->get()->groupBy('module_id');
            $menusPrincipais = $menus->flatMap(function($moduleMenus) {
                return $moduleMenus->where('parent_id', null);
            });
            $totalMenusPrincipais = $menusPrincipais->count();
        @endphp

        @if($modulosAtivados->count() === 1 || $totalMenusPrincipais <= 10)
            @foreach($menusPrincipais as $menuPrincipal)
            <li class="nav-item">
                <a href="{{ route($menuPrincipal->route) }}" class="nav-link {{ request()->routeIs($menuPrincipal->route) ? 'active' : '' }}">
                <i class="nav-icon {{$menuPrincipal->icon}}"></i>
                <p>{{ __($menuPrincipal->menu_name) }}</p>
                </a>
                @php
                $submenus = $menuPrincipal->children; // Assumindo que você tem uma relação definida para submenus
                @endphp
                @if($submenus->count() > 0)
                <ul class="nav nav-treeview">
                    @foreach($submenus as $submenu)
                    <li class="nav-item">
                        <a href="{{ route($submenu->route) }}" class="nav-link {{ request()->routeIs($submenu->route) ? 'active' : '' }}">
                        <i class="{{$submenu->icon}} nav-icon"></i>
                        <p>{{ __($submenu->menu_name) }}</p>
                        </a>
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
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                <p>
                    {{ __($modulo->module_name) }}
                    <i class="right fas fa-angle-left"></i>
                </p>
                </a>
                <ul class="nav nav-treeview">
                @foreach($menusDoModulo as $menu)
                    @if(is_null($menu->parent_id))
                    <li class="nav-item">
                        <a href="{{ route($menu->route) }}" class="nav-link {{ request()->routeIs($menu->route) ? 'active' : '' }}">
                        <p>{{ __($menu->menu_name) }}</p>
                        </a>
                        @php
                        $submenus = $menu->submenus; // Assumindo que você tem uma relação definida para submenus
                        @endphp
                        @if($submenus->count() > 0)
                        <ul class="nav nav-treeview">
                            @foreach($submenus as $submenu)
                            <li class="nav-item">
                                <a href="{{ route($submenu->route) }}" class="nav-link {{ request()->routeIs($submenu->route) ? 'active' : '' }}">
                                <p>{{ __($submenu->menu_name) }}</p>
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

        <li class="nav-item">
            <a href="#" class="nav-link">
            <i class="nav-icon fas fa-gear"></i>
            <p> AGT <i class="right fas fa-angle-left"></i> </p>
            </a>
        </li>

        <li class="nav-item">
            <a href="#" class="nav-link">
            <i class="nav-icon fab fa-whatsapp"></i>
            <p> Whatsapp Empresa <i class="right fas fa-angle-left"></i> </p>
            </a>
        </li>
        <hr>
        <!-- API -->
        <li class="nav-item">
            <a href="#" class="nav-link">
            <i class="nav-icon fab fa-link"></i>
            <p> APIs <i class="right fas fa-angle-link"></i> </p>
            </a>
        </li>
        </ul>
    </nav>
    <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>