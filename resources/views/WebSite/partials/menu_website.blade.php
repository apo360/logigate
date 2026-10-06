@once
    <link rel="stylesheet" href="{{ asset('css/website/website-shell.css') }}">
    <script defer src="{{ asset('js/website/website-shell.js') }}"></script>
@endonce
@php
    $websiteLinks = [
        ['home', 'Início', 'home'],
        ['consultar.pauta', 'Consultar Tarifas', 'file'],
        ['marketplace', 'Marketplace', 'store'],
        ['consultar.licenciamento', 'Licenciamento', 'signature'],
    ];
@endphp
<header class="website-header" data-website-header>
    <div class="website-shell website-header-row">
        <a class="website-brand" href="{{ route('home') }}" aria-label="LogiGate — página inicial"><img src="{{ asset('dist/img/LandingPage/logo-horizontal.webp') }}" width="720" height="201" alt="LOGIGATE"></a>
        <button class="website-menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="website-navigation" hidden>@include('WebSite.partials.website-icon', ['icon' => 'menu'])</button>
        <nav id="website-navigation" class="website-navigation" aria-label="Navegação principal">
            @foreach ($websiteLinks as [$destination, $label, $icon])
                <a href="{{ route($destination) }}" @if(request()->routeIs($destination)) aria-current="page" @endif>@include('WebSite.partials.website-icon', ['icon' => $icon])<span>{{ $label }}</span></a>
            @endforeach
            <a href="{{ route('cliente.portal.login') }}" @if(request()->routeIs('cliente.portal.login', 'cliente.portal.password.reset')) aria-current="page" @endif>@include('WebSite.partials.website-icon', ['icon' => 'user'])<span>Portal do cliente</span></a>
            @if (Route::has('login'))
                @auth
                    <a class="website-access" href="{{ url('/dashboard') }}">@include('WebSite.partials.website-icon', ['icon' => 'login'])<span>Dashboard</span></a>
                @else
                    @if (Route::has('register'))<a href="{{ route('register') }}">@include('WebSite.partials.website-icon', ['icon' => 'user-plus'])<span>Registar</span></a>@endif
                    <a class="website-access" href="{{ route('login') }}">@include('WebSite.partials.website-icon', ['icon' => 'login'])<span>Acesso</span></a>
                @endauth
            @endif
        </nav>
    </div>
</header>
