
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png" />
        <link rel="icon" type="image/png" href="assets/img/favicon.png" />
        <title>Logigate| Cadastro</title>

        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
        <meta name="viewport" content="width=device-width" />

        <!-- Canonical SEO -->
        <link rel="canonical" href="https://www.logigate.ao"/>

        <!-- Meta Tags -->
        <meta name="keywords" content="logigate, sistema de gestão aduaneira, gestão financeira, gestão contabilística, automação aduaneira, hongayetu lda, software aduaneiro, controle logístico, contabilidade aduaneira">
        <meta name="description" content="Logigate é um sistema completo de gestão aduaneira, financeira e contabilística, desenvolvido pela Hongayetu Lda para otimizar processos de despacho e gestão de operações.">

        <!-- Schema.org markup -->
        <meta itemprop="name" content="Logigate - Gestão Aduaneira, Financeira e Contabilística">
        <meta itemprop="description" content="Logigate oferece uma solução robusta e integrada para automação e controle de processos aduaneiros, financeiros e contabilísticos, garantindo eficiência e precisão.">
        <meta itemprop="image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg">

        <!-- Twitter Card data -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@hongayetu">
        <meta name="twitter:title" content="Logigate - Sistema de Gestão Aduaneira, Financeira e Contabilística">
        <meta name="twitter:description" content="Aumente a eficiência dos seus processos com o Logigate, desenvolvido pela Hongayetu Lda, uma plataforma avançada de automação aduaneira e gestão financeira.">
        <meta name="twitter:creator" content="@hongayetu">
        <meta name="twitter:image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg">

        <!-- Open Graph data -->
        <meta property="og:title" content="Logigate | Sistema de Gestão Aduaneira e Financeira" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://www.logigate.ao" />
        <meta property="og:image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg" />
        <meta property="og:description" content="Logigate é uma solução desenvolvida pela Hongayetu Lda para gestão aduaneira, financeira e contabilística, garantindo automação e eficiência nos processos de despacho e controle financeiro." />
        <meta property="og:site_name" content="Logigate" />


        <!-- CSS Files -->
        <link href="{{ asset('ly_login/css/bootstrap.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('ly_login/css/paper-bootstrap-wizard.css') }}" rel="stylesheet" />

		<!-- CSS Just for demo purpose, don't include it in your project -->
		<link href="{{ asset('ly_login/css/demo.css') }}" rel="stylesheet" />

		<!-- Fonts and Icons -->
		<link href="https://netdna.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.css" rel="stylesheet">
		<link href='https://fonts.googleapis.com/css?family=Muli:400,300' rel='stylesheet' type='text/css'>
		<link href="{{ asset('ly_login/css/themify-icons.css') }}" rel="stylesheet">
	</head>

	<body>	
        <div class="image-container set-full-height" style="background-image: url({{ asset('dist/img/logistic_bg_login.jpg') }})">
            <!--   Creative Tim Branding   -->
            <a href="https://logigate.ao">
                <div class="logo-container">
                    <div class="logo">
                        <img src="{{ asset('dist/img/LOGIGATE.png') }}" style="width: 100px;">
                    </div>
                </div>
            </a>

            <!--   Big container   -->
            <div class="container">
                <div class="row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <!--      Wizard container        -->
                        <div class="wizard-container">

                            <div class="card wizard-card" data-color="blue" id="wizardProfile">
                                <x-validation-errors class="mb-4" />

                                @session('status')
                                    <div class="mb-4 font-medium text-sm text-green-600">
                                        {{ $value }}
                                    </div>
                                @endsession

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="wizard-header text-center">
                                        <h3 class="wizard-title">Logigate</h3>
                                        <p class="category">Faça login para aceder a sua conta.</p>
                                    </div>

                                    <div class="wizard-navigation">
                                        <div class="progress-with-circle">
                                            <div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="1" style="width: 21%;"></div>
                                        </div>
                                        <ul>
                                            <li>
                                                <a href="#acesso" data-toggle="tab">
                                                    <div class="icon-circle">
                                                        <i class="ti-lock"></i>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-content">
									    <div class="tab-pane" id="acesso">
                                            <div class="row">
                                                <div class="col-sm-10 col-sm-offset-1">
                                                    <label for="email">{{ __('Email') }}</label>
                                                    <input id="email" class="form-control" type="email" name="email" :value="old('email')" required autofocus autocomplete="username">
                                                </div>

                                                <div class="col-sm-10 col-sm-offset-1">
                                                    <label for="password">{{ __('Password') }}</label>
                                                    <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password">
                                                </div>

                                                <div class="col-sm-10 col-sm-offset-1 mt-4" style="padding-top: 5px;">
                                                    <label for="remember_me" class="flex items-center">
                                                        <x-checkbox id="remember_me" name="remember" />
                                                        <span class="ms-2 text-sm text-gray-600">{{ __('Lembra-me') }}</span>
                                                    </label>
                                                </div>

                                                <div class="col-sm-10 col-sm-offset-1 mt-4" style="padding-top: 5px;">
                                                    @if (Route::has('password.request'))
                                                        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                                                            {{ __('Esqueci a palavra-passe') }}
                                                        </a>
                                                    @endif
                                                </div>
                                                <br>
                                                <div class="col-sm-10 col-sm-offset-1" style="padding-top: 20px;">
                                                    <div class="pull-center">
                                                        <x-button class='btn btn-dark btn-wd'>
                                                            {{ __('Log in') }}
                                                        </x-button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <!--   Core JS Files   -->
	<script src="{{ asset('ly_login/js/jquery-2.2.4.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('ly_login/js/bootstrap.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('ly_login/js/jquery.bootstrap.wizard.js') }}" type="text/javascript"></script>

	<!--  Plugin for the Wizard -->
	<script src="{{ asset('ly_login/js/demo.js') }}" type="text/javascript"></script>
	<script src="{{ asset('ly_login/js/paper-bootstrap-wizard.js') }}" type="text/javascript"></script>

	<!--  More information about jquery.validate here: https://jqueryvalidation.org/	 -->
	<script src="{{ asset('ly_login/js/jquery.validate.min.js') }}" type="text/javascript"></script>
</html>