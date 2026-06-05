<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>Logigate | Portal do Cliente - Acesso</title>
</head>
<body>
    <h2>Portal do Cliente - Acesso</h2>
    <p>Bem-vindo ao Portal do Cliente!</p>
    <p>Faça login para acessar suas informações e serviços personalizados.</p>

    <div>
        <form action="{{ route('portal-cliente.login.submit') }}" method="POST">
            @csrf
            <div>
                <label for="email">NIF, Nº Telefone ou Email:</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div>
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>

    <div>
        <p>Esqueceu sua senha? <a href="#">Clique aqui para recuperar</a>.</p>
        <p>Não tem uma conta? <a href="#">Contacte o seu despachante</a>.</p>
    </div>
</body>
</html>