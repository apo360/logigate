<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Logigate | Login</title>
    <meta name="description" content="Aceda à plataforma Logigate para gerir processos aduaneiros, documentos e operações da sua empresa.">
    <link rel="canonical" href="{{ url('/login') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --brand: #0047AB;
            --brand-dark: #073B83;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #F7FAFC;
            color: #0F172A;
        }

        .focus-ring:focus-visible {
            outline: 3px solid #38BDF8;
            outline-offset: 3px;
        }

        .login-visual {
            background-image:
                linear-gradient(135deg, rgba(3, 22, 51, .92), rgba(0, 71, 171, .78)),
                url('{{ asset('dist/img/logistic_bg_login.jpg') }}');
            background-size: cover;
            background-position: center;
        }

        .btn-primary {
            min-height: 46px;
            border-radius: 8px;
            background: var(--brand);
            color: #FFFFFF;
            font-weight: 800;
            transition: background-color .2s ease;
        }

        .btn-primary:hover {
            background: var(--brand-dark);
        }
    </style>
</head>
<body>
    <main class="min-h-screen lg:grid lg:grid-cols-2">
        <section class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-10">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="focus-ring mb-8 inline-flex items-center gap-3 rounded">
                    <img src="{{ asset('dist/img/LOGIGATE.png') }}" alt="" class="h-11 w-auto">
                    <span class="text-xl font-extrabold tracking-tight">Logi<span class="text-blue-700">Gate</span></span>
                </a>

                <div class="mb-8">
                    <p class="text-sm font-bold uppercase tracking-wide text-blue-700">Acesso seguro</p>
                    <h1 class="mt-2 text-3xl font-extrabold text-gray-900">Entrar na plataforma</h1>
                    <p class="mt-3 text-gray-600">Use as credenciais da sua conta para gerir processos, documentos e operações aduaneiras.</p>
                </div>

                @if (session('status'))
                    <div class="mb-5 rounded border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                <x-validation-errors class="mb-5 rounded border border-red-200 bg-red-50 p-4 text-sm text-red-800" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-800">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="focus-ring mt-2 w-full rounded border border-gray-300 px-4 py-3 text-gray-900 shadow-sm"
                            placeholder="nome@empresa.ao">
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-4">
                            <label for="password" class="block text-sm font-bold text-gray-800">Senha</label>
                            <a href="{{ route('password.request') }}" class="focus-ring rounded text-sm font-semibold text-blue-700 hover:text-blue-900">Esqueci a senha</a>
                        </div>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="focus-ring mt-2 w-full rounded border border-gray-300 px-4 py-3 text-gray-900 shadow-sm"
                            placeholder="A sua senha">
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <input id="remember_me" type="checkbox" name="remember" class="focus-ring h-4 w-4 rounded border-gray-300 text-blue-700">
                            Lembrar-me
                        </label>
                    </div>

                    <button type="submit" class="btn-primary focus-ring w-full">
                        Entrar
                    </button>
                </form>

                <div class="mt-8 border-t border-gray-200 pt-6">
                    <p class="text-sm font-bold text-gray-900">Outros acessos</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('cliente.portal.login') }}" class="focus-ring rounded border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-800 hover:border-blue-700 hover:text-blue-700">
                            Portal Cliente
                        </a>
                        <a href="#" class="focus-ring rounded border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-800 hover:border-blue-700 hover:text-blue-700">
                            Transitário
                        </a>
                    </div>
                    <a href="{{ route('home') }}" class="focus-ring mt-5 inline-flex rounded text-sm font-semibold text-blue-700 hover:text-blue-900">
                        <i class="fas fa-arrow-left mr-2 mt-1" aria-hidden="true"></i>Voltar ao início
                    </a>
                </div>
            </div>
        </section>

        <aside class="login-visual hidden min-h-screen items-center px-10 py-12 text-white lg:flex">
            <div class="max-w-xl">
                <div class="mb-8 inline-flex rounded bg-white px-3 py-2 text-sm font-extrabold text-blue-800">
                    LOGIGATE 2026
                </div>
                <h2 class="text-4xl font-extrabold leading-tight">Gestão aduaneira, documentos e processos num único lugar</h2>
                <p class="mt-5 text-lg leading-8 text-blue-50">
                    Centralize a operação, acompanhe processos e ofereça aos seus clientes uma experiência moderna e transparente.
                </p>

                <div class="mt-10 grid gap-3">
                    @foreach(['Processos Aduaneiros', 'Gestão Documental', 'Portal Cliente', 'Pagamentos Digitais'] as $item)
                        <div class="rounded border border-white border-opacity-30 bg-white bg-opacity-10 px-4 py-3 font-semibold">
                            <i class="fas fa-check mr-2 text-green-300" aria-hidden="true"></i>{{ $item }}
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </main>
</body>
</html>
