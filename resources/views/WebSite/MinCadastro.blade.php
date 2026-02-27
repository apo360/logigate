<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Cadastro - LogiGate</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #0047AB;
            --primary-dark: #003580;
            --secondary: #00B4D8;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 71, 171, 0.3);
        }
        
        .social-btn {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .plan-card {
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
        }
        
        .plan-card:hover {
            transform: translateX(5px);
        }
        
        .feature-item {
            position: relative;
            padding-left: 24px;
        }
        
        .feature-item:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--primary);
            font-weight: bold;
        }
        
        .loading {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .success-check {
            animation: checkmark 0.5s ease;
        }
        
        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="max-w-6xl w-full">
        <!-- Header com Logo -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-300 to-blue-100 rounded-xl flex items-center justify-center">
                    <img 
              src="{{ asset('dist/img/LOGIGATE.png') }}" alt="LogiGate" style="opacity: .8; max-width: 70px;" 
              class="hidden md:block group-hover:animate-spin transition-all duration-300"
            >
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Logi<span class="text-blue-600">Gate</span></h1>
                    <p class="text-sm text-gray-500">Sistema Aduaneiro Inteligente</p>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="max-w-md mx-auto mb-6">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">1</div>
                        <span class="ml-2 text-sm font-medium text-blue-600">Plano Escolhido</span>
                    </div>
                    <div class="h-1 w-8 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs">2</div>
                        <span class="ml-2 text-sm font-medium text-blue-600">Cadastro</span>
                    </div>
                    <div class="h-1 w-8 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center text-xs">3</div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Pagamento</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Coluna Esquerda - Resumo do Plano -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-6">
                    <div class="text-center mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Resumo do Plano</h2>
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-medium">
                            <i class="fas fa-crown mr-2"></i>
                            Plano Selecionado
                        </div>
                    </div>
                    
                    <!-- Plano Escolhido -->
                    <div class="plan-card bg-gradient-to-r from-blue-50 to-white p-5 rounded-xl mb-6">
                        @php use Illuminate\Support\Str; @endphp
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{$planoSelecionado->nome}}</h3>
                                <p class="text-gray-600 text-sm">{{ Str::limit($planoSelecionado->descricao, 50) }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-blue-600">{{ number_format($price, 2, ',', '.') }}</div>
                                <div class="text-gray-500 text-sm">AOA/mês</div>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            @foreach($planoSelecionado->itemplano as $items)
                                <div class="feature-item text-sm text-gray-700">{{$items->item}}</div>
                            @endforeach
                        </div>
                        
                        <div class="text-center">
                            <button onclick="changePlan()" class="text-blue-600 text-sm font-medium hover:text-blue-800 transition-colors">
                                <i class="fas fa-exchange-alt mr-1"></i> Alterar Plano
                            </button>
                        </div>
                    </div>
                    
                    <!-- Teste Grátis -->
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-gift text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-green-900 mb-1">Teste Grátis de 14 Dias</h4>
                                <p class="text-green-700 text-sm">
                                    Comece agora e teste todas as funcionalidades sem compromisso.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Garantias -->
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-shield-alt text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">Segurança Garantida</h4>
                                <p class="text-gray-600 text-sm">Seus dados estão protegidos</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-undo-alt text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">Cancelamento Imediato</h4>
                                <p class="text-gray-600 text-sm">Cancele a qualquer momento</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-headset text-purple-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">Suporte 24/7</h4>
                                <p class="text-gray-600 text-sm">Equipe especializada disponível</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita - Formulário de Cadastro -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">Crie sua conta em 30 segundos</h2>
                        <p class="text-gray-600">
                            Complete seu cadastro e comece a usar o LogiGate agora mesmo.
                        </p>
                    </div>
                    
                    <!-- Botões de Login Social -->
                    <div class="mb-8">
                        <div class="text-center mb-4">
                            <span class="text-gray-500 text-sm">Cadastre-se rapidamente com</span>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                            <button onclick="socialLogin('google')" class="social-btn bg-white py-3 px-4 rounded-xl flex items-center justify-center">
                                <i class="fab fa-google text-red-500 mr-3"></i>
                                <span>Google</span>
                            </button>
                            
                            <button onclick="socialLogin('facebook')" class="social-btn bg-white py-3 px-4 rounded-xl flex items-center justify-center">
                                <i class="fab fa-facebook text-blue-600 mr-3"></i>
                                <span>Facebook</span>
                            </button>
                            
                            <button onclick="socialLogin('linkedin')" class="social-btn bg-white py-3 px-4 rounded-xl flex items-center justify-center">
                                <i class="fab fa-linkedin text-blue-700 mr-3"></i>
                                <span>LinkedIn</span>
                            </button>
                        </div>
                        
                        <div class="flex items-center my-6">
                            <div class="flex-1 h-px bg-gray-300"></div>
                            <div class="px-4">
                                <span class="text-gray-500 text-sm">ou com email</span>
                            </div>
                            <div class="flex-1 h-px bg-gray-300"></div>
                        </div>
                    </div>
                    
                    <!-- Formulário de Cadastro -->
                    <form  class="space-y-6" method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                        @csrf
                        <!-- Plano Hidden -->
                         <input type="hidden" id="plano_id" name="plano_id" value="{{$planoSelecionado->id}}">
                        <!-- Nome -->
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">
                                Nome <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input 
                                    type="text" 
                                    id="name"
                                    name="name"
                                    required
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                    placeholder="Insira o seu Primeiro e Último Nome"
                                >
                            </div>
                            <div id="name" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <!-- Email -->
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input 
                                    type="email" 
                                    id="email"
                                    name="email"
                                    required
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                    placeholder="seu@email.com"
                                >
                            </div>
                            <div id="emailError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <!-- Senha -->
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">
                                Senha <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input 
                                    type="password" 
                                    id="password"
                                    type="password"
                                    required
                                    minlength="6"
                                    class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                    placeholder="Mínimo 6 caracteres"
                                >
                                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i id="passwordToggle" class="fas fa-eye text-gray-400"></i>
                                </button>
                            </div>
                            <div id="passwordError" class="text-red-500 text-sm mt-1 hidden"></div>
                            <div class="mt-2">
                                <div class="text-sm text-gray-600">
                                    <span id="passwordStrength" class="font-medium">Força da senha: </span>
                                    <span id="strengthText" class="text-gray-500">Digite sua senha</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div id="strengthBar" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Confirmação de Senha -->
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">
                                Confirmar Senha <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input 
                                    type="password" 
                                    id="confirmPassword"
                                    required
                                    class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                    placeholder="Digite novamente"
                                >
                                <button type="button" onclick="toggleConfirmPassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i id="confirmPasswordToggle" class="fas fa-eye text-gray-400"></i>
                                </button>
                            </div>
                            <div id="confirmPasswordError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        
                        <!-- Aceitar Termos -->
                        <div class="flex items-start">
                            <input 
                                type="checkbox" 
                                id="terms"
                                name="terms"
                                required
                                class="mt-1 mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <label for="terms" class="text-sm text-gray-700">
                                Concordo com os 
                                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Termos de Serviço</a> 
                                e 
                                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Política de Privacidade</a>
                                <span class="text-red-500">*</span>
                            </label>
                        </div>
                        
                        <!-- Newsletter -->
                        <div class="flex items-start">
                            <input 
                                type="checkbox" 
                                id="newsletter"
                                name="newsletter"
                                class="mt-1 mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <label for="newsletter" class="text-sm text-gray-700">
                                Desejo receber novidades sobre atualizações do sistema e dicas de gestão aduaneira.
                            </label>
                        </div>
                        
                        <!-- Botões de Ação -->
                        <div class="pt-4">
                            <button type="submit" id="submitBtn" class="btn-primary w-full py-4 rounded-xl font-bold text-lg flex items-center justify-center">
                                <span id="submitText">Criar Conta e Continuar</span>
                                <i id="submitSpinner" class="fas fa-spinner loading ml-2 hidden"></i>
                            </button>
                            
                            <div class="text-center mt-4">
                                <p class="text-gray-600 text-sm">
                                    Já tem uma conta? 
                                    <a href="{{route('login')}}" class="text-blue-600 font-medium hover:text-blue-800">Faça login</a>
                                </p>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Informação adicional -->
                    <div class="mt-8 p-4 bg-blue-50 rounded-xl">
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-blue-900 mb-1">Próximos passos</h4>
                                <p class="text-blue-700 text-sm">
                                    Após criar sua conta, você será redirecionado para a página de pagamento 
                                    para ativar seu plano.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-gray-500 text-sm">
                © 2024 LogiGate by Hongayetu LDA. 
                <a href="#" class="text-gray-600 hover:text-gray-800">Política de Privacidade</a> • 
                <a href="#" class="text-gray-600 hover:text-gray-800">Termos de Serviço</a>
            </p>
        </div>
    </div>

    <script>

        // Alternar visibilidade da senha
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash text-gray-400';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye text-gray-400';
            }
        }

        // Alternar visibilidade da confirmação de senha
        function toggleConfirmPassword() {
            const confirmInput = document.getElementById('confirmPassword');
            const toggleIcon = document.getElementById('confirmPasswordToggle');
            
            if (confirmInput.type === 'password') {
                confirmInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash text-gray-400';
            } else {
                confirmInput.type = 'password';
                toggleIcon.className = 'fas fa-eye text-gray-400';
            }
        }

        // Validar força da senha
        function checkPasswordStrength(password) {
            let strength = 0;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            if (password.length >= 6) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            if (/[^A-Za-z0-9]/.test(password)) strength += 25;
            
            // Atualizar barra de força
            strengthBar.style.width = strength + '%';
            
            // Atualizar texto e cor
            if (strength < 50) {
                strengthBar.style.backgroundColor = '#ef4444';
                strengthText.textContent = 'Fraca';
                strengthText.className = 'text-red-500';
            } else if (strength < 75) {
                strengthBar.style.backgroundColor = '#f59e0b';
                strengthText.textContent = 'Média';
                strengthText.className = 'text-yellow-500';
            } else {
                strengthBar.style.backgroundColor = '#10b981';
                strengthText.textContent = 'Forte';
                strengthText.className = 'text-green-500';
            }
        }

        // Login Social
        function socialLogin(provider) {
            const providers = {
                'google': 'Google',
                'facebook': 'Facebook',
                'linkedin': 'LinkedIn'
            };
            
            // Mostrar loading
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            
            submitText.textContent = `Conectando com ${providers[provider]}...`;
            submitSpinner.classList.remove('hidden');
            submitBtn.disabled = true;
            
            // Simular conexão social
            setTimeout(() => {
                // Em produção, aqui você faria a autenticação real
                showSuccessModal();
            }, 1500);
        }

        // Alterar Plano
        function changePlan() {
            if (confirm('Deseja voltar para a página de planos?')) {
                window.location.href = 'plans.html'; // Redirecionar para página de planos
            }
        }

        // Mostrar Modal de Sucesso
        function showSuccessModal() {
            const modal = document.getElementById('successModal');
            const modalContent = modal.querySelector('div > div');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Animar entrada
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
            
            // Redirecionar após 2 segundos
            setTimeout(() => {
                window.location.href = 'payment.html?plan=' + planId;
            }, 2000);
        }

        // Validar formulário
        function validateForm() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const terms = document.getElementById('terms').checked;
            
            let isValid = true;
            
            // Resetar erros
            document.querySelectorAll('[id$="Error"]').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('emailError').textContent = 'Por favor, insira um email válido';
                document.getElementById('emailError').classList.remove('hidden');
                isValid = false;
            }
            
            // Validar senha
            if (password.length < 6) {
                document.getElementById('passwordError').textContent = 'A senha deve ter pelo menos 6 caracteres';
                document.getElementById('passwordError').classList.remove('hidden');
                isValid = false;
            }
            
            // Validar confirmação de senha
            if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'As senhas não coincidem';
                document.getElementById('confirmPasswordError').classList.remove('hidden');
                isValid = false;
            }
            
            // Validar termos
            if (!terms) {
                alert('Por favor, aceite os Termos de Serviço para continuar');
                isValid = false;
            }
            
            return isValid;
        }

        // Event Listeners para validação em tempo real
        document.getElementById('password').addEventListener('input', function(e) {
            checkPasswordStrength(e.target.value);
            
            // Validar confirmação de senha em tempo real
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (confirmPassword && e.target.value !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'As senhas não coincidem';
                document.getElementById('confirmPasswordError').classList.remove('hidden');
            } else {
                document.getElementById('confirmPasswordError').classList.add('hidden');
            }
        });

        document.getElementById('confirmPassword').addEventListener('input', function(e) {
            const password = document.getElementById('password').value;
            if (password && e.target.value !== password) {
                document.getElementById('confirmPasswordError').textContent = 'As senhas não coincidem';
                document.getElementById('confirmPasswordError').classList.remove('hidden');
            } else {
                document.getElementById('confirmPasswordError').classList.add('hidden');
            }
        });

        // Carregar plano ao iniciar
        document.addEventListener('DOMContentLoaded', function() {
            
            // Focar no primeiro campo
            document.getElementById('email').focus();
            
            // Animar entrada
            const form = document.querySelector('.lg\\:col-span-2 > div');
            form.classList.add('opacity-0', 'translate-y-4');
            
            setTimeout(() => {
                form.classList.remove('opacity-0', 'translate-y-4');
                form.classList.add('transition-all', 'duration-500');
            }, 100);
        });

    </script>
</body>
</html>