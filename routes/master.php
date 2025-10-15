
<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;

    Route::post('verify-pin', function (Request $request) {
        // Check rate limit
        if (RateLimiter::tooManyAttempts('verify-pin', 3)) {
            $seconds = RateLimiter::availableIn('verify-pin');
            return response()->json([
                'success' => false,
                'message' => 'Acesso bloqueado. Tente novamente em ' . ceil($seconds / 60) . ' minutos.',
            ], 429);
        }
    
        // Validate the PIN
        $request->validate([
            'pin' => ['required', 'string', 'size:' . config('admin.pin_length')],
        ]);
    
        // Verify the PIN
        if (password_verify($request->input('pin'), env('ADMIN_MASTER_PIN_HASH'))) {
            // Authenticate the admin
            session([config('admin.session_key') => true]);
    
            // Clear rate limiter
            RateLimiter::clear('verify-pin');
    
            return response()->json([
                'success' => true,
                'redirect_url' => route('admin.dashboard'),
            ]);
        } else {
            // Increment failed attempts
            RateLimiter::hit('verify-pin');
    
            return response()->json([
                'success' => false,
                'message' => 'PIN inválido. Tentativas restantes: ' . (3 - RateLimiter::attempts('verify-pin')),
            ]);
        }
    })->name('verify-pin');

    
// Grupo de rotas protegidas para o Administrador Master
// Route::middleware(['admin.master'])->group(function () {
    // Dashboard do Administrador Master
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Gerenciamento de usuários
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');

    // Gerenciamento de paises
    Route::get('/admin/countries', [AdminController::class, 'countries'])->name('admin.countries');

    // Gerenciamento de portos
    Route::get('/admin/ports', [AdminController::class, 'ports'])->name('admin.ports');

    // Gerenciamento de products
    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');

    // Gerenciamento de categorias
    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');

    // Configurações do sistema
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');

    // Gerenenciamento de Pricing
    Route::get('/admin/pricing', [AdminController::class, 'pricing'])->name('admin.pricing');
// });


    