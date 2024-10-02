<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form action="{{ route('password.change') }}" method="POST">
            @csrf
            <div>
                <label for="password">Nova Senha:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div>
                <label for="password_confirmation">Confirme a Nova Senha:</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>
            <button type="submit">Alterar Senha</button>
        </form>

    </x-authentication-card>
</x-guest-layout>
