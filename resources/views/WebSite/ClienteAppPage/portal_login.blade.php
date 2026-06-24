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

    @if(session('status'))
        <p>{{ session('status') }}</p>
    @endif

    @if(session('error'))
        <p>{{ session('error') }}</p>
    @endif

    <div>
        <form action="{{ route('cliente.portal.login.submit') }}" method="POST">
            @csrf
            <div>
                <label for="login">NIF, Nº Telefone ou Email:</label>
                <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus>
                @error('login')
                    <p>{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <p>{{ $message }}</p>
                @enderror
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>

    <div>
        <p>Esqueceu sua senha? <a href="{{ route('cliente.portal.password.reset') }}">Clique aqui para recuperar</a>.</p>
        <p>Não tem uma conta? <a href="#">Contacte o seu despachante</a>.</p>
    </div>
</body>
</html>
