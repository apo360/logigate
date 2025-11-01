<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <title>Logigate | Login</title>
        <!-- Canonical SEO -->
        <link rel="canonical" href="https://aduaneiro.hongayetu.com"/>

        <!-- Meta Tags -->
        <meta name="keywords" content="logigate, sistema de gestão aduaneira, gestão financeira, gestão contabilística, automação aduaneira, hongayetu lda, software aduaneiro, controle logístico, contabilidade aduaneira, despacho aduaneiro, gestão de operações, Angola, África">
        <meta name="description" content="Logigate: Solução completa para gestão aduaneira, financeira e contabilística. Automatize processos, reduza custos e aumente a eficiência dos seus despachos com a Hongayetu Lda.">

        <!-- Schema.org markup -->
        <meta itemprop="name" content="Logigate - Gestão Aduaneira, Financeira e Contabilística">
        <meta itemprop="description" content="Logigate oferece uma solução robusta e integrada para automação e controle de processos aduaneiros, financeiros e contabilísticos, garantindo eficiência e precisão.">
        <meta itemprop="image" content="https://aduaneiro.hongayetu.com/images/logigate-thumbnail.jpg">
        <meta itemprop="datePublished" content="2023-10-01">
        <meta itemprop="ratingValue" content="4.9">
        <meta itemprop="reviewCount" content="150">

        <!-- Twitter Card data -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@hongayetu">
        <meta name="twitter:title" content="Logigate - Sistema de Gestão Aduaneira, Financeira e Contabilística">
        <meta name="twitter:description" content="Aumente a eficiência dos seus processos com o Logigate, desenvolvido pela Hongayetu Lda. Automatize despachos e gestão financeira com uma plataforma avançada. #Logística #Angola">
        <meta name="twitter:creator" content="@hongayetu">
        <meta name="twitter:image" content="https://aduaneiro.hongayetu.com/images/logigate-thumbnail.jpg">
        <meta name="twitter:image:alt" content="Logigate - Sistema de Gestão Aduaneira">

        <!-- Open Graph data -->
        <meta property="og:title" content="Logigate | Sistema de Gestão Aduaneira e Financeira" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://aduaneiro.hongayetu.com" />
        <meta property="og:image" content="https://aduaneiro.hongayetu.com/images/logigate-thumbnail.jpg" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="628" />
        <meta property="og:description" content="Logigate é uma solução desenvolvida pela Hongayetu Lda para gestão aduaneira, financeira e contabilística, garantindo automação e eficiência nos processos de despacho e controle financeiro." />
        <meta property="og:site_name" content="Logigate" />
        <meta property="og:locale" content="pt_AO" />
        <meta property="og:updated_time" content="2023-10-01T00:00:00+01:00" />

        <!-- Tailwind CSS -->
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen bg-cover bg-center" style="background-image: url({{ asset('dist/img/logistic_bg_login.jpg') }})">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-4xl flex flex-col md:flex-row">
            <!-- Coluna Esquerda: Formulário de Login -->
            <div class="w-full md:w-1/2 p-8">
                <!-- Título e Descrição -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold">Logigate</h1>
                    <p class="text-gray-600">Faça login para acessar sua conta.</p>
                </div>

                <!-- Formulário de Login -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Seleção de Tipo de Usuário (Radio Buttons) -->
                    <div class="space-y-4">
                        <div class="border-2 border-blue-500 p-4 rounded-lg">
                            <div class="flex items-center">
                                <input id="cliente" type="radio" name="user_type" value="cliente" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="cliente" class="ml-3 block text-sm font-medium text-gray-700">Cliente</label>
                            </div>
                            <p class="text-sm text-gray-500 ml-7">Acesso para clientes que desejam acompanhar seus processos.</p>
                        </div>
                        <div class="border-2 border-blue-500 p-4 rounded-lg">
                            <div class="flex items-center">
                                <input id="despachante" type="radio" name="user_type" value="despachante" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" checked autofocus>
                                <label for="despachante" class="ml-3 block text-sm font-medium text-gray-700">Despachante</label>
                            </div>
                            <p class="text-sm text-gray-500 ml-7">Acesso para despachantes que gerenciam processos aduaneiros.</p>
                        </div>
                        <div class="border-2 border-blue-500 p-4 rounded-lg">
                            <div class="flex items-center">
                                <input id="admin" type="radio" name="user_type" value="admin" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="admin" class="ml-3 block text-sm font-medium text-gray-700">Administrador</label>
                            </div>
                            <p class="text-sm text-gray-500 ml-7">Acesso para administradores do sistema.</p>
                        </div>
                    </div>

                    <!-- Acesso para Despachantes -->
                    <div id="lg_despachante" class="space-y-4">
                        <!-- Campo de Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Campo de Senha -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Acesso para Clientes -->
                    <div id="lg_usuarioIAM" class="hidden space-y-4">
                        <div id="etapa1">
                            <label for="nif" class="block text-sm font-medium text-gray-700">NIF</label>
                            <input id="nif" type="text" name="nif" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <button type="button" id="confirmarTelefone" class="mt-4 w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Acessar
                            </button>
                        </div>
                    </div>

                    <!-- Acesso para Administradores (QR Code) -->
                    <div id="admin_qr" class="hidden space-y-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Introduza o PIN para acessar como administrador.</p>
                            <input type="text" id="pin" name="pin" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('pin') border-red-500 @enderror">
                            <button id="verify-pin" type="button" class="mt-4 w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Acessar
                            </button>
                        </div>
                        <!-- <div class="text-center">
                            <p class="text-sm text-gray-900 text-bold">Ou</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Escaneie o QR Code abaixo para acessar como administrador.</p>
                            Placeholder para o QR Code
                            <div id="qr_code_placeholder" class="mt-4 p-4 bg-gray-100 rounded-lg">
                                <img src="https://via.placeholder.com/150" alt="QR Code" class="mx-auto">
                            </div>
                        </div> -->
                    </div>

                    <!-- Lembrar-me e Esqueci a Senha -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="remember_me" class="ml-2 block text-sm text-gray-900">Lembrar-me</label>
                        </div>
                        <div>
                            <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-500">Esqueci a senha</a>
                        </div>
                    </div>

                    <!-- Botão de Login -->
                    <div id="btt_sub">
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Entrar
                        </button>
                    </div>
                </form>

                <!-- Mensagens de Erro -->
                <x-validation-errors class="mb-4" />

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif
            </div>

            <!-- Coluna Direita: Imagem ou Logotipo Animado -->
            <div class="w-full md:w-1/2 bg-black flex items-center justify-center p-8 relative overflow-hidden bg-cover bg-center" style="background-image: url('{{ asset('dist/img/geometric-shape-background_1189-277.avif') }}');">
                <!-- Formas Abstratas -->
                <div class="absolute inset-0 z-0">
                </div>

                <!-- Logotipo -->
                <img src="{{ asset('dist/img/logotipos/2.png') }}" alt="Logotipo Animado" class="w-48 h-48 relative z-10 mix-blend-multiply">
            </div>
        </div>

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                // Configuração global do token CSRF (para manter boas práticas)
                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                });

                // Alternar entre tipos de usuário
                $('input[name="user_type"]').change(function() {
                    const tipo = $(this).val();

                    // Esconde todos os blocos
                    $('#lg_despachante, #lg_usuarioIAM, #admin_qr, #btt_sub').addClass('hidden');
                    $('#email, #password, #nif, #pin').removeAttr('required');

                    if (tipo === 'despachante') {
                        $('#btt_sub').removeClass('hidden');
                        $('#lg_despachante').removeClass('hidden');
                        $('#email, #password').attr('required', true);
                    } 
                    else if (tipo === 'cliente') {
                        $('#lg_usuarioIAM').removeClass('hidden');
                        $('#nif').attr('required', true);
                        $('#etapa1').removeClass('hidden');
                        $('#etapa2, #telefoneContainer').addClass('hidden');
                    } 
                    else if (tipo === 'admin') {
                        $('#admin_qr').removeClass('hidden');
                    }
                });

                // Cliente: apenas NIF → redireciona
                $('#confirmarTelefone').click(function() {
                    const nif = $('#nif').val().trim();

                    if (!nif) {
                        alert('Por favor, insira o NIF.');
                        return;
                    }

                    // Simula login do cliente
                    $.ajax({
                        url: 'verify-nif',
                        method: 'POST',
                        data: { nif: nif },
                        success: function(response) {
                            if (response.success) {
                                window.location.href = response.redirect_url;
                            } else {
                                alert('NIF não encontrado. Tente novamente.');
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            alert(response.message || 'Erro ao processar acesso do cliente.');
                        }
                    });
                });

                // Admin: apenas PIN → redireciona
                $('#verify-pin').click(function() {
                    const pin = $('#pin').val().trim();

                    if (!pin) {
                        alert('Por favor, insira o PIN.');
                        return;
                    }

                    $.ajax({
                        url: 'verify-pin',
                        method: 'POST',
                        data: {
                            pin: pin,
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.href = response.redirect_url;
                            } 
                            else {
                                alert(response.message || 'PIN inválido. Tente novamente.');
                            }
                        },
                        error: function(xhr) { // Pegar o erro geral e inseri no alert
                            const response = xhr.responseJSON;
                            alert(response.message || 'Erro ao verificar o PIN. Tente novamente.');
                        }
                    });
                });
            });
        </script>
    </body>
</html>