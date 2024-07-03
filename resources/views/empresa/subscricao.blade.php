<x-app-layout>
    <script>
        function updateTotalPrice() {
            const checkboxes = document.querySelectorAll('input[name="selected_modulos[]"]:checked');
            let totalPrice = 0;
            checkboxes.forEach((checkbox) => {
                totalPrice += parseFloat(checkbox.dataset.price);
            });
            document.getElementById('totalPrice').innerText = totalPrice.toFixed(2) + ' Kz';
            document.getElementById('total_price').value = totalPrice.toFixed(2);
        }
    </script>

    <h1>Escolha os Módulos</h1>
    <form action="{{ route('payment.pay') }}" method="POST">
        @csrf
        <input type="hidden" name="empresaId" id="empresaid" value="{{$empresa->id}}">
        @foreach($modulos as $modulo)
            @if(is_null($modulo->parent_id))
                <div>
                    <strong>{{ $modulo->module_name }} - {{ $modulo->price }} Kz</strong>
                    <div style="margin-left: 20px;">
                        @foreach($modulos as $submodulo)
                            @if($submodulo->parent_id == $modulo->id)
                                <div>
                                    <input type="checkbox" name="selected_modulos[]" value="{{ $submodulo->id }}" data-price="{{ $submodulo->price }}" onchange="updateTotalPrice()">
                                    {{ $submodulo->module_name }} - {{ $submodulo->price }} Kz
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
        <div>
            <strong>Total: <span id="totalPrice">0.00 Kz</span></strong>
            <input type="hidden" name="total_price" id="total_price" value="0.00">
        </div>
        <div>
            <label for="metodo_pagamento">Método de Pagamento:</label>
            <select name="metodo_pagamento_id">
                @foreach($metodosPagamento as $metodo)
                    <option value="{{ $metodo->id }}">{{ $metodo->metodo }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit">Subscribir</button>
    </form>

</x-app-layout>
