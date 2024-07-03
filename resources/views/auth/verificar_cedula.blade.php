<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

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

    </x-authentication-card>
</x-guest-layout>