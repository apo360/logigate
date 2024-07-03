<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar OTP</title>
</head>
<body>
    <h1>Verificar Código OTP</h1>
    @if (session('message'))
        <p>{{ session('message') }}</p>
    @endif
    <form method="POST" action="{{ route('confirmaOtp') }}">
        @csrf
        <span>Usuario: {{$email}}</span>
        <label for="otp">Código OTP</label>
        <input type="text" name="otp" id="otp" required>
        @error('otp')
            <p>{{ $message }}</p>
        @enderror
        <button type="submit">Verificar</button>
    </form>
    <form method="POST" action="{{ url('/resend-otp') }}">
        @csrf
        <button type="submit">Reenviar Código OTP</button>
    </form>
</body>
</html>