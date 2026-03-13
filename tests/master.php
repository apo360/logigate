<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
// Rotas para o administrador master
    // Rota para exibir/verificar o PIN (com middleware web)
    Route::middleware(['web'])->group(function () {
        Route::match(['get', 'post'], 'verify-pin', [AdminAuthController::class, 'verifyPin'])->name('verify-pin');
    });

    // Rotas protegidas pelo middleware admin.master
    Route::middleware(['web','admin.master'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
            Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
            Route::get('/countries', [AdminController::class, 'countries'])->name('admin.countries');
            Route::get('/ports', [AdminController::class, 'ports'])->name('admin.ports');
            Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
            Route::get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
            Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
            Route::get('/pricing', [AdminController::class, 'pricing'])->name('admin.pricing');
        });
    });
