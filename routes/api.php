<?php

use App\Http\Controllers\APIs\LogsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) { return $request->user(); })->middleware('auth:sanctum');

// Route::group(['middleware' => ['api.key']], function () {
    
// });

Route::get('/log-alert', [LogsController::class, 'getLogAlerts']);

