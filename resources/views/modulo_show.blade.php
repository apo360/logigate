<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

</head>
<body>
    <form method="GET" action="">
        @csrf

        @foreach($modulo as $row)
            {{ $row->module_name}}
            <input type="hidden" name="module" id="module" value="{{ $row->module_id }}">
            <br>
            @if($row->submodules->count() > 0)
                <div class="card-body">
                    <ol>
                        @foreach($row->submodules as $submodule)
                            <li>
                                <label>
                                    <input type="checkbox" wire:model="selectedSubmodules" value="{{ $row->id }}" class="submodule-checkbox" data-module-id="{{ $row->id }}">
                                    {{ $submodule->module_name }} - Kz{{ number_format($submodule->price, 2, ',', '.') }}
                                </label>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif
            <p> <input type="text" value="{{$row->price}}" id="price_module" name="price_module"> </p>

            <div>
                <div class="form-group">
                    <h4>Escolha do Plano</h4>
                    <label for="plan">Plano:</label>
                    <select class="form-control" id="plan" required name="plan">
                        <option value="">Selecione...</option>
                        <option value="base" {{ session('plan') == 'base' ? 'selected' : '' }}>Base - {{$row->price}}</option>
                        <option value="premium" {{ session('plan') == 'premium' ? 'selected' : '' }}>Premium - {{$row->price*1.5}}</option>
                        <option value="negocios" {{ session('plan') == 'negocios' ? 'selected' : '' }}>Negócios - {{$row->price*2.5}}</option>
                        <option value="empresa" {{ session('plan') == 'empresa' ? 'selected' : '' }}>Empresa - {{$row->price*3.5}}</option>
                    </select>
                </div>
            </div>

        @endforeach
        <button type="submit" class="btn btn-primary">Subscrever</button>
    </form>
</body>
</html>