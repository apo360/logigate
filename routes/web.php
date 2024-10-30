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
use App\Http\Controllers\ContaCorrenteController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\ExportadorController;
use App\Http\Controllers\IbanController;
use App\Http\Controllers\LicenciamentoController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MercadoriaController;
use App\Http\Controllers\MigracaoController;
use App\Http\Controllers\ModuleSubscriptionController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\ProcessoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UserController;
use App\Models\Empresa;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\CustomerAvencaController;
use App\Http\Controllers\WebPage\RastreamentoController;
use App\Http\Controllers\PautaAduaneiraController;

    /** Rotas WEB */
    Route::get('/', function () { $modulos = Module::all(); return view('welcome', compact('modulos')); });
    // Exibir o formulário de consulta
    Route::get('/consultar-licenciamento', [RastreamentoController::class, 'consultarLicenciamento'])->name('consultar.licenciamento');

    // Processar a pesquisa do código
    Route::post('/consultar-licenciamento', [RastreamentoController::class, 'resultadoConsulta'])->name('resultado.consulta');

    Route::get('Verificar-Cedula', [CedulaController::class, 'create'])->name('cedula');
    Route::get('Registo', function(){ return view('auth.register_manual'); })->name('verificar.manual');
    Route::post('Verificar-Cedula', [CedulaController::class, 'validar'])->name('cedula.verificar');

    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('logout');

    Route::get('/verify-otp', [OtpController::class, 'showVerifyOtpForm'])->middleware('auth');
    Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->name('confirmaOtp')->middleware('auth');
    Route::post('/resend-otp', [OtpController::class, 'sendOtp'])->middleware('auth');

    Route::get('/password/change', [PasswordController::class, 'showChangeForm'])->name('password.change')->middleware('check.password.changed');
    Route::post('re/password/change', [PasswordController::class, 'changePassword'])->name('password.change.store')->middleware('check.password.changed');
    
    Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
        
        // Sistema de Controlle de usuarios. 
        Route::get('/usuarios/block/{id}', [UserController::class, 'block'])->name('usuarios.block');
        Route::get('/usuarios/unblock/{id}', [UserController::class, 'unblock'])->name('usuarios.unblock');
        Route::get('/usuarios/resert/{id}', [UserController::class, 'resert_pass'])->name('usuarios.resetPassword');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard-RH', function () {return view('dashboard_rh'); })->name('dashboard.rh');
        
        Route::resources([
            'activated-modules' => ActivatedModuleController::class,
            'arquivos' => ArquivoController::class,
            'customers' => CustomerController::class,
            'documentos' => DocumentoController::class,
            'empresas' => EmpresaController::class,
            'exportadors' => ExportadorController::class,
            'menus' => MenuController::class,
            'mercadorias' => MercadoriaController::class,
            'modules' => ModuleController::class,
            'permissions' => PermissionsController::class,
            'processos' => ProcessoController::class,
            'roles' => Roles::class,
            'usuarios' => UserController::class,
            'produtos' => ProdutoController::class,
            'licenciamentos' => LicenciamentoController::class,
            'modulos' => ModuleController::class,
            'avenca' => CustomerAvencaController::class,
        ]);

        Route::get('/import-pauta-aduaneira', [PautaAduaneiraController::class, 'import_view'])->name('pauta.import');
        Route::post('/import-pauta-aduaneira', [PautaAduaneiraController::class, 'import'])->name('pauta.import.submit');

        // Rota personalizada para criar mercadorias com licenciamento ou processo
        Route::get('/mercadorias/create/{licenciamento_id?}/{processo_id?}', [MercadoriaController::class, 'create'])->name('mercadorias.createWithParams');
        // web.php
        Route::get('get-codigo-aduaneiro/{cod_pauta}', [MercadoriaController::class, 'getCodigosAduaneiros'])->name('pauta.get');

        // Rota o Rascunho do Licenciamento
        Route::post('licenciamento/rascunho', [LicenciamentoController::class, 'storeDraft'])->name('licenciamento.rascunho.store');

        Route::get('customer/conta_corrente/Listagem', [CustomerController::class, 'index_conta'])->name('customers.listagem_cc');
        Route::get('customers/{id}/conta_corrente', [CustomerController::class, 'conta'])->name('cliente.cc');
        Route::get('customers/avenca/listagem', [CustomerController::class, 'avenca_list'])->name('cliente.listagem.avenca');
        Route::get('customers/{id}/avenca', [CustomerController::class, 'avenca'])->name('cliente.avenca');
        //Route::get('processos/{id}/documentos/factura', [DocumentoController::class, 'create'])->name('documentos.create');

        // Rota para Inserir Grupo/Categoria de Produtos
        Route::post('/produto/grupo/insert', [ProdutoController::class, 'InsertGrupo'])->name('insert.grupo.produto');
            
        Route::prefix('customer/conta_corrente')->group(function () {
            Route::get('/create/{cliente_id}', [ContaCorrenteController::class, 'create'])->name('conta_corrente.create');
            Route::post('/store/{cliente_id}', [ContaCorrenteController::class, 'store'])->name('conta_corrente.store');
        });

        // Rotas específicas de usuários e funções
        Route::prefix('users/{user}')->group(function () {
            Route::get('assign-role', [UserRoleController::class, 'showAssignRoleForm'])->name('users.showAssignRoleForm');
            Route::post('assign-role', [UserRoleController::class, 'assignRole'])->name('users.assignRole');
            Route::post('remove-role', [UserRoleController::class, 'removeRole'])->name('users.removeRole');
        });

        Route::get('/users/{user}/permissions', [UserController::class, 'editPermissions'])->name('usuarios.permissions');
        Route::post('/users/{user}/permissions', [UserController::class, 'storePermissions'])->name('usuarios.permissions.store');

        Route::get('empresa/cambios', [PaisController::class, 'list_cambios'])->name('empresa.cambio');
        Route::put('empresa/cambios/actualizar', [PaisController::class, 'update'])->name('cambios.update');

        Route::get('processo/tarifas', [ProcessoController::class, 'tarifas'])->name('processos.tarifa');
        Route::get('processo/rastreamento', [ProcessoController::class, 'rastreamento'])->name('processos.rastreamento');
        Route::get('processo/autorizacoes', [ProcessoController::class, 'autorizacao'])->name('processos.autorizar');
        Route::get('processo/DU-Electronico', [ProcessoController::class, 'du_electronico'])->name('processos.du');
        Route::post('processo/buscar', [ProcessoController::class, 'buscarProcesso'])->name('processos.buscar');
        Route::post('processo/atualizar-codigo-aduaneiro', [ProcessoController::class, 'atualizarCodigoAduaneiro'])->name('processos.atualizarCodigoAduaneiro');
        Route::get('processo/imprimir', [ProcessoController::class, 'print'])->name('processos.print');
        Route::get('processo/gerar-xml/{IdProcesso}', [ProcessoController::class, 'GerarXml'])->name('gerar.xml');
        Route::get('licenciamento/gerar-txt/{IdProcesso}', [LicenciamentoController::class, 'GerarTxT'])->name('gerar.txt');
        Route::get('processo/imprimir/{IdProcesso}/requisicao')->name('processo.print.requisicao');


        Route::get('/subscricao/{empresa}', [ModuleSubscriptionController::class, 'show'])->name('subscribe.view');
        Route::post('/subscricao/pagamentos', [ModuleSubscriptionController::class, 'pay'])->name('payment.pay');

        Route::get('empresa/migracao', [MigracaoController::class, 'create'])->name('empresa.migracao');
        Route::post('empresa/banco/inserir', [IbanController::class, 'insertConta'])->name('banco.inserir');
        Route::post('empresa/importar/clientes', [MigracaoController::class, 'importCustomers'])->name('import.customers');
        Route::post('empresa/importar/exportadores', [MigracaoController::class, 'importExportadores'])->name('import.exportadores');
        Route::post('empresa/importar/processos', [MigracaoController::class, 'importProcessos'])->name('import.processos');
        Route::post('empresa/logotipo/inserir', [EmpresaController::class, 'storeLogo'])->name('empresa.logotipo');

        Route::get('processos/report/{ProcessoID}/visualizar', [RelatorioController::class, 'generateReport'])->name('processos.print');
        // Documentos
        Route::get('documentos/facturas/{invoiceNo}/visualizar', [RelatorioController::class, 'InvoicesSales'])->name('documento.print');
        Route::get('documentos/facturas/{invoiceNo}/download', [DocumentoController::class, 'DownloadDocumento'])->name('documento.download');
        Route::get('documentos/facturas/{invoiceNo}/{destinatario}/email', [DocumentoController::class, 'EnviarPorEmail'])->name('documento.email');
        Route::get('documentos/efetuar-pagamento/{id}', [DocumentoController::class, 'ViewPagamento'])->name('documento.ViewPagamento');
        Route::post('documentos/efetuar-pagamento/{id}', [DocumentoController::class, 'efetuarPagamento'])->name('documento.efetuarPagamento');
        
        // API
        Route::get('/processos/{customerId}/{status}', [ProcessoController::class, 'getProcessesByIdAndStatus']);
        Route::get('/customers/{customerId}/{status}', [CustomerController::class, 'getProcessoByCustomer']);
    });
