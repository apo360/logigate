<div>
    <h1>Escolha os Módulos</h1>
    <form wire:submit.prevent="subscribe">
        @foreach($modulos as $modulo)
            @if(is_null($modulo->parent_id))
                <div>
                    <strong>{{ $modulo->module_name }} - {{ $modulo->price }} Kz</strong>
                    <div style="margin-left: 20px;">
                        @foreach($modulos as $submodulo)
                            @if($submodulo->parent_id == $modulo->id)
                                <div>
                                    <input type="checkbox" wire:model="selectedModulos" value="{{ $submodulo->id }}">
                                    {{ $submodulo->module_name }} - {{ $submodulo->price }} Kz
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
        <div>
            <strong>Total: {{ $totalPrice }} Kz</strong>
        </div>
        <button type="submit">Subscribir</button>
    </form>

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
</div>



