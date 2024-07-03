@once
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Carregar a imagem em cache antes de aplicar ao fundo
            var img = new Image();
            img.src = '{{ asset('dist/img/logistic_bg_login.jpg') }}';
            img.onload = function() {
                // Ajustar a qualidade da imagem de fundo com base na largura da tela
                var screenWidth = $(window).width();
                if (screenWidth > 1200) {
                    $('body').css('background-image', 'url({{ asset('dist/img/logistic_bg_login.jpg') }})');
                } else {
                    // Use uma versão de imagem menor ou uma técnica diferente para imagens maiores
                    $('body').css('background-image', 'url({{ asset('dist/img/logistic_bg_login_small.jpg') }})');
                }
            };
        });
    </script>
@endonce

<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background: url('{{ asset('dist/img/logistic_bg_login.jpg') }}') no-repeat center center fixed; background-size: cover;">
    <div>
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        {{ $slot }}
    </div>
</div>
