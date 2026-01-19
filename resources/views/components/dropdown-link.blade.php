@props(['href' => null])

@if($href)
    <a href="{{ $href }}"
       {{ $attributes->merge([
           'class' => 'flex items-center px-4 py-2 text-xs text-gray-700 dark:text-gray-200
                       hover:bg-gray-100 dark:hover:bg-gray-800 transition'
       ]) }}>
        {{ $slot }}
    </a>
@else
    <button
        {{ $attributes->merge([
            'class' => 'w-full text-left flex items-center px-4 py-2 text-xs text-gray-700
                        dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition'
        ]) }}>
        {{ $slot }}
    </button>
@endif
