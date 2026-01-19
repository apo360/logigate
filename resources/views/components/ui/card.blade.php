@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm']) }}>
    @if($title || $actions)
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                @if($title)
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                        {{ $title }}
                    </h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
            @if($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-4">
        {{ $slot }}
    </div>
</div>
