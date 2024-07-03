<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logigate - Modulos</title>
</head>
<body>
    <h2>Nossos Modulos</h2>

    <ul>
        @foreach($modulos as $modulo)
            @if(!$modulo->parent_id)
                <li>{{$modulo->module_name}} </li>
                <a href="" class="brn btn-primary">Comprar</a>
            @endif
        @endforeach
    </ul>

</body>
</html>