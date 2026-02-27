<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Pagamento Confirmado | Active a sua conta </title>
</head>
<body>

    <h1>Pagamento Recebido com Sucesso!</h1>
    <p>ID do Pagamento: {{ $payment->id }}</p>
    <p>Estado do Pagamento: {{ $payment->status }}</p>
    <p>Montante: {{ $payment->amount }} {{ $payment->currency }}</p>
    <p>Data do Pagamento: {{ $payment->created_at }}</p>

    <div class="">
        <span>Clique a baixo para entrar em sua conta e iniciar a sua experiência com aplicação, enquanto preenche os campos príncipais para um bom funcionamento</span>
    </div>
    
</body>
</html>