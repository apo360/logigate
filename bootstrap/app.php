<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    // ROTAS PRINCIPAIS
    ->withRouting(
        web: [
            __DIR__ . '/../routes/web.php',       // Rotas normais
            __DIR__ . '/../routes/master.php',    // Rotas admin SEPARADAS
            __DIR__ . '/../routes/customers.php', // Rotas clientes
            __DIR__ . '/../routes/test.php',      // Rotas de teste
        ],

        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
    )

    // MIDDLEWARES
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'admin.master'   => \App\Http\Middleware\AdminMasterMiddleware::class,
            'customer.auth'  => \App\Http\Middleware\CustomerAuthMiddleware::class,
        ]);

        /**
         * IMPORTANTE:
         * NÃO adicionamos nada global
         * que force autenticação nas rotas admin.
         */
    })

    // EXCEÇÕES
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })

    ->create();
