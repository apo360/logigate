<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebPage\CustomerAuthController;
use App\Http\Controllers\WebPage\CustomerWebController;
// Rotas para os clientes
    // Rota para exibir/verificar o PIN (com middleware web)
    Route::middleware(['web'])->group(function () {
        Route::match(['get', 'post'], 'verify-nif', [CustomerAuthController::class, 'verifyNif'])->name('customer.verify-nif');
    });

    // Rotas protegidas pelo middleware customer.auth
    Route::middleware(['web','customer.auth'])->group(function () {
        Route::prefix('cliente')->group(function () {
            Route::get('/dashboard', [CustomerWebController::class, 'index'])->name('customer.web.dashboard');
            Route::get('/profile', [CustomerWebController::class, 'profile'])->name('customer.web.profile');
            Route::post('/logout', [CustomerWebController::class, 'logout'])->name('customer.web.logout');
        });
    });