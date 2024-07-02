<!DOCTYPE html>
<html>
<head>
    <title>Pagamento</title>
</head>
<body>
    <h1>Pagamento</h1>
    <form action="{{ route('payment.pay') }}" method="POST">
        @csrf
        <div>
            <label for="metodo_pagamento">Método de Pagamento:</label>
            <select name="metodo_pagamento_id">
                @foreach($metodosPagamento as $metodo)
                    <option value="{{ $metodo->id }}">{{ $metodo->metodo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <strong>Total a Pagar: {{ $totalPrice }} Kz</strong>
            <input type="hidden" name="total_price" value="{{ $totalPrice }}">
        </div>
        <button type="submit">Pagar</button>
    </form>

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
</body>
</html>
