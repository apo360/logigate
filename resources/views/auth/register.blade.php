<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Logigate | Cadastro</title>

	<!-- Canonical SEO -->
    <link rel="canonical" href="https://www.logigate.ao"/>

    <!-- Meta Tags -->
    <meta name="keywords" content="logigate, sistema de gestão aduaneira, gestão financeira, gestão contabilística, automação aduaneira, hongayetu lda, software aduaneiro, controle logístico, contabilidade aduaneira, despacho aduaneiro, gestão de operações, Angola, África">
    <meta name="description" content="Logigate: Solução completa para gestão aduaneira, financeira e contabilística. Automatize processos, reduza custos e aumente a eficiência dos seus despachos com a Hongayetu Lda.">

    <!-- Schema.org markup -->
    <meta itemprop="name" content="Logigate - Gestão Aduaneira, Financeira e Contabilística">
    <meta itemprop="description" content="Logigate oferece uma solução robusta e integrada para automação e controle de processos aduaneiros, financeiros e contabilísticos, garantindo eficiência e precisão.">
    <meta itemprop="image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg">
    <meta itemprop="datePublished" content="2023-10-01">
    <meta itemprop="ratingValue" content="4.9">
    <meta itemprop="reviewCount" content="150">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@hongayetu">
    <meta name="twitter:title" content="Logigate - Sistema de Gestão Aduaneira, Financeira e Contabilística">
    <meta name="twitter:description" content="Aumente a eficiência dos seus processos com o Logigate, desenvolvido pela Hongayetu Lda. Automatize despachos e gestão financeira com uma plataforma avançada. #Logística #Angola">
    <meta name="twitter:creator" content="@hongayetu">
    <meta name="twitter:image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg">
    <meta name="twitter:image:alt" content="Logigate - Sistema de Gestão Aduaneira">

    <!-- Open Graph data -->
    <meta property="og:title" content="Logigate | Sistema de Gestão Aduaneira e Financeira" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://www.logigate.ao" />
    <meta property="og:image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="628" />
    <meta property="og:description" content="Logigate é uma solução desenvolvida pela Hongayetu Lda para gestão aduaneira, financeira e contabilística, garantindo automação e eficiência nos processos de despacho e controle financeiro." />
    <meta property="og:site_name" content="Logigate" />
    <meta property="og:locale" content="pt_AO" />
    <meta property="og:updated_time" content="2023-10-01T00:00:00+01:00" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('dist/img/logistic_bg_login.jpg');">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-4xl">
            <div class="text-center mb-8">
                <img src="dist/img/LOGIGATE.png" alt="Logigate Logo" class="w-24 mx-auto">
                <h1 class="text-2xl font-bold mt-4">Logigate</h1>
                <p class="text-gray-600">Inicie aqui o seu registo.</p>
            </div>

            <form id="registerForm" method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf
                <div class="wizard">
                    <div class="wizard-header flex justify-between mb-4">
                        <div class="progress-bar bg-gray-200 h-2 rounded-full w-full">
                            <div class="progress bg-blue-500 h-2 rounded-full" style="width: 20%;"></div>
                        </div>
                    </div>

                    <div class="wizard-navigation flex justify-between mb-4">
                        <a href="#step_cedula" class="step active" data-step="1">
                            <div class="icon-circle bg-blue-500 text-white w-8 h-8 rounded-full flex items-center justify-center">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="text-sm mt-1">Cedula</span>
                        </a>
                        <a href="#about" class="step" data-step="2">
                            <div class="icon-circle bg-gray-300 text-gray-600 w-8 h-8 rounded-full flex items-center justify-center">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="text-sm mt-1">Despachante</span>
                        </a>
                        <a href="#manager" class="step" data-step="3">
                            <div class="icon-circle bg-gray-300 text-gray-600 w-8 h-8 rounded-full flex items-center justify-center">
                                <i class="fas fa-cog"></i>
                            </div>
                            <span class="text-sm mt-1">Representante</span>
                        </a>
                        <a href="#account" class="step" data-step="4">
                            <div class="icon-circle bg-gray-300 text-gray-600 w-8 h-8 rounded-full flex items-center justify-center">
                                <i class="fas fa-map"></i>
                            </div>
                            <span class="text-sm mt-1">Conta</span>
                        </a>
                        <a href="#termos" class="step" data-step="5">
                            <div class="icon-circle bg-gray-300 text-gray-600 w-8 h-8 rounded-full flex items-center justify-center">
                                <i class="fas fa-map"></i>
                            </div>
                            <span class="text-sm mt-1">Termos</span>
                        </a>
                    </div>

                    <input type="hidden" name="plano_id" value="1">
                    <input type="hidden" name="modalidade_pagamento" value="Mensal">

                    <div class="wizard-content">
                        <div id="step_cedula" class="step-content active">
                            <h5 class="text-lg font-semibold mb-4">Por favor, Valide o seu Nº de Cedula.</h5>
                            <div class="mb-4">
								<div>
									<label for="cedula" class="block text-sm font-medium text-gray-700">Nº Cedula <small>(required)</small></label>
									<input type="text" name="cedula" id="cedula" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Introduza o numero da cedula do despachante">
									<p id="cedula-error" class="text-sm text-red-500 hidden mt-2"></p>
                                    <div id="cedula-match-icon" class="mt-2 flex items-center hidden">
                                        <i class="fas fa-check text-green-500 hidden"></i>
                                        <i class="fas fa-times text-red-500 hidden"></i>
                                    </div>
                                    <p id="cedula-match-error" class="text-sm mt-2"></p>
								</div>
							</div>
                            <h5 class="text-lg font-semibold mb-4">Designação (Escolha apenas um)</h5>
                            <div class="grid grid-cols-3 gap-4">
								<label class="flex items-center space-x-2">
									<input type="radio" name="Designacao" value="Agente de Carga" class="form-radio">
									<span>Agente de Carga</span>
								</label>
								<label class="flex items-center space-x-2">
									<input type="radio" name="Designacao" value="Despachante Oficial" class="form-radio">
									<span>Despachante</span>
								</label>
								<label class="flex items-center space-x-2">
									<input type="radio" name="Designacao" value="Transitário" class="form-radio">
									<span>Transitário</span>
								</label>
							</div>
							<p id="designacao-error" class="text-sm text-red-500 hidden mt-2">Por favor, selecione uma opção.</p>
                        </div>

                        <div id="about" class="step-content hidden">
                            <h5 class="text-lg font-semibold mb-4">Dados do despachante.</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="picture-container center" style="cursor: pointer;" id="wizardPictureContainer">
                                        <div class="picture w-24 h-24 rounded-full overflow-hidden border border-gray-300" tabindex="0" aria-label="Escolher logotipo">
                                            <img src="ly_login/img/default-avatar.jpg" class="w-full h-full object-cover" id="wizardPicturePreview" alt="Prévia do logotipo">
                                            <input type="file" id="wizard-picture" name="logotipo" class="hidden" accept="image/*" aria-label="Selecionar logotipo">
                                        </div>
                                        <h6 class="text-sm text-center mt-2">Escolher logotipo</h6>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-4">
                                        <label for="empresa" class="block text-sm font-medium text-gray-700">Empresa <small>(required)</small></label>
                                        <input type="text" name="empresa" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Nome da Empresa">
                                    </div>
                                    <div class="mb-4">
                                        <label for="nif" class="block text-sm font-medium text-gray-700">NIF <small>(required)</small></label>
                                        <input type="text" name="nif" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="NIF da Empresa">
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
								<div>
									<label for="provincia" class="block text-sm font-medium text-gray-700">Província</label>
									<select name="provincia" id="provincia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
										<option value="">Selecionar</option>
										@foreach(\App\Models\Provincia::all() as $prov)
											<option value="{{$prov->id}}">{{$prov->Nome}}</option>
										@endforeach
									</select>
									<div id="loading-cidades" class="hidden mt-2">
										<i class="fas fa-spinner fa-spin"></i> Carregando cidades...
									</div>
								</div>
                                <div>
                                    <label for="cidade" class="block text-sm font-medium text-gray-700">Cidade</label>
                                    <select name="cidade" id="cidade" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Selecionar</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="endereco" class="block text-sm font-medium text-gray-700">Endereço <small>(required)</small></label>
                                <input type="text" name="endereco" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Endereço Completo">
                            </div>
                        </div>

						<div id="manager" class="step-content hidden">
    						<h5 class="text-lg font-semibold mb-4">Informação do Representante</h5>
							<div class="grid grid-cols-1 gap-4">
								<div>
									<label for="name" class="block text-sm font-medium text-gray-700">Primeiro Nome <small>(required)</small></label>
									<input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Estefania...">
								</div>
								<div>
									<label for="apelido" class="block text-sm font-medium text-gray-700">Apelido <small>(required)</small></label>
									<input type="text" name="apelido" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Costa...">
								</div>
								<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
									<div>
										<label for="telefone" class="block text-sm font-medium text-gray-700">Telefone</label>
										<input type="text" name="telefone" id="telefone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="+244 900 000 000">
									</div>
									<div>
										<label for="tipo_representante" class="block text-sm font-medium text-gray-700">Tipo de Representante</label>
										<select name="tipo_representante" id="tipo_representante" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
											<option value="">Selecionar</option>
											<option value="Contabilista">Contabilista</option>
											<option value="Auditor">Auditor</option>
											<option value="Juridico">Juridico</option>
											<option value="Próprio">O Próprio</option>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div id="account" class="step-content hidden">
							<h5 class="text-lg font-semibold mb-4">Falta Mais um Passo</h5>
							<div class="grid grid-cols-1 gap-4">
								<div>
									<label for="email" class="block text-sm font-medium text-gray-700">Email <small>(required)</small></label>
									<input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="estefania.costa@hongayetu.com">
									<p id="email-error" class="text-sm text-red-500 hidden">Por favor, insira um e-mail válido.</p>
								</div>
								<div>
									<label for="password" class="block text-sm font-medium text-gray-700">Password</label>
									<input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="*******">
									<div id="password-strength" class="text-sm mt-1"></div>
								</div>
								<div class="relative">
									<label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Password</label>
									<input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm pr-10" placeholder="*******">
									<div id="password-match-icon" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-7">
										<i class="fas fa-times text-red-500 hidden"></i>
										<i class="fas fa-check text-green-500 hidden"></i>
									</div>
									<p id="password-match-error" class="text-sm text-red-500 hidden">As senhas não coincidem.</p>
								</div>
							</div>
						</div>

						<div id="termos" class="step-content hidden">
							<h5 class="text-lg font-semibold mb-4">Termos de Serviços e Políticas de Privacidade</h5>
							<div class="grid grid-cols-1 gap-4">
								@if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
									<div class="mt-4">
										<label for="terms" class="flex items-center">
											<input type="checkbox" name="terms" id="terms" class="form-checkbox h-4 w-4 text-blue-600" required>
											<span class="ml-2 text-sm text-gray-700">
												{!! __('I agree to the :terms_of_service and :privacy_policy', [
													'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-blue-600 hover:text-blue-800">'.__('Termos de Serviço').'</a>',
													'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-blue-600 hover:text-blue-800">'.__('Políticas de Privacidade').'</a>',
												]) !!}
											</span>
										</label>
                                        <p id="terms-error" class="error-feedback text-sm text-red-500 hidden mt-2" aria-live="polite">Você deve aceitar os termos para continuar.</p>11011
									</div>
								@endif
							</div>
						</div>

                    </div>

                    <div class="wizard-footer flex justify-between mt-8">
                        <div>
                            <button type="button" class="btn-previous bg-gray-300 text-gray-700 px-4 py-2 rounded-md">Anterior</button>
                            <a href="/" class="bg-blue-500 text-white px-4 py-2 rounded-md">Página Inicial</a>
                        </div>
                        <div>
                            <button type="button" class="btn-next bg-blue-500 text-white px-4 py-2 rounded-md">Próximo</button>
                            <button type="submit" class="btn-finish bg-green-500 text-white px-4 py-2 rounded-md">Registrar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var currentStep = 1;
            var totalSteps = $('.step-content').length;

            function updateProgress() {
                var progress = (currentStep / totalSteps) * 100;
                $('.progress').css('width', progress + '%');
            }

            function showStep(step) {
                $('.step-content').addClass('hidden');
                $('.step-content').eq(step - 1).removeClass('hidden');
                $('.step').removeClass('active');
                $('.step').eq(step - 1).addClass('active');
            }

            function validateStep(step) {
                let valid = true;

                // Limpa feedbacks anteriores
                $('.error-feedback').addClass('hidden');

                if (step === 1) {
                    // Verificar se "Despachante oficial" está selecionado
                    const isDespachanteOficial = $('input[name="Designacao"]:checked').val() === 'Despachante Oficial';
                    if (isDespachanteOficial) {
                        // Cedula
                        const cedula = $('#cedula').val().trim();
                        if (!cedula) {
                            $('#cedula-error').text('Por favor, preencha o número da cédula.').removeClass('hidden').attr('aria-live', 'polite');
                            $('#cedula').addClass('border-red-500');
                            valid = false;
                        } else {
                            $('#cedula-error').addClass('hidden').text('');
                            $('#cedula').removeClass('border-red-500');
                        }
                    }
                    // Designacao
                    const isDesignacaoSelected = $('input[name="Designacao"]:checked').length > 0;
                    if (!isDesignacaoSelected) {
                        $('#designacao-error').removeClass('hidden').attr('aria-live', 'polite');
                        $('input[name="Designacao"]').parent().addClass('border border-red-500 p-2 rounded-md');
                        valid = false;
                    } else {
                        $('#designacao-error').addClass('hidden');
                        $('input[name="Designacao"]').parent().removeClass('border border-red-500 p-2 rounded-md');
                    }
                }

                if (step === 2) {
                    // Empresa
                    const empresa = $('input[name="empresa"]').val().trim();
                    if (!empresa) {
                        if ($('#empresa-error').length === 0) {
                            $('input[name="empresa"]').after('<p id="empresa-error" class="error-feedback text-sm text-red-500 mt-2" aria-live="polite">Por favor, preencha o nome da empresa.</p>');
                        }
                        $('#empresa-error').removeClass('hidden');
                        $('input[name="empresa"]').addClass('border-red-500');
                        valid = false;
                    } else {
                        $('#empresa-error').addClass('hidden');
                        $('input[name="empresa"]').removeClass('border-red-500');
                    }
                    // NIF
                    const nif = $('input[name="nif"]').val().trim();
                    if (!nif) {
                        if ($('#nif-error').length === 0) {
                            $('input[name="nif"]').after('<p id="nif-error" class="error-feedback text-sm text-red-500 mt-2" aria-live="polite">Por favor, preencha o NIF.</p>');
                        }
                        $('#nif-error').removeClass('hidden');
                        $('input[name="nif"]').addClass('border-red-500');
                        valid = false;
                    } else {
                        $('#nif-error').addClass('hidden');
                        $('input[name="nif"]').removeClass('border-red-500');
                    }
                    // Endereço
                    const endereco = $('input[name="endereco"]').val().trim();
                    if (!endereco) {
                        if ($('#endereco-error').length === 0) {
                            $('input[name="endereco"]').after('<p id="endereco-error" class="error-feedback text-sm text-red-500 mt-2" aria-live="polite">Por favor, preencha o endereço.</p>');
                        }
                        $('#endereco-error').removeClass('hidden');
                        $('input[name="endereco"]').addClass('border-red-500');
                        valid = false;
                    } else {
                        $('#endereco-error').addClass('hidden');
                        $('input[name="endereco"]').removeClass('border-red-500');
                    }
                }

                if (step === 3) {
                    // Nome
                    const nome = $('input[name="name"]').val().trim();
                    if (!nome) {
                        if ($('#name-error').length === 0) {
                            $('input[name="name"]').after('<p id="name-error" class="error-feedback text-sm text-red-500 mt-2" aria-live="polite">Por favor, preencha o primeiro nome.</p>');
                        }
                        $('#name-error').removeClass('hidden');
                        $('input[name="name"]').addClass('border-red-500');
                        valid = false;
                    } else {
                        $('#name-error').addClass('hidden');
                        $('input[name="name"]').removeClass('border-red-500');
                    }
                    // Apelido
                    const apelido = $('input[name="apelido"]').val().trim();
                    if (!apelido) {
                        if ($('#apelido-error').length === 0) {
                            $('input[name="apelido"]').after('<p id="apelido-error" class="error-feedback text-sm text-red-500 mt-2" aria-live="polite">Por favor, preencha o apelido.</p>');
                        }
                        $('#apelido-error').removeClass('hidden');
                        $('input[name="apelido"]').addClass('border-red-500');
                        valid = false;
                    } else {
                        $('#apelido-error').addClass('hidden');
                        $('input[name="apelido"]').removeClass('border-red-500');
                    }
                }

                if (step === 4) {
                    // Email
                    const email = $('#email').val().trim();
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!email || !emailPattern.test(email)) {
                        $('#email-error').removeClass('hidden').attr('aria-live', 'polite');
                        $('#email').addClass('border-red-500');
                        valid = false;
                    } else {
                        $('#email-error').addClass('hidden');
                        $('#email').removeClass('border-red-500');
                    }
                    // Password
                    const password = $('#password').val();
                    if (!password || password.length < 8) {
                        if ($('#password-error').length === 0) {
                            $('#password').after('<p id="password-error" class="error-feedback text-sm text-red-500 mt-2" aria-live="polite">A senha deve ter pelo menos 8 caracteres.</p>');
                        }
                        $('#password-error').removeClass('hidden');
                        $('#password').addClass('border-red-500');
                        valid = false;
                    } else {
                        $('#password-error').addClass('hidden');
                        $('#password').removeClass('border-red-500');
                    }
                    // Confirmação de senha
                    const confirmPassword = $('#password_confirmation').val();
                    if (password !== confirmPassword) {
                        $('#password-match-error').removeClass('hidden').attr('aria-live', 'polite');
                        $('#password_confirmation').addClass('border-red-500');
                        valid = false;
                    } else {
                        $('#password-match-error').addClass('hidden');
                        $('#password_confirmation').removeClass('border-red-500');
                    }
                }

                if (step === 5) {
                    // Termos
                    if ($('#terms').length && !$('#terms').is(':checked')) {
                        if ($('#terms-error').length === 0) {
                            $('#terms').parent().after('<p id="terms-error" class="error-feedback text-sm text-red-500 mt-2" aria-live="polite">Você deve aceitar os termos para continuar.</p>');
                        }
                        $('#terms-error').removeClass('hidden');
                        valid = false;
                    } else {
                        $('#terms-error').addClass('hidden');
                    }
                }

                return valid;
            }

            $('.btn-next').click(function() {
                if (validateStep(currentStep)) {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        showStep(currentStep);
                        updateProgress();
                    }
                }
                return false;
            });

            $('.btn-previous').click(function() {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                    updateProgress();
                }
            });

            // Pré-carregar cidades em um objeto JavaScript
            var cidades = @json(\App\Models\Municipio::all());

            $('#provincia').change(function() {
                var provinciaId = $(this).val();
                $('#cidade').empty();
                $('#cidade').append('<option value="">Selecionar</option>');

                if (provinciaId) {
                    // Filtrar as cidades com base na província selecionada
                    var cidadesFiltradas = cidades.filter(function(cidade) {
                        return cidade.provincia_id == provinciaId;
                    });

                    // Preencher o select de cidades
                    $.each(cidadesFiltradas, function(key, value) {
                        $('#cidade').append('<option value="' + value.id + '">' + value.Nome + '</option>');
                    });
                }
            });

			$('#provincia').change(function() {
				$('#loading-cidades').removeClass('hidden');
				setTimeout(() => {
					// Simula um atraso de carregamento
					$('#loading-cidades').addClass('hidden');
					// Lógica para carregar cidades
				}, 1000);
			});

            // Preview da imagem do logotipo
            $('#wizard-picture').change(function() {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#wizardPicturePreview').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            });

            // Força a seleção do input file ao clicar na imagem
            $('.picture').on('click', function(e) {
                // Evita disparar o evento se o clique for no input file
                if (e.target.id !== 'wizard-picture') {
                    $('#wizard-picture').click();
                }
            });

            // Validação de força da senha
            $('#password').on('input', function() {
                var password = $(this).val();
                var strengthText = '';
                var strengthColor = '';

                if (password.length < 6) {
                    strengthText = 'Fraca';
                    strengthColor = 'text-red-500';
                } else if (password.length < 10) {
                    strengthText = 'Média';
                    strengthColor = 'text-yellow-500';
                } else {
                    strengthText = 'Forte';
                    strengthColor = 'text-green-500';
                }

                $('#password-strength').text('Força da senha: ' + strengthText).removeClass('text-red-500 text-yellow-500 text-green-500').addClass(strengthColor);
            });

            // Verificar cedula via AJAX dinâmicamente
            $('#cedula').on('input', function() {
                var cedula = $(this).val();

                if (cedula.length === 0) {
                    $('#cedula-match-icon i').addClass('hidden');
                    $('#cedula-match-error').addClass('hidden');
                } else {
                    $.ajax({
                        url: "{{ route('cedula.verificar') }}",
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            cedula: cedula
                        },
                        success: function(response) {
                            $('#cedula-match-icon').removeClass('hidden');
                            $('#cedula-match-icon i.fa-times').addClass('hidden');
                            $('#cedula-match-icon i.fa-check').removeClass('hidden');
                            $('#cedula-match-error')
                                .removeClass('text-red-500')
                                .addClass('text-green-500')
                                .text(response.message)
                                .attr('aria-live', 'polite')
                                .removeClass('hidden');
                        },
                        error: function(xhr) {
                            $('#cedula-match-icon').removeClass('hidden');
                            $('#cedula-match-icon i.fa-check').addClass('hidden');
                            $('#cedula-match-icon i.fa-times').removeClass('hidden');
                            let msg = 'O Nº da Cédula já existe.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            $('#cedula-match-error')
                                .removeClass('text-green-500')
                                .addClass('text-red-500')
                                .text(msg)
                                .attr('aria-live', 'polite')
                                .removeClass('hidden');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>