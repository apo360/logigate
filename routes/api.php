<?php

use App\Http\Controllers\APIs\LogsController;
use App\Http\Controllers\APIs\NifVerificationController;
use App\Http\Controllers\APIs\PautaAduaneiraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppyPayWebhookController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\NewsletterSubscriberController;

Route::prefix('v1')->group(function () {
    // Contact form
    Route::post('/contact/send', [ContactMessageController::class, 'send'])->name('contact.send');
    
    // Newsletter
    Route::middleware('throttle:5,1')->post('/newsletter/subscribe', [NewsletterSubscriberController::class, 'subscribe']);
    Route::post('/newsletter/subscribe', [NewsletterSubscriberController::class, 'subscribe'])->name('newsletter.subscribe');
    Route::post('/newsletter/unsubscribe', [NewsletterSubscriberController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
    Route::get('/newsletter/confirm/{token}', [NewsletterSubscriberController::class, 'confirm'])->name('newsletter.confirm');
    
    // Rotas públicas com rate limiting
    Route::middleware('throttle:60,1')->group(function () {
        
        // Listar com filtros
        Route::get('/pauta', [PautaAduaneiraController::class, 'index']);
        
        // Busca avançada
        Route::get('/pauta/busca', [PautaAduaneiraController::class, 'search']);
        
        // Sugestões (autocomplete)
        Route::get('/pauta/sugestoes', [PautaAduaneiraController::class, 'suggestions']);
        
        // Estatísticas
        Route::get('/pauta/estatisticas', [PautaAduaneiraController::class, 'statistics']);
        
        // Exportar (requer rate limit menor)
        Route::middleware('throttle:30,1')->get('/pauta/exportar', [PautaAduaneiraController::class, 'export']);
    });

    // Detalhe de código específico (sem limite adicional)
    Route::get('/pauta/{codigo}', [PautaAduaneiraController::class, 'show'])->where('codigo', '[0-9\.]+');
});


    Route::get('/user', function (Request $request) { return $request->user(); })->middleware('auth:sanctum');

    Route::get('/log-alert', [LogsController::class, 'getLogAlerts']);

    // routes/api.php
    Route::match(['post','get'], '/webhooks/appypay', [AppyPayWebhookController::class, 'handle']);

    // API para verificar NIF dos Customers
    Route::prefix('verify-nif')->group(function () {
        Route::get('cliente/{nif}', [NifVerificationController::class, 'verifyCliente']);
        Route::get('exportador/{nif}', [NifVerificationController::class, 'verifyExportador']);
    });

