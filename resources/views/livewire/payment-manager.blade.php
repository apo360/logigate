<div>
    <h1>Pagamento</h1>
    <form wire:submit.prevent="processPayment">
        <div>
            <strong>Total: ${{ $totalPrice }}</strong>
        </div>
        <div>
            <select wire:model="selectedMetodo">
                @foreach($metodos as $metodo)
                    <option value="{{ $metodo->id }}">{{ $metodo->metodo }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit">Pagar</button>
    </form>
</div>

