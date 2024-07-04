<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <form action="{{ route('cedula.verificar') }}" method="post">
            @csrf

            <div class="mt-4">
                <span style="font-family: 'Courier New', Courier, monospace; font-size: 24px;">Faça Login em LogiGate</span>
            </div>
            <hr>
            <div class="mt-4">
                <p>Logigate ID is a new personal profile for builders <a href="#">Ver mais</a></p>
            </div>

            <div class="mt-4">
                <x-label for="cedula" value="{{ __('Cedula') }}" />
                <x-input id="cedula" class="block mt-1 w-full" type="text" name="cedula" :value="old('cedula')" required autofocus />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Já tenho Registo!') }}
                </a>

                <x-button class="ms-4">
                    {{ __('Validar Cedula') }}
                </x-button>
            </div>
        </form>

        @if ($errors->any())
            <div class = ''>

                <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <p class="mt-4 font-medium text-red-600">{{ __('Pretende dar sequência ao cadastro manual?.') }} <a href="{{route('verificar.manual')}}" class="text-green-600">SIM</a></p>
            </div>
        @endif

    </x-authentication-card>
</x-guest-layout>