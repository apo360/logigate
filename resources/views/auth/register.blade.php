<!doctype html>
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
		                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registerForm">
								@csrf
		                <!--        You can switch " data-color="orange" "  with one of the next bright colors: "blue", "green", "orange", "red", "azure"          -->

		                    	<div class="wizard-header text-center">
		                        	<h3 class="wizard-title">Logigate</h3>
									<p class="category">Inicie aqui o seu registo.</p>
		                    	</div>

								<div class="wizard-navigation">
									<div class="progress-with-circle">
									     <div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="5" style="width: 21%;"></div>
									</div>
									<ul>
										<li>
											<a href="#cedula" data-toggle="tab">
												<div class="icon-circle">
													<i class="ti-user"></i>
												</div>
												{{__('Cedula')}}
											</a>
										</li>
			                            <li>
											<a href="#about" data-toggle="tab">
												<div class="icon-circle">
													<i class="ti-user"></i>
												</div>
												{{__('Despachante')}}
											</a>
										</li>
			                            <li>
											<a href="#manager" data-toggle="tab">
												<div class="icon-circle">
													<i class="ti-settings"></i>
												</div>
												{{__('Representante')}}
											</a>
										</li>
			                            <li>
											<a href="#account" data-toggle="tab">
												<div class="icon-circle">
													<i class="ti-map"></i>
												</div>
												Conta
											</a>
										</li>
										
										<li>
											<a href="#termos" data-toggle="tab">
												<div class="icon-circle">
													<i class="ti-map"></i>
												</div>
												Termos
											</a>
										</li>
			                        </ul>
								</div>
		                        <div class="tab-content">
									<div class="tab-pane" id="cedula">
		                            	<div class="row">
											<h5 class="info-text"> {{__('Por favor, Valide o seu Nº de Cedula.')}}</h5>
											<div class="col-sm-10 col-sm-offset-1">
												<div class="form-group">
													<label for="cedula">{{__('Nº Cedula')}} <small>(required)</small></label>
													<input name="cedula" type="text" class="form-control" placeholder="Introduza o numero da cedula do despachante">
												</div>
											</div>
											<h5 class="info-text"> {{__('Designação')}} {{__('Escolha apenas um')}} </h5>
											<div class="row">
												<div class="col-sm-8 col-sm-offset-2">
													<div class="col-sm-4">
														<div class="choice" data-toggle="wizard-checkbox">
															<input type="radio" name="Designacao" value="Design">
															<div class="card card-checkboxes card-hover-effect">
																<i class="ti-paint-roller"></i>
																<p>Design</p>
															</div>
														</div>
													</div>
													<div class="col-sm-4">
														<div class="choice" data-toggle="wizard-checkbox">
															<input type="radio" name="Designacao" value="Despachante Oficial">
															<div class="card card-checkboxes card-hover-effect">
																<i class="ti-pencil-alt"></i>
																<p>Despachante</p>
															</div>
														</div>
													</div>
													<div class="col-sm-4">
														<div class="choice" data-toggle="wizard-checkbox">
															<input type="radio" name="Designacao" value="Transitário">
															<div class="card card-checkboxes card-hover-effect">
																<i class="ti-star"></i>
																<p>Transitário</p>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
		                            </div>
		                            <div class="tab-pane" id="about">
		                            	<div class="row">
											<h5 class="info-text"> {{__('Dados do despachante.')}} </h5>
											<div class="col-sm-3 col-sm-offset-1">
												<div class="picture-container">
													<div class="picture">
														<img src="{{ asset('ly_login/img/default-avatar.jpg')}}" class="picture-src" id="wizardPicturePreview" title="" />
														<input type="file" id="wizard-picture" name="logotipo">
													</div>
													<h6>Escolher logotipo</h6>
												</div>
											</div>
											<div class="col-sm-7">
												<div class="form-group">
													<label for="empresa">{{ __('Empresa') }} <small>(required)</small></label>
													<input name="empresa" id="empresa" type="text" class="form-control" placeholder="Nome da Empresa">
												</div>
												<div class="form-group">
													<label for="nif">{{ __('NIF') }} <small>(required)</small></label>
													<input name="nif" id="nif" type="text" class="form-control" placeholder="NIF da Empresa">
												</div>
											</div>
											<div class="col-sm-10 col-sm-offset-1">
												<div class="row">
													<div class="col-sm-6">
														<div class="form-group">
															<label for="provincia">{{__('Província')}}</label>
															<select name="provincia" id="provincia" class="form-control">
																<option value="">{{__('Selecionar')}}</option>
                                                                @foreach(\App\Models\Provincia::all() as $prov)
                                                                    <option value="{{$prov->id}}">{{$prov->Nome}}</option>
                                                                @endforeach
															</select>
														</div>
													</div>
													<div class="col-sm-6">
														<div class="for-group">
															<div class="form-group">
																<label for="cidade">{{__('Cidade')}}</label>
																<select name="cidade" id="cidade" class="form-control">
																	<option value="">{{__('Selecionar')}}</option>
																</select>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col-sm-10 col-sm-offset-1">
												<div class="form-group">
													<label for="endereco">{{ __('Endereço') }} <small>(required)</small></label>
													<input name="endereco" type="text" class="form-control" placeholder="{{__('Endereço Completo')}}">
												</div>
											</div>
										</div>
		                            </div>
		                            <div class="tab-pane" id="manager">
		                                <h5 class="info-text"> {{__('Informação do Representante')}} </h5>
		                                <div class="row">
		                                    <div class="col-sm-10 col-sm-offset-1">
												<div class="form-group">
													<label for="name"> {{__('Primeiro Nome')}} <small>(required)</small></label>
													<input name="name" type="text" class="form-control" placeholder="Estefania...">
												</div>
												<div class="form-group">
													<label> {{__('Apelido')}} <small>(required)</small></label>
													<input name="apelido" type="text" class="form-control" placeholder="Costa...">
												</div>
											</div>
											<div class="col-sm-10 col-sm-offset-1">
												<div class="row">
													<div class="col-sm-5">
														<div class="form-group">
															<label for="telefone">{{ __('Telefone') }} </label>
															<input name="telefone" type="number" class="form-control" placeholder="{{__('Número de Contacto')}}">
														</div>
													</div>
													<div class="col-sm-6">
														<div class="form-group">
															<label for="">{{__('Tipo de Representante')}}</label>
															<select name="tipo_representante" id="tipo_representante" class="form-control">
																<option value="">{{__('Selecionar')}}</option>
                                                                <option value="Contabilista">{{__('Contabilista')}}</option>
                                                                <option value="Auditor">{{__('Auditor')}}</option>
                                                                <option value="Juridico">{{__('Juridico')}}</option>
                                                                <option value="Próprio">{{__('O Próprio')}}</option>
															</select>
														</div>
													</div>
												</div>
												
											</div>
		                                </div>
		                            </div>
		                            <div class="tab-pane" id="account">
		                                <div class="row">
		                                    <div class="col-sm-12">
		                                        <h5 class="info-text"> {{__('Falta Mais um passo')}} </h5>
		                                    </div>
		                                    <div class="col-sm-10 col-sm-offset-1">
												<div class="form-group">
													<label for="email">{{ __('Email') }} <small>(required)</small></label>
													<input name="email" type="email" class="form-control" placeholder="{{__('estefania.costa@hongayetu.com')}}">
												</div>
											</div>
		                                    <div class="col-sm-10 col-sm-offset-1">
		                                        <div class="form-group">
		                                            <label for="password">{{ __('Password') }}</label>
		                                            <input type="password" name="password" id="password" class="form-control" placeholder="*******">
		                                        </div>
		                                    </div>
		                                    <div class="col-sm-10 col-sm-offset-1">
		                                        <div class="form-group">
		                                            <label for="password_confirmation">{{ __('Confirm Password') }}</label>
		                                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="New York...">
		                                        </div>
		                                    </div>
		                                </div>
		                            </div>
									<div class="tab-pane" id="termos">
		                                <div class="row">
		                                    <div class="col-sm-12">
		                                        <h5 class="info-text"> {{__('Termos de Serviços e Politicas de Privacidade')}} </h5>
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
		                                </div>
		                            </div>
		                        </div>
		                        <div class="wizard-footer">
		                            <div class="pull-right">
		                                <input type='button' class='btn btn-next btn-fill btn-warning btn-wd' name='next' value="{{__('Próximo')}}" />
                                        <x-button class="ms-4" class='btn btn-finish btn-fill btn-success btn-wd'>
                                            {{ __('Register') }}
                                        </x-button>
		                            </div>

		                            <div class="pull-left">
		                                <input type='button' class='btn btn-previous btn-default btn-wd' name='previous' value="{{__('Anterior')}}" />
										<a class='btn btn-home btn-primary btn-wd' href="/" >{{__('Pagina')}}</a>
		                            </div>
		                            <div class="clearfix"></div>
		                        </div>
		                    </form>
		                </div>
		            </div> <!-- wizard container -->
		        </div>
	    	</div><!-- end row -->
		</div> <!--  big container -->

	    <div class="footer">
	        <div class="container text-center">
				<div class="flex items-center justify-end mt-4">
					<a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
						{{ __('Já tenho conta?') }}
					</a>
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

    <script type="text/javascript">
        $(document).ready(function() {
            // Pré-carregar cidades em um objeto JavaScript
            var cidades = @json(\App\Models\Municipio::all());

            $('#provincia').change(function() {
                var provinciaId = $(this).val();
                $('#cidade').empty();
                $('#cidade').append('<option value="">{{__("Selecionar")}}</option>');

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
        });
    </script>
    
</html>

