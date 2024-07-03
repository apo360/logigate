<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>
        <h1>Verificar Código OTP</h1>
        @if (session('message'))
            <p>{{ session('message') }}</p>
        @endif
        <form method="POST" action="{{ route('confirmaOtp') }}">
            @csrf
            <span>Usuario: {{$email}}</span>
            <div>
                <x-label for="otp" value="{{ __('Código OTP') }}" />
                <x-input id="otp" class="block mt-1 w-full" name="otp" required autofocus />
            </div>
            @error('otp')
                <p>{{ $message }}</p>
            @enderror
            <x-button class="mt-4">
                    {{ __('Verificar') }}
            </x-button>
        </form>
        <form method="POST" action="{{ url('/resend-otp') }}">
            @csrf
            <x-button class="mt-4">
                    {{ __('Reenviar Código OTP') }}
            </x-button>
        </form>
    </x-authentication-card>
</x-guest-layout>