<x-guest-layout>
    <style>
        .strength-weak {
            color: red;
        }

        .strength-medium {
            color: orange;
        }

        .strength-strong {
            color: green;
        }

        .match {
            color: green;
        }

        .no-match {
            color: red;
        }
    </style>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mt-4">
                <x-label for="cedula" value="{{ __('Cedula') }}" />
                <x-input id="cedula" class="block mt-1 w-full" type="text" name="cedula" value="{{ $dados['cedula'] }}" required readonly />
            </div>

            <div class="mt-4">
                <x-label for="empresa" value="{{ __('Designação') }}" />
                <select name="Designacao" id="Designacao" class="block mt-1 w-full">
                    <option value="Despachante Oficial">Despachante</option>
                    <option value="Praticante">Praticante</option>
                </select>
            </div>

            <div class="mt-4">
                <x-label for="empresa" value="{{ __('Empresa') }}" />
                <x-input id="empresa" class="block mt-1 w-full" type="text" name="empresa" value="{{ $dados['user']['name'] }}" required />
            </div>
            
            <div class="mt-4">
                <x-label for="nif" value="{{ __('NIF') }}" />
                <x-input id="nif" class="block mt-1 w-full" type="text" name="nif" value="{{ $dados['nif'] }}" required />
            </div>

            <div class="mt-4">
                <x-label for="endereco" value="{{ __('Endereço') }}" />
                <x-input id="endereco" class="block mt-1 w-full" type="text" name="endereco" value="{{ $dados['endereco'] }}" required />
            </div>

            <div class="mt-4">
                <x-label for="name" value="{{ __('Nome Completo (Representante)') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" value="{{ $dados['user']['email'] }}" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />

                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ms-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const strengthIndicator = document.getElementById('password-strength');
            const matchIndicator = document.getElementById('password-match');

            function checkPasswordStrength(password) {
                let strength = 'weak';
                const regexStrong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
                const regexMedium = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{6,}$/;

                if (regexStrong.test(password)) {
                    strength = 'strong';
                } else if (regexMedium.test(password)) {
                    strength = 'medium';
                }

                return strength;
            }

            function updateStrengthIndicator(strength) {
                strengthIndicator.textContent = '';
                strengthIndicator.classList.remove('strength-weak', 'strength-medium', 'strength-strong');

                if (strength === 'weak') {
                    strengthIndicator.textContent = 'Senha Fraca';
                    strengthIndicator.classList.add('strength-weak');
                } else if (strength === 'medium') {
                    strengthIndicator.textContent = 'Senha Média';
                    strengthIndicator.classList.add('strength-medium');
                } else if (strength === 'strong') {
                    strengthIndicator.textContent = 'Senha Forte';
                    strengthIndicator.classList.add('strength-strong');
                }
            }

            function checkPasswordMatch(password, confirmPassword) {
                return password === confirmPassword;
            }

            function updateMatchIndicator(isMatching) {
                matchIndicator.textContent = '';
                matchIndicator.classList.remove('match', 'no-match');

                if (isMatching) {
                    matchIndicator.textContent = 'As senhas correspondem';
                    matchIndicator.classList.add('match');
                } else {
                    matchIndicator.textContent = 'As senhas não correspondem';
                    matchIndicator.classList.add('no-match');
                }
            }

            passwordInput.addEventListener('input', function () {
                const strength = checkPasswordStrength(passwordInput.value);
                updateStrengthIndicator(strength);

                const isMatching = checkPasswordMatch(passwordInput.value, confirmPasswordInput.value);
                updateMatchIndicator(isMatching);
            });

            confirmPasswordInput.addEventListener('input', function () {
                const isMatching = checkPasswordMatch(passwordInput.value, confirmPasswordInput.value);
                updateMatchIndicator(isMatching);
            });
        });
    </script>
</x-guest-layout>
