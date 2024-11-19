@props(['name', 'options' => [], 'selected' => null])

<select {{ $attributes->merge(['class' => 'form-control']) }} name="{{ $name }}" id="{{ $name }}">
    <option value="">Select</option>
    @foreach ($options as $value => $label)
        <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
</select>
