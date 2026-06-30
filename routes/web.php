<?php

use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\OtpController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ActivatedModuleController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\Roles;
use App\Http\Controllers\ArquivoController;
use App\Http\Controllers\CedulaController;
use App\Http\Controllers\BillingPlanController;
use App\Http\Controllers\ContaCorrenteController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\DocumentoArquivoController;
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
use App\Http\Controllers\ContabilidadeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\CustomerAvencaController;
use App\Http\Controllers\WebPage\RastreamentoController;
use App\Http\Controllers\PautaAduaneiraController;
use App\Http\Controllers\PortoController;
use Illuminate\Http\Request;
use App\Http\Controllers\AsycudaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\ScheduledTaskController;
use App\Http\Controllers\WebPage\WelcomeController;
use App\Http\Controllers\AppyPayWebhookController;
use App\Models\Processo;

    /** Rotas WEB */
    Route::get('/', [WelcomeController::class, 'index'])->name('home');

    // Exibir o formulário de consulta da Pauta Aduaneira
    Route::get('/consultar-pauta-aduaneira', [WelcomeController::class, 'consultarPauta'])->name('consultar.pauta');

    // MarketPlace
    Route::get('/mercado', [WelcomeController::class, 'marketplace'])->name('marketplace');

    // Rotas de Checkout (Pagamento da Subscrição Rápida)
    Route::get('/cadastro-/{conta}/Confirmar-Pagamento', function(){return view('pagamentos.pagamento-quick');})->name('checkout');

    // Bloco Teste FIM AppPay

    // Exibir o formulário de consulta
    Route::get('/consultar-licenciamento', [RastreamentoController::class, 'consultarLicenciamento'])->name('consultar.licenciamento');

    // Processar a pesquisa do código
    Route::post('/consultar-licenciamento', [RastreamentoController::class, 'resultadoConsulta'])->name('resultado.consulta');

    Route::get('Verificar-Cedula', [CedulaController::class, 'create'])->name('cedula');
    Route::get('Registo', function(){ return view('auth.register_manual'); })->name('verificar.manual');
    Route::post('Verificar-Cedula', [CedulaController::class, 'validarCedulaLocal'])->name('cedula.verificar');

    Route::get('/verify-otp', [OtpController::class, 'showVerifyOtpForm'])->middleware('auth');
    Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->name('confirmaOtp')->middleware('auth');
    Route::post('/resend-otp', [OtpController::class, 'sendOtp'])->middleware('auth');

    Route::get('/password/change', [PasswordController::class, 'showChangeForm'])->name('password.change')->middleware('check.password.changed');
    Route::post('re/password/change', [PasswordController::class, 'changePassword'])->name('password.change.store')->middleware('check.password.changed');

    Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {

        Route::post('/logout', function (Request $request) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login');
        })->name('logout');
        
        // ------------- Rotas para os despachantes ------------ //
        // Sistema de Controlle de usuarios. 
        // State-changing user management routes must be non-GET so they stay
        // behind CSRF protection and cannot be triggered by crawlers/links.
        Route::post('/usuarios/block/{id}', [UserController::class, 'block'])->name('usuarios.block');
        Route::post('/usuarios/unblock/{id}', [UserController::class, 'unblock'])->name('usuarios.unblock');
        Route::post('/usuarios/resert/{id}', [UserController::class, 'resert_pass'])->name('usuarios.resetPassword');

        Route::get('/billing/plans', [BillingPlanController::class, 'index'])->name('billing.plans');
        Route::post('/billing/plans', [BillingPlanController::class, 'start'])->name('billing.start');
        Route::view('/admin/integracoes', 'admin.integracoes')->name('admin.integracoes');
        Route::view('/integracoes', 'admin.integracoes')->name('integracoes.index');
        Route::view('/configuracoes', 'empresa.configuracoes')->name('configuracoes.index');
        Route::view('/seguranca-auditoria', 'empresa.auditoria')->name('logs.index');

        Route::middleware('check.subscription')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/dashboard-RH', function () {return view('dashboard_rh'); })->name('dashboard.rh');
            Route::get('/dashboard-Licenciamento', [DashboardController::class, 'licenciamentoEstatisticas'])->name('licenciamento.estatistica');
            Route::get('/dashboard-Processos', [DashboardController::class, 'ProcessosEstatisticas'])->name('processos.estatistica');
            Route::get('/dashboard-Factura', [DashboardController::class, 'FacturaEstatisticas'])->name('factura.estatistica');
        });

        Route::resources([
            'activated-modules' => ActivatedModuleController::class,
            'documentos' => DocumentoController::class,
            'empresas' => EmpresaController::class,
            'exportadors' => ExportadorController::class,
            'menus' => MenuController::class,
            'modules' => ModuleController::class,
            'permissions' => PermissionsController::class,
            'roles' => Roles::class,
            'usuarios' => UserController::class,
            'produtos' => ProdutoController::class,
            'modulos' => ModuleController::class,
            'avenca' => CustomerAvencaController::class,
        ]);

        Route::view('/arquivos', 'arquivo.index')->name('arquivos.index');
        Route::resource('arquivos', ArquivoController::class)->except(['index']);

        Route::get('/documentos-arquivo/{documentoArquivo}/preview', [DocumentoArquivoController::class, 'preview'])->name('documentos-arquivo.preview');
        Route::get('/documentos-arquivo/{documentoArquivo}/download', [DocumentoArquivoController::class, 'download'])->name('documentos-arquivo.download');
        Route::delete('/documentos-arquivo/{documentoArquivo}', [DocumentoArquivoController::class, 'destroy'])->name('documentos-arquivo.destroy');

        Route::prefix('customers/conta_corrente')->group(function () {
            Route::get('/create/{cliente_id}', [ContaCorrenteController::class, 'create'])->name('conta_corrente.create');
            Route::post('/store/{cliente_id}', [ContaCorrenteController::class, 'store'])->name('conta_corrente.store');
            Route::get('/Listagem', [CustomerController::class, 'index_conta'])->name('customers.listagem_cc');
            Route::get('/{id}/show', [CustomerController::class, 'conta'])->name('cliente.cc');
            Route::get('/{id}/avenca', [CustomerController::class, 'avenca'])->name('cliente.avenca');
        });

        // =========================
        // Routes example (routes/api.php)
        // =========================
        Route::prefix('scheduled-tasks')->group(function() {
            Route::get('/', [ScheduledTaskController::class, 'index']);
            Route::post('/', [ScheduledTaskController::class, 'store']);
            Route::get('/{id}', [ScheduledTaskController::class, 'show']);
            Route::put('/{id}', [ScheduledTaskController::class, 'update']);
            Route::post('/{id}/approve', [ScheduledTaskController::class, 'approve']);
            Route::post('/{id}/run-now', [ScheduledTaskController::class, 'runNow']);
        });

        /**
         *  
         */
        Route::get('/Tarefas', [ScheduledTaskController::class, 'index'])->name('leander.dashboard');

        // Arquivos no S3
        Route::get('/arquivo/download/{key}', [ArquivoController::class, 'download'])->where('key', '.*')->name('arquivos.dowload');
        Route::post('/arquivo/MoveMassa', [ArquivoController::class, 'bulkActions'])->name('arquivos.bulkActions');
        Route::get('/arquivo/pasta-view', [ArquivoController::class, 'PastaView'])->name('PastaAbrir');
        Route::post('/arquivo/criar-pasta', [ArquivoController::class, 'criarPasta'])->name('arquivos.criarPasta');
        Route::get('/arquivo/visualizar/{key}', [ArquivoController::class, 'visualizar'])->where('key', '.*')->name('arquivos.visualizar');


        Route::get('relatorios/licenciamento/{tipo}', [RelatorioController::class, 'RelatorioLicenciamento'])->name('relatorio.visualizar');
        Route::get('licenciamento/relatorio', [RelatorioController::class, 'SelecionarRelatorio'])->name('relatorio.licenciamento');

        // web.php
        Route::get('get-codigo-aduaneiro/{cod_pauta}', [MercadoriaController::class, 'getCodigosAduaneiros'])->name('pauta.get');
        Route::get('get-portos/{paisId}', [PortoController::class, 'getPortos'])->name('portos.get');

        // Rota o Rascunho do Licenciamento
        Route::post('licenciamento/rascunho', [LicenciamentoController::class, 'storeDraft'])->name('licenciamento.rascunho.store');
        Route::post('/licenciamentos/import', [LicenciamentoController::class, 'import'])->name('licenciamentos.import');
        Route::get('licenciamentos/listas/pice', [LicenciamentoController::class, 'pice'])->name('licenciamentos.pice');


        // Rota para Inserir Grupo/Categoria de Produtos
        Route::post('/produto/grupo/insert', [ProdutoController::class, 'InsertGrupo'])->name('insert.grupo.produto');
        // toggle.produto
        Route::post('/produto/toggle/{id}', [ProdutoController::class, 'updateStatus'])->name('toggle.produto'); //updateStatus
        // Rota para Atualizar Preço do Produto
        Route::post('/produto/{produto}/actualizar-preço', [ProdutoController::class, 'updatePrice'])->name('produtos.updatePrice');
        // Rota para pegar a view de atualização de preço
        Route::get('/produto/{produto}/actualizar-preço', [ProdutoController::class, 'showUpdatePriceForm'])->name('produtos.showUpdatePriceForm');

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

        // Rotas para analisar com IA Chatgpt
        Route::get('processo/DU-Electronico/lista', [AsycudaController::class, 'listarXMLs'])->name('processos.du');
        Route::post('processo/DU-Electronico/upload', [AsycudaController::class, 'uploadXML'])->name('asycuda.upload.post');
        Route::post('processo/DU-Electronico/analisar', [AsycudaController::class, 'analyze'])->name('asycuda.analyze.post');
        Route::post('processo/DU-Electronico/validar', [AsycudaController::class, 'validateFile'])->name('asycuda.validate.post');
        Route::get('processo/DU-Electronico/analisar/{file}', [AsycudaController::class, 'analisarDeclaracao'])->name('xmls.analisar');

        Route::get('processo/gerar-xml/{IdProcesso}', [ProcessoController::class, 'GerarXml'])->name('gerar.xml');
        Route::get('processos/{processo}/simulador-pauta', function (Processo $processo) {
            Gate::authorize('simulate', $processo);

            return redirect()->route('processos.edit', ['processo' => $processo->id, 'tab' => 'simulacao']);
        })->name('processos.simulador-pauta');
        Route::post('/processo/finalizar/{processoID}', [ProcessoController::class, 'processoFinalizar'])->name('processo.finalizar');
        Route::get('/processo/nao-finalizados', [ProcessoController::class, 'processosNaoFinalizados']);

        Route::get('processos/report/{ProcessoID}/visualizar', [ProcessoController::class, 'printNotaDespesa'])->name('processos.print');
        Route::post('processos/report/{ProcessoID}/imprimir-carta', [ProcessoController::class, 'printCartaDiversa'])->name('processos.imprimirCarta');
        Route::get('processos/report/{ProcessoID}/Extrato-Mercadoria', [ProcessoController::class, 'printExtratoMercadoria'])->name('processos.Extrato_mercadoria');

        Route::post('processo/imprimir/{IdProcesso}/requisicao', [ProcessoController::class, 'printCartaDiversa'])->name('processo.print.requisicao');
        Route::post('licenciamento/mercadorias/reagrupar/{licenciamentoId}', [MercadoriaController::class, 'reagrupar'])->name('mercadorias.reagrupar');

        // Deprecated legacy flow. Keep reachable only through explicit legacy links,
        // not from the SaaS onboarding path that now ends in checkout.
        Route::get('/subscricao/{empresa}', [ModuleSubscriptionController::class, 'show'])->name('subscribe.view');
        Route::post('/subscricao/pagamentos', function () {
            abort(410, 'Fluxo legado de pagamento de módulos inativo. Use o checkout AppyPay.');
        })->name('payment.pay');

        Route::get('empresa/migracao', [MigracaoController::class, 'create'])->name('empresa.migracao');
        Route::post('empresa/banco/inserir', [IbanController::class, 'insertConta'])->name('banco.inserir');
        Route::post('empresa/importar/clientes', [MigracaoController::class, 'importCustomers'])->name('import.customers');
        Route::post('empresa/importar/exportadores', [MigracaoController::class, 'importExportadores'])->name('import.exportadores');
        Route::post('empresa/importar/processos', [MigracaoController::class, 'importProcessos'])->name('import.processos');
        Route::post('empresa/logotipo/inserir', [EmpresaController::class, 'storeLogo'])->name('empresa.logotipo');

        // Pauta Aduaneira
        Route::get('pauta-aduaneira', [PautaAduaneiraController::class, 'index'])->name('pauta.index');
        Route::get('pauta-aduaneira/simulador', [PautaAduaneiraController::class, 'simulador'])->name('pauta.simulador');
        Route::get('pauta-aduaneira/importar', [PautaAduaneiraController::class, 'import'])->name('pauta.import_view');
        Route::get('pauta-aduaneira/consultar', [PautaAduaneiraController::class, 'consultar'])->name('pauta.consultar');
        Route::get('pauta-aduaneira/{id}', [PautaAduaneiraController::class, 'show'])->whereNumber('id')->name('pauta.show');

        // Contabilidade Aduaneira
        Route::get('contabilidade/contas', [ContabilidadeController::class, 'contas'])->name('contabilidade.contas');
        Route::get('contabilidade/lancamentos', [ContabilidadeController::class, 'lancamentos'])->name('contabilidade.lancamentos');
        Route::get('contabilidade/relatorios', [ContabilidadeController::class, 'relatorios'])->name('contabilidade.relatorios');
        Route::get('contabilidade/configuracoes', [ContabilidadeController::class, 'configuracoes'])->name('contabilidade.configuracoes');
        Route::get('contabilidade/plano-contas', [ContabilidadeController::class, 'planoContas'])->name('contabilidade.plano_contas');
        Route::get('contabilidade/mapa', [ContabilidadeController::class, 'mapa'])->name('contabilidade.mapa');
        Route::get('contabilidade/balanco', [ContabilidadeController::class, 'balanco'])->name('contabilidade.balancete');

        // Documentos
        Route::get('documentos/facturas/{invoiceNo}/visualizar', [RelatorioController::class, 'generateInvoices'])->name('documento.print');
        Route::get('documentos/facturas/{invoiceNo}/download', [DocumentoController::class, 'DownloadDocumento'])->name('documento.download');
        Route::post('documentos/facturas/{invoiceNo}/{destinatario}/email', [DocumentoController::class, 'EnviarPorEmail'])->name('documento.email');
        Route::get('documentos/efetuar-pagamento/{id}', [PagamentoController::class, 'ViewPagamento'])->name('documento.ViewPagamento');
        Route::post('documentos/efetuar-pagamento/{id}', [PagamentoController::class, 'efetuarPagamento'])->name('documento.efetuarPagamento');
        Route::get('documentos/filtrar', [DocumentoController::class, 'filtrar'])->name('faturas.filtrar');
        // Documentos (Recibo)
        Route::get('documentos/recibos', [DocumentoController::class, ''])->name('documentos.emitir.recibo');
        // ------------- /.Rotas para os despachantes ------------ //


        Route::resource('customers', CustomerController::class)->except(['store', 'update']);
        Route::resource('licenciamentos', LicenciamentoController::class)->except(['store', 'update']);
        Route::resource('processos', ProcessoController::class)->except(['store', 'update']);

    });
