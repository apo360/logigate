<x-app-layout>
<div class="container mt-5">
    <h1 class="text-center mb-4">Adicionar Usuário</h1>

    <!-- Exibir mensagens de sucesso ou erro -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Formulário para adicionar novo usuário -->
    <form action="{{ route('usuarios.store') }}" method="POST" class="bg-light p-4 rounded shadow">
        @csrf
        
        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required placeholder="Digite o nome completo">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required placeholder="Digite o email">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Digite a senha" oninput="validatePassword()">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div id="passwordFeedback" class="invalid-feedback" style="display:none;"></div>
            <div class="progress mt-2">
                <div id="passwordStrength" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div id="strengthLabel" class="mt-2"></div>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Senha</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Confirme a senha" oninput="validatePassword()">
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Papel</label>
            <select name="role" class="form-select" required>
                <option value="" disabled selected>Selecione um papel</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success w-100" id="submitBtn" disabled>Salvar</button>
        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary w-100 mt-2">Voltar</a>
    </form>
</div>

<style>
    .container {
        max-width: 600px; /* Limitar a largura do formulário */
        margin: auto; /* Centralizar o formulário */
    }

    .bg-light {
        background-color: #f8f9fa; /* Fundo claro */
    }

    .form-label {
        font-weight: bold; /* Destacar os rótulos */
    }

    .btn {
        transition: background-color 0.3s, transform 0.3s; /* Transição suave para interações */
    }

    .btn:hover {
        background-color: #0056b3; /* Cor ao passar o mouse */
        transform: scale(1.05); /* Efeito de aumento */
    }

    .progress {
        height: 20px; /* Altura da barra de progresso */
    }
</style>

<script>
    function validatePassword() {
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;
        const feedback = document.getElementById('passwordFeedback');
        const passwordStrength = document.getElementById('passwordStrength');
        const strengthLabel = document.getElementById('strengthLabel');
        const submitBtn = document.getElementById('submitBtn');

        // Calcular a força da senha
        let strength = 0;
        if (password.length >= 8) {
            strength += 1; // Comprimento
        }
        if (/[A-Z]/.test(password)) {
            strength += 1; // Letra maiúscula
        }
        if (/[a-z]/.test(password)) {
            strength += 1; // Letra minúscula
        }
        if (/[0-9]/.test(password)) {
            strength += 1; // Dígito
        }
        if (/[^A-Za-z0-9]/.test(password)) {
            strength += 1; // Caractere especial
        }

        // Atualizar a barra de progresso
        const strengthPercentage = (strength / 5) * 100;
        passwordStrength.style.width = strengthPercentage + '%';

        // Atualizar o rótulo da força
        if (strength === 0) {
            strengthLabel.textContent = '';
        } else if (strength <= 2) {
            strengthLabel.textContent = 'Fraca';
            passwordStrength.className = 'progress-bar bg-danger'; // Vermelha
        } else if (strength === 3) {
            strengthLabel.textContent = 'Média';
            passwordStrength.className = 'progress-bar bg-warning'; // Amarela
        } else {
            strengthLabel.textContent = 'Forte';
            passwordStrength.className = 'progress-bar bg-success'; // Verde
        }

        // Check if passwords match
        if (password !== passwordConfirmation) {
            feedback.textContent = "As senhas não coincidem.";
            feedback.style.display = 'block';
            feedback.classList.add('is-invalid');
            submitBtn.disabled = true;
        } else {
            feedback.style.display = 'none';
            feedback.classList.remove('is-invalid');
            submitBtn.disabled = false; // Enable the submit button
        }
    }
</script>

</x-app-layout>
