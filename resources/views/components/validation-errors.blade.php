@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'alert alert-danger']) }}>
        <div class="font-medium">{{ __('Desculpa! Algo está errado.') }}</div>

        <ul class="mt-3 list-disc list-inside text-sm ">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
