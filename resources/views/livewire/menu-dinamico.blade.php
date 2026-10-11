<nav class="space-y-1" aria-label="Menu principal">
    @php
        // Compatibility for existing preview fixtures; normal requests use the resolver.
        $groups = $navigationGroups ?? collect($menusPorModulo ?? [])->map(fn ($items, $id) => ['label' => $nomesModulos[$id] ?? 'Módulo', 'items' => $items])->all();
    @endphp
    @foreach($groups as $code => $group)
        <section wire:key="navigation-group-{{ $code }}" class="pt-3">
            @if(config('navigation.grouped'))
                <h2 class="px-2 mb-2 text-xs font-semibold tracking-wider uppercase text-white/50">{{ $group['label'] }}</h2>
            @endif
            <div class="space-y-1">
                @foreach($group['items'] as $menu)
                    @include('livewire.partials.sub-menu', ['submenu' => $menu])
                @endforeach
            </div>
        </section>
    @endforeach
</nav>