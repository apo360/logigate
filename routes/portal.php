<?php

use App\Http\Controllers\ClientePortal\Auth\ClientePortalLoginController;
use App\Http\Controllers\ClientePortal\Auth\ClientePortalLogoutController;
use App\Http\Controllers\ClientePortal\Auth\ClientePortalResetPasswordController;
use App\Http\Controllers\ClientePortal\ClientePortalDashboardController;
use App\Http\Controllers\ClientePortal\ClientePortalDocumentoController;
use App\Http\Controllers\ClientePortal\ClientePortalEmpresaContextController;
use App\Http\Controllers\ClientePortal\ClientePortalLicenciamentoController;
use App\Http\Controllers\ClientePortal\ClientePortalProcessoController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal-cliente')
    ->name('cliente.portal.')
    ->group(function () {
        Route::get('/Acesso', [ClientePortalLoginController::class, 'show'])->name('login');
        Route::post('/Acesso', [ClientePortalLoginController::class, 'store'])->name('login.submit');
        Route::get('/Recuperar-Senha', [ClientePortalResetPasswordController::class, 'show'])->name('password.reset');
        Route::post('/Recuperar-Senha', [ClientePortalResetPasswordController::class, 'store'])->name('password.reset.submit');

        Route::middleware(['auth:cliente_portal', 'cliente.portal.active'])->group(function () {
            Route::get('/dashboard', ClientePortalDashboardController::class)->name('dashboard');
            Route::post('/logout', ClientePortalLogoutController::class)->name('logout');

            Route::get('/processos', [ClientePortalProcessoController::class, 'index'])->name('processos.index');
            Route::get('/processos/{processoId}', [ClientePortalProcessoController::class, 'show'])
                ->whereNumber('processoId')
                ->name('processos.show');
            
            Route::get('/licenciamentos', [ClientePortalLicenciamentoController::class, 'index'])->name('licenciamentos.index');
            Route::get('/licenciamentos/{licenciamentoId}', [ClientePortalLicenciamentoController::class, 'show'])
                ->whereNumber('licenciamentoId')
                ->name('licenciamentos.show');
            Route::get('/rastreamento', [ClientePortalLicenciamentoController::class, 'rastreamento'])->name('licenciamentos.rastreamento');
            Route::post('/rastreamento', [ClientePortalLicenciamentoController::class, 'result'])->name('licenciamentos.result');

            Route::get('/documentos', [ClientePortalDocumentoController::class, 'index'])->name('documentos.index');
            Route::post('/documentos', [ClientePortalDocumentoController::class, 'upload'])->name('documentos.upload');
            Route::get('/documentos/{documentoId}/download', [ClientePortalDocumentoController::class, 'download'])
                ->whereNumber('documentoId')
                ->name('documentos.download');

            Route::post('/empresa-contexto', [ClientePortalEmpresaContextController::class, 'update'])->name('empresa-context.update');
        });
    });
