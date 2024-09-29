<nav aria-label="breadcrumb">
    <ol class="breadcrumb" style="--bs-breadcrumb-divider: '{{ $separator }}';">
        @foreach ($items as $item)
            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" 
                aria-current="{{ $loop->last ? 'page' : '' }}">
                @if (!$loop->last)
                    <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                @else
                    {{ $item['name'] }}
                @endif
            </li>
        @endforeach
    </ol>
</nav>