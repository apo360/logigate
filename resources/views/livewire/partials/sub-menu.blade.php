<!-- resources/ -->
@php
    $children = $submenu['children'] ?? [];
    $isActive = request()->routeIs($submenu['route']);
@endphp

<div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">

    <a href="{{ $submenu['route'] == '#' ? '#' : route($submenu['route']) }}"
       @if(count($children) > 0)
           @click.prevent="open = !open"
       @endif
       class="flex items-center p-2 rounded-lg 
              text-white/80 hover:bg-white/10
              {{ $isActive ? 'bg-logigate-primary text-white' : '' }}">
       
        <i class="{{ $submenu['icon'] }} text-logigate-secondary"></i>
        <span class="ml-3">{{ $submenu['menu_name'] }}</span>

        @if(count($children) > 0)
            <i class="ml-auto fa text-white/50"
               :class="open ? 'fa-angle-down' : 'fa-angle-left'"></i>
        @endif
    </a>

    @if(count($children) > 0)
        <div x-show="open" x-transition class="ml-6 mt-2 space-y-1">
            @foreach($children as $child)
                @include('livewire.partials.sub-menu', ['submenu' => $child])
            @endforeach
        </div>
    @endif

</div>
