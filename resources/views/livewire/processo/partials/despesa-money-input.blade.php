@php
    $placeholder = $placeholder ?? '0,00';
@endphp

<div wire:key="despesa-field-{{ $field }}">
    <label for="despesa-{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
    </label>

    <div class="relative">
        <input
            id="despesa-{{ $field }}"
            name="despesa_{{ $field }}"
            type="text"
            wire:model.blur="form.{{ $field }}"
            class="w-full px-3 py-2 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            placeholder="{{ $placeholder }}"
            x-mask:dynamic="$money($input, '.', ',', 2)"
        >

        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <span class="text-gray-500">Kz</span>
        </div>
    </div>

    @error("form.{$field}")
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
