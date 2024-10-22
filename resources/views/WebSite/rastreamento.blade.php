<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastreamento de Documentos</title>
</head>
<body>
<div class="container">
        <h1>Consulta de Licenciamento</h1>
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('resultado.consulta') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="codigo_licenciamento">Código do Licenciamento:</label>
                <input type="text" name="codigo_licenciamento" id="codigo_licenciamento" class="form-control" placeholder="Insira o código do licenciamento">
            </div>

            <button type="submit" class="btn btn-primary">Consultar</button>
        </form>
    </div>
</body>
</html>