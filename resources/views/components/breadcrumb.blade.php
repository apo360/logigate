<nav aria-label="breadcrumb" class="bg-gray-50 p-2 rounded-lg shadow-sm">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        @foreach ($items as $item)
            <li class="flex items-center">
                @if (!$loop->last)
                    <a href="{{ $item['url'] }}" class="hover:text-blue-500 transition duration-200">
                        {{ $item['name'] }}
                    </a>
                    <span class="mx-2 text-gray-400">/</span> <!-- Separador -->
                @else
                    <span class="text-gray-900 font-medium">{{ $item['name'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>