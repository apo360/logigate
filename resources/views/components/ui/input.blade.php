@props([
    'name',
    'type' => 'text',
    'label' => null,
    'placeholder' => '',
    'hint' => null,
    'icon' => null,
    'prefix' => null,
    'suffix' => null,
    'datalist' => null, // Novo prop para datalist

    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'autofocus' => false,
    'autocomplete' => null,
    'col' => null,

    'options' => [],
    'rows' => 3,
    'currency' => null,
    'wrapperClass' => '',

    // Select Search
    'model' => null,
    'displayField' => null,
    'extraField' => null,
    'searchField' => null,
    'field' => null,
    'key' => null,
    'emptyMessage' => 'Nenhum resultado encontrado.',
    'where' => [],
])

@php
    $inputId  = $attributes->get('id', $name.'-input');
    $errorKey = str_replace(['[',']'], ['.',''], $name);

    $baseClass = 'w-full rounded-lg px-3 py-2 bg-slate-900 border border-slate-700 
                text-slate-100 text-sm focus:outline-none focus:ring-1 focus:ring-[var(--lg-primary)] 
                focus:border-[var(--lg-primary)] disabled:opacity-50 disabled:cursor-not-allowed $col';
@endphp

<div class="w-full mb-4 {{ $wrapperClass }}">

    {{-- LABEL --}}
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="{{ $icon }} text-gray-400"></i>
            </div>
        @endif
        
        @if($prefix)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500">{{ $prefix }}</span>
            </div>
        @endif

        {{-- TEXTAREA --}}
        @if($type === 'textarea')
            <textarea
                id="{{ $inputId }}"
                name="{{ $name }}"
                rows="{{ $rows }}"
                placeholder="{{ $placeholder ?: $label }}"
                wire:model.defer="form.{{ $name }}"
                @required($required)
                @if($readonly) readonly @endif
                @disabled($disabled)
                {{ $attributes->except(['id','class'])->merge(['class' => $baseClass]) }}
            >{{ old($name, $attributes->get('value')) }}</textarea>

        {{-- SELECT NORMAL --}}
        @elseif($type === 'select')
            <select
                id="{{ $inputId }}"
                name="{{ $name }}"
                wire:model.defer="form.{{ $name }}"
                @required($required)
                @disabled($disabled)
                {{ $attributes->except(['id','class'])->merge(['class' => $baseClass]) }}
            >
                @if($placeholder || !$required)
                    <option value="">{{ $placeholder ?: 'Selecionar…' }}</option>
                @endif

                @foreach($options as $value => $text)
                    <option value="{{ $value }}"
                        @selected((string)old($name, $attributes->get('value')) === (string)$value)
                    >
                        {{ $text }}
                    </option>
                @endforeach
            </select>

        {{-- CHECKBOX --}}
        @elseif($type === 'checkbox')
            <label class="inline-flex items-center gap-2 text-xs text-slate-200">
                <input
                    type="checkbox"
                    id="{{ $inputId }}"
                    name="{{ $name }}"
                    value="1"
                    @checked(old($name, $attributes->get('value')))
                    @disabled($disabled)
                    class="rounded border-slate-600 bg-slate-900 text-[var(--lg-primary)] focus:ring-[var(--lg-primary)]"
                >
                <span>{{ $label }}</span>
            </label>

        {{-- RADIO --}}
        @elseif($type === 'radio')
            <div class="flex flex-wrap gap-3 text-xs text-slate-200">
                @foreach($options as $value => $text)
                    <label class="inline-flex items-center gap-2">
                        <input
                            type="radio"
                            name="{{ $name }}"
                            value="{{ $value }}"
                            @checked((string)old($name, $attributes->get('value')) === (string)$value)
                            @disabled($disabled)
                            class="text-[var(--lg-primary)] bg-slate-900 border-slate-600 focus:ring-[var(--lg-primary)]"
                        >
                        <span>{{ $text }}</span>
                    </label>
                @endforeach
            </div>

        {{-- FILE --}}
        @elseif($type === 'file')
            <input
                type="file"
                id="{{ $inputId }}"
                name="{{ $name }}"
                wire:model.defer="form.{{ $name }}"
                @required($required)
                @disabled($disabled)
                {{ $attributes->except(['id','class'])->merge([
                    'class' => 'block w-full text-xs text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[var(--lg-primary)] file:text-white hover:file:bg-[var(--lg-primary-soft)] bg-slate-900 border border-slate-700 rounded-lg',
                ]) }}
            >

        {{-- SELECT SEARCH --}}
        @elseif($type === 'select-search')
            <div class="relative" wire:ignore>
                <livewire:components.select-search
                    :model="$model"
                    :display-field="$displayField"
                    :extra-field="$extraField"
                    :search-field="$searchField"
                    :where="$where"
                    :field="$field"
                    wire:key="ss-{{ $name }}"
                />

                @if(in_array($name, ['customer_id', 'exportador_id']))
                    <button type="button"
                        wire:click="$dispatch('openQuickModal', '{{ $name }}')"
                        class="absolute right-2 top-2 text-[10px] text-indigo-400 hover:text-indigo-300">
                        + Novo {{ $name === 'customer_id' ? 'Cliente' : 'Exportador' }}
                    </button>
                @endif
            </div>

        {{-- PASSWORD --}}
        @elseif($type === 'password')
            <input
                id="{{ $inputId }}"
                name="{{ $name }}"
                type="password"
                placeholder="{{ $placeholder ?: $label }}"
                wire:model.defer="form.{{ $name }}"
                @required($required)
                @readonly($readonly)
                @disabled($disabled)
                {{ $attributes->except(['id','class'])->merge(['class' => $baseClass]) }}
            >

        {{-- INPUT PADRÃO / MONEY / NUMBER / DATE --}}
        @else
            <div class="relative flex items-stretch">

                @if($prefix || $icon)
                    <span class="inline-flex items-center px-2 text-[11px] text-slate-400 bg-slate-900 border border-r-0 border-slate-700 rounded-l-lg">
                        @if($icon)
                            <i class="{{ $icon }} text-[10px]"></i>
                        @else
                            {{ $prefix }}
                        @endif
                    </span>
                @endif

                <input
                    id="{{ $inputId }}"
                    name="{{ $name }}"
                    type="{{ $type === 'money' ? 'text' : $type }}"
                    wire:model.defer="form.{{ $name }}"
                    value="{{ old($name, $attributes->get('value')) }}"
                    placeholder="{{ $placeholder ?: $label }}"
                    @required($required)
                    @if($readonly) readonly @endif
                    @disabled($disabled)

                    {{ $attributes->except(['id','class'])->merge([
                        'class' => $baseClass.($prefix||$icon ? ' rounded-l-none' : ''),
                    ]) }}

                    {{-- MONEY MASK --}}
                    @if($type === 'money')
                        x-data
                        x-on:input="$el.value = $el.value.replace(/[^0-9,.]/g,'')"
                        x-on:blur="
                            if($el.value){
                                let v = parseFloat($el.value.replace(/\./g,'').replace(',','.'));
                                $el.value = v.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                        "
                    @endif
                >

                @if($suffix || $currency)
                    <span class="inline-flex items-center px-2 text-[11px] text-slate-400 bg-slate-900 border border-l-0 border-slate-700 rounded-r-lg">
                        {{ $suffix ?: $currency }}
                    </span>
                @endif
            </div>
        @endif

        @if($suffix)
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <span class="text-gray-500">{{ $suffix }}</span>
            </div>
        @endif
    </div>
    {{-- HINT --}}
    @if($hint)
        <p class="mt-1 text-[11px] text-slate-400">{{ $hint }}</p>
    @endif

    {{-- ERROR --}}
    @error($errorKey)
        <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p>
    @enderror

    <style>
        .select-search {
            @apply w-full rounded-lg px-3 py-2 bg-slate-900 border border-slate-700 text-slate-100 text-sm focus:outline-none focus:ring-1 focus:ring-[var(--lg-primary)] focus:border-[var(--lg-primary)] disabled:opacity-50 disabled:cursor-not-allowed;
        }

        /* Adicionar ao seu CSS */
        input[list]::-webkit-calendar-picker-indicator {
            opacity: 1;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E") no-repeat center;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        /* Estilo para sugestões em destaque */
        .sugestao-destaque {
            background-color: #f0f9ff;
            border-left: 3px solid #3b82f6;
            padding-left: 8px;
        }
    </style>
</div>
