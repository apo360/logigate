<!doctype html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#061f40">
    <title>Portal do cliente — Acesso | LogiGate</title>
    <meta name="description" content="Aceda ao Portal do Cliente LogiGate com as credenciais disponibilizadas pela sua empresa ou despachante.">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/website/portal-login.css') }}">
    <script defer src="{{ asset('js/website/portal-login.js') }}"></script>
</head>
<body class="portal-login-page">
    <a class="portal-skip" href="#portal-content">Saltar para o conteúdo</a>
    @include('WebSite.partials.menu_website')
    <main id="portal-content" tabindex="-1">
        <div class="portal-container">
            <nav class="portal-breadcrumb" aria-label="Caminho da página"><a href="{{ route('home') }}">Início</a><span aria-hidden="true">/</span><span aria-current="page">Portal do cliente</span></nav>
            <div class="portal-layout">
                <section class="portal-intro" aria-labelledby="portal-title">
                    <div class="portal-intro-copy">
                        <p class="portal-eyebrow">PORTAL DO CLIENTE · LOGIGATE</p>
                        <h1 id="portal-title">A sua operação,<br><span>mais perto de si.</span></h1>
                        <p class="portal-lead">Acompanhe os processos e documentos que a sua empresa ou despachante disponibiliza para si.</p>
                        <ul class="portal-features">
                            <li>@include('WebSite.partials.website-icon', ['icon' => 'file'])<div><strong>Consulte os seus processos</strong><p>Aceda à informação partilhada sobre a sua operação.</p></div></li>
                            <li>@include('WebSite.partials.website-icon', ['icon' => 'signature'])<div><strong>Encontre os documentos</strong><p>Consulte os documentos disponibilizados no portal.</p></div></li>
                            <li>@include('WebSite.partials.website-icon', ['icon' => 'user'])<div><strong>Um acesso disponibilizado para si</strong><p>Utilize as credenciais fornecidas por quem acompanha a sua operação.</p></div></li>
                        </ul>
                    </div>
                    <div class="portal-visual"><img src="{{ asset('dist/img/LandingPage/port-desktop-1600.webp') }}" width="1600" height="900" alt="" loading="lazy"><span>Ilustração conceptual · contexto portuário</span></div>
                </section>
                <section class="portal-access-panel" aria-labelledby="access-title">
                    <div class="portal-card">
                        <span class="portal-access-icon" aria-hidden="true">@include('WebSite.partials.website-icon', ['icon' => 'login'])</span>
                        <p class="portal-eyebrow">O SEU ESPAÇO DE ACOMPANHAMENTO</p>
                        <h2 id="access-title">Bem-vindo ao seu portal</h2>
                        <p class="portal-card-copy">Introduza as suas credenciais para continuar.</p>
                        @if ($portalStatus = ($status ?? session('status')))
                            <div class="portal-message portal-message-status" role="status">{{ $portalStatus }}</div>
                        @endif
                        @if (session('error'))
                            <div class="portal-message portal-message-error" role="alert">{{ session('error') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="portal-message portal-message-error" role="alert"><strong>Não foi possível entrar.</strong><p>Verifique os campos indicados e tente novamente.</p>
                                @foreach ($errors->getMessages() as $field => $messages)
                                    @if (!in_array($field, ['login', 'password']))
                                        @foreach ($messages as $message)<p>{{ $message }}</p>@endforeach
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        <form action="{{ route('cliente.portal.login.submit') }}" method="POST" class="portal-form" data-portal-login>
                            @csrf
                            <div class="portal-field">
                                <label for="login">Identificador, telefone ou email</label>
                                <input type="text" id="login" name="login" value="{{ old('login') }}" maxlength="255" autocomplete="username" autocapitalize="none" spellcheck="false" required aria-describedby="login-help{{ $errors->has('login') ? ' login-error' : '' }}" @if($errors->has('login')) aria-invalid="true" @endif>
                                <p id="login-help" class="portal-field-help">Use o identificador ou contacto associado ao seu acesso.</p>
                                @error('login')<p id="login-error" class="portal-field-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="portal-field">
                                <div class="portal-label-row"><label for="password">Palavra-passe</label><a href="{{ route('cliente.portal.password.reset') }}">Precisa de ajuda?</a></div>
                                <div class="portal-password-wrap"><input type="password" id="password" name="password" autocomplete="current-password" required @if($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif><button type="button" class="portal-password-toggle" data-password-toggle aria-controls="password" aria-pressed="false" hidden>Mostrar</button></div>
                                @error('password')<p id="password-error" class="portal-field-error">{{ $message }}</p>@enderror
                            </div>
                            <label class="portal-remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Manter sessão iniciada</span></label>
                            <button type="submit" class="portal-submit">Entrar no portal <span aria-hidden="true">→</span></button>
                        </form>
                        <p class="portal-reset-note">Para recuperar a palavra-passe, contacte o seu despachante.</p>
                        <div class="portal-account-help"><strong>Ainda não tem acesso?</strong><p>Peça as suas credenciais à empresa ou ao despachante que acompanha a sua operação.</p></div>
                    </div>
                    <p class="portal-business-access">É uma empresa ou despachante? <a href="{{ route('login') }}">Entrar na aplicação →</a></p>
                </section>
            </div>
        </div>
    </main>
    @include('WebSite.partials.footer_website')
</body>
</html>