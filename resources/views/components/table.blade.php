@props([
    'data' => [],
    'columns' => [], // ['label'=>'Nome','key'=>'cliente.nome']
    'hoverable' => true,
    'striped' => true,
])

<div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 
            rounded-2xl shadow-sm overflow-hidden">

    {{-- HEADER --}}
    <div class="flex justify-between items-center px-4 py-3 border-b 
                border-gray-200 dark:border-gray-700">

        <div>
            <h3 class="font-semibold text-gray-700 dark:text-gray-200">
                {{ $title ?? 'Lista' }}
            </h3>
            @if(isset($subtitle))
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        <div class="flex gap-2">
            {{ $actions ?? '' }}
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-gray-700 dark:text-gray-300"
               wire:loading.class="opacity-50 cursor-progress">
            
            {{-- HEADER COLUMNS --}}
            <thead class="bg-gradient-to-r from-logigate-dark to-logigate-primary text-white uppercase text-xs">
                <tr>
                    @foreach($columns as $col)
                        <th class="px-4 py-2 text-left whitespace-nowrap">
                            {{ $col['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            {{-- ROWS --}}
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700
                {{ $striped ? 'odd:bg-gray-50 dark:odd:bg-gray-800' : '' }}">
                {{ $rows }}
            </tbody>
        </table>
    </div>

    {{-- FOOTER --}}
    @if(isset($footer))
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-gray-500 text-sm">
            {{ $footer }}
        </div>
    @endif
</div>
