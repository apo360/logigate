<!DOCTYPE html>
<html>
<head>
    <title>Login Suspeito Detectado</title>
</head>
<body>
    <h1>Olá, {{ $userName }}</h1>
    <p>Detectamos um login suspeito na sua conta de um novo endereço IP:</p>
    <p>IP: {{ $ipAddress }}</p>
    <p>Se você não reconhece essa atividade, por favor, altere sua senha imediatamente.</p>
    <p><a href="{{ url('/password/reset') }}">Alterar Senha</a></p>
    <p>Se reconhece o login, nenhuma ação adicional é necessária.</p>
    <p>Atenciosamente, Sua Equipe de Segurança</p>
</body>
</html>
