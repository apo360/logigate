@props(['disabled' => false, 'namebutton', 'idButton'])

<div class="input-group">
    <input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!}>

    <div class="input-group-append">
        <a href="#" class="btn btn-dark" id="{{ $idButton }}"> {{ $namebutton }}</a>
    </div>
</div>