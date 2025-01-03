<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- SEO -->
        <title>{{ config('app.name', 'Logigate') }}</title>
        <meta name="description" content="{{ $page_description ?? 'Sistema de Gestão de Processos Aduaneiros' }}">
        <meta name="keywords" content="{{ $page_keywords ?? 'Sistema, Gestão, Logistica', 'Aduaneira', 'Despachantes', 'Processos' }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('dist/img/LOGIGATE.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Font-Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <!-- Ionicons -->
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Toastr -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
        <!-- DataTables -->
        <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
        <!-- Select2 -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet">
        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
        <!-- Tempusdominus Bootstrap 4 -->
        <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
        <!-- iCheck -->
        <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
        <!-- JQVMap -->
        <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
        <!-- overlayScrollbars -->
        <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
        <!-- Daterange picker -->
        <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
        <!-- Flag Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">

        <!-- Select2 -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-XXXXX-X"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'UA-XXXXX-X');
        </script>

        <!-- Rich Snippets -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Logigate",
            "url": "https://logigate.com",
            "logo": "https://logigate.com/logo.png",
            "Acesso" : "https://logigate.com/login",
            "Registro" : "https://logigate.com/register",
            "sameAs": [
                "https://facebook.com/logigate",
                "https://instagram.com/logigate"
            ]
        }
        </script>

        <!-- Mobile Optimization -->
        <meta name="theme-color" content="#ffffff">
    </head>

    <body class="hold-transition sidebar-mini layout-fixed">

        <div class="wrapper">
            <!-- Menu Header (Navbar) -->
            @livewire('navigation-menu')
            <!-- /.Menu Header (Navbar) -->
            
            <!-- Menu Aside -->
            <x-menu-aside />

            <main class="content-wrapper">
                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-dark shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif
                
                <div style="padding: 10px;">
                    <!-- Incluir componentes de validação -->
                    @include('components.validation-errors')
                    @include('components.validation-success')

                    {{ $slot }}
                </div>
                
            </main>
        </div>

        <footer class="main-footer foot-dark">
            <strong>Copyright &copy; 2023 <a href="https://adminlte.io"><b style="color: green;">Honga</b>Yetu</a>.</strong>
            Todos os Direitos Reservados. <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.2.0</div>
        </footer>
        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-navy">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->

        @stack('modals')

        @livewireScripts
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Bootstrap Bundle (includes Popper) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Toastr -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <!-- DataTables  & Plugins -->
        <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
        <script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
        <!-- Select2 -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Moment.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <!-- Axios -->
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <!-- Alpine.js -->
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
        <!-- AdminLTE App -->
        <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
        <!-- AdminLTE for demo purposes -->
        <script src="{{ asset('dist/js/demo.js') }}"></script>
        <!-- bs-custom-file-input -->
        <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    </body>
</html>
