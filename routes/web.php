<?php

use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\OtpController;
use App\Http\Middleware\EnsureOtpIsVerified;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ActivatedModuleController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\Roles;
use App\Http\Controllers\ArquivoController;
use App\Http\Controllers\CedulaController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportadorController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MercadoriaController;
use App\Http\Controllers\ModuleSubscriptionController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\ProcessoController;
use App\Models\Empresa;
use App\Models\Module;
use Illuminate\Support\Facades\Log;

Route::domain('{company}.logigate.com')->group(function () {
    Route::get('/', function ($company) {
        Log::info("Acessando subdomínio: $company");
        $company = Empresa::where('Dominio', $company)->first();

        if ($company) {
            Log::info("Redirecionando para login para a empresa: $company->nome");
            return redirect()->route('login');
        }

        Log::error("Empresa não encontrada para o subdomínio: $company");

        $erro = "Empresa não encontrada para o subdomínio";
        return view('auth.erro_empresa', compact('erro'));
    });
});

Route::get('/', function () { $modulos = Module::all();
    return view('welcome', compact('modulos')); });

    Route::get('Verificar-Cedula', [CedulaController::class, 'create'])->name('cedula');
    Route::post('Verificar-Cedula', [CedulaController::class, 'validar'])->name('cedula.verificar');

    Route::resources(['modulos' => ModuleController::class]);

Route::get('/verify-otp', [OtpController::class, 'showVerifyOtpForm'])->middleware('auth');
Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->name('confirmaOtp')->middleware('auth');
Route::post('/resend-otp', [OtpController::class, 'sendOtp'])->middleware('auth');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), EnsureOtpIsVerified::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
        Route::resource('permissions', PermissionsController::class)->middleware('role');

        Route::resources([
            'roles' => Roles::class,
            'modules' => ModuleController::class,
            'activated-modules' => ActivatedModuleController::class,
            'menus' => MenuController::class,
            'processos' => ProcessoController::class,
            'customers' => CustomerController::class,
            'exportadors' => ExportadorController::class,
            'arquivos' => ArquivoController::class,
            'processos' => ProcessoController::class,
            'empresas' => EmpresaController::class,
            'mercadorias' => MercadoriaController::class,

        ]);
        
        // Rotas específicas de usuários e funções
        Route::prefix('users/{user}')->group(function () {
            Route::get('assign-role', [UserRoleController::class, 'showAssignRoleForm'])->name('users.showAssignRoleForm');
            Route::post('assign-role', [UserRoleController::class, 'assignRole'])->name('users.assignRole');
            Route::post('remove-role', [UserRoleController::class, 'removeRole'])->name('users.removeRole');
        });

        Route::get('empresa/cambios', [PaisController::class, 'list_cambios'])->name('empresa.cambio');
        Route::put('empresa/cambios/actualizar', [PaisController::class, 'update'])->name('cambios.update');

        Route::get('processo/tarifas', [ProcessoController::class, 'tarifas'])->name('processos.tarifa');
        Route::get('processo/rastreamento', [ProcessoController::class, 'rastreamento'])->name('processos.rastreamento');
        Route::get('processo/autorizacoes', [ProcessoController::class, 'autorizacao'])->name('processos.autorizar');
        Route::get('processo/DU-Electronico', [ProcessoController::class, 'du_electronico'])->name('processos.du');
        Route::get('processo/imprimir', [ProcessoController::class, 'print'])->name('processos.print');

        Route::get('/subscricao/{empresa}', [ModuleSubscriptionController::class, 'show'])->name('subscribe.view');
        Route::post('/subscricao/pagamentos', [ModuleSubscriptionController::class, 'pay'])->name('payment.pay');

});

