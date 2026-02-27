<?php

use App\Http\Controllers\APIs\LogsController;
use App\Http\Controllers\APIs\NifVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppyPayWebhookController;

Route::get('/user', function (Request $request) { return $request->user(); })->middleware('auth:sanctum');

Route::get('/log-alert', [LogsController::class, 'getLogAlerts']);

// routes/api.php
Route::match(['post','get'], '/webhooks/appypay', [AppyPayWebhookController::class, 'handle']);

// API para verificar NIF dos Customers
Route::prefix('verify-nif')->group(function () {
    Route::get('cliente/{nif}', [NifVerificationController::class, 'verifyCliente']);
    Route::get('exportador/{nif}', [NifVerificationController::class, 'verifyExportador']);
});

