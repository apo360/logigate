<!-- resources/views/livewire/menu-dinamico.blade.php -->

<div class="space-y-1">

    <!-- ========== FIXED ITEMS ========== -->
    <a href="{{ route('leander.dashboard') }}"
       class="flex items-center p-2 rounded-lg 
              text-white/80 hover:bg-white/10 mb-1
              {{ request()->routeIs('leander.dashboard') ? 'bg-logigate-primary text-white' : '' }}">
        <i class="fa fa-tasks text-logigate-secondary"></i>
        <span class="ml-3">Tarefas</span>
    </a>

    <a href="{{ route('dashboard') }}"
       class="flex items-center p-2 rounded-lg 
              text-white/80 hover:bg-white/10 mb-4
              {{ request()->routeIs('dashboard') ? 'bg-logigate-primary text-white' : '' }}">
        <i class="fa fa-gauge text-logigate-secondary"></i>
        <span class="ml-3">Dashboard</span>
    </a>

    <!-- ========== MENUS DINÂMICOS ========== -->
    @if(count($modulosAtivos) === 1 || count($menusPrincipais) <= 10)

        @foreach($menusPrincipais as $menu)
            <div x-data="{ open: {{ request()->routeIs($menu['route']) ? 'true' : 'false' }} }">

                <a href="{{ $menu['route'] == '#' ? '#' : route($menu['route']) }}"
                   @if(isset($menu['children']) && count($menu['children']) > 0)
                       @click.prevent="open = !open"
                   @endif
                   class="flex items-center p-2 rounded-lg 
                          text-white/80 hover:bg-white/10
                          {{ request()->routeIs($menu['route']) ? 'bg-logigate-primary text-white' : '' }}">

                    <i class="{{ $menu['icon'] }} text-logigate-secondary"></i>
                    <span class="ml-3">{{ $menu['menu_name'] }}</span>

                    @if(isset($menu['children']) && count($menu['children']) > 0)
                        <i class="ml-auto fa text-white/50"
                           :class="open ? 'fa-angle-down' : 'fa-angle-left'"></i>
                    @endif
                </a>

                @if(isset($menu['children']) && count($menu['children']) > 0)
                    <div x-show="open" x-transition class="ml-6 mt-2 space-y-1">
                        @foreach($menu['children'] as $child)
                            @include('livewire.partials.sub-menu', ['submenu' => $child])
                        @endforeach
                    </div>
                @endif

            </div>
        @endforeach

    @else

        <!-- Agrupar por módulos -->
        @foreach($menusPorModulo as $moduleId => $menus)
            @php
                $modulo = \App\Models\Modulo::find($moduleId);
                $routes = array_column($menus, 'route');
                $isActive = in_array(request()->route()->getName(), $routes);
            @endphp

            <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="text-xs uppercase text-logigate-tertiary font-bold mt-4 pl-2">

                <button @click="open = !open" class="flex items-center p-2 w-full text-left rounded-lg text-white/80 hover:bg-white/10">
                    <span class="ml-1 font-semibold">{{ $modulo->module_name }}</span>
                    <i class="ml-auto fa text-white/50" :class="open ? 'fa-angle-down' : 'fa-angle-left'"></i>
                </button>

                <div x-show="open" x-transition class="ml-6 mt-2 space-y-1">
                    @foreach($menus as $menu)
                        @if($menu['parent_id'] === null)
                            @include('livewire.partials.sub-menu', ['submenu' => $menu])
                        @endif
                    @endforeach
                </div>

            </div>
        @endforeach

    @endif

    <!-- SEPARADOR -->
    <hr class="border-white/10 my-4">

    <!-- RELATÓRIOS -->
    <a href="{{ route('relatorio.licenciamento') }}"
       class="flex items-center p-2 rounded-lg text-white/80 hover:bg-white/10">
        <i class="fa fa-file-pdf text-logigate-secondary"></i>
        <span class="ml-3">Relatórios</span>
    </a>

    <!-- ARQUIVOS -->
    <a href="{{ route('arquivos.index') }}"
       class="flex items-center p-2 rounded-lg text-white/80 hover:bg-white/10">
        <i class="fa fa-file-alt text-logigate-secondary"></i>
        <span class="ml-3">Arquivos</span>
    </a>

    <!-- CONFIGURAÇÕES -->
    <a href="#">
        <div class="flex items-center p-2 rounded-lg text-white/80 hover:bg-white/10">
            <i class="fa fa-cog text-logigate-secondary"></i>
            <span class="ml-3">Configurações</span>
        </div>
    </a>

</div>

