<?php

use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\OtpController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ActivatedModuleController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\Roles;
use App\Http\Controllers\AgenteCarga\DashboardController as AgenteCargaDashboardController;
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
use App\Http\Controllers\ContabilidadeController;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\CustomerAvencaController;
use App\Http\Controllers\EmolumentoTarifaController;
use App\Http\Controllers\GpsTrakerController;
use App\Http\Controllers\WebPage\RastreamentoController;
use App\Http\Controllers\PautaAduaneiraController;
use App\Http\Controllers\PortoController;
use App\Http\Controllers\ProcessoDraftController;
use App\Http\Controllers\Transitario\DashboardController as TransitarioDashboardController;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AsycudaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\SAFtController;
use Illuminate\Support\Facades\DB;

    /** Rotas WEB */
    Route::get('/', function () { $modulos = Module::all(); return view('welcome', compact('modulos')); });
    // Exibir o formulário de consulta
    Route::get('/consultar-licenciamento', [RastreamentoController::class, 'consultarLicenciamento'])->name('consultar.licenciamento');

    // Processar a pesquisa do código
    Route::get('/marketplace', function() { 
        $produtos = [
            ['nome' => 'Produto 1', 'descricao' => 'Descrição do Produto 1', 'imagem' => 'https://via.placeholder.com/150'],
            ['nome' => 'Produto 2', 'descricao' => 'Descrição do Produto 2', 'imagem' => 'https://via.placeholder.com/150'],
        ];
        return view('WebSite.marketplace', compact('produtos'));
    })->name('marketplace');

    // Exibir o formulário de consulta da Pauta Aduaneira
    Route::get('/consultar-pauta-aduaneira', [PautaAduaneiraController::class, 'consultarPauta'])->name('consultar.pauta');

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
        Route::get('/usuarios/block/{id}', [UserController::class, 'block'])->name('usuarios.block');
        Route::get('/usuarios/unblock/{id}', [UserController::class, 'unblock'])->name('usuarios.unblock');
        Route::get('/usuarios/resert/{id}', [UserController::class, 'resert_pass'])->name('usuarios.resetPassword');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard-RH', function () {return view('dashboard_rh'); })->name('dashboard.rh');
        Route::get('/dashboard-Licenciamento', [DashboardController::class, 'licenciamentoEstatisticas'])->name('licenciamento.estatistica');
        Route::get('/dashboard-Processos', [DashboardController::class, 'ProcessosEstatisticas'])->name('processos.estatistica');
        Route::get('/dashboard-Factura', [DashboardController::class, 'FacturaEstatisticas'])->name('factura.estatistica');

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
            'emolumento_tarifas' => EmolumentoTarifaController::class,
            'processos-drafts' => ProcessoDraftController::class,
        ]);

        Route::prefix('customers')->group(function () {
            Route::post('/toggle-status/{id}', [CustomerController::class, 'toggleStatus']);
            Route::post('/{id}/documents', [CustomerController::class, 'documentosStore'])->name('customers.documents.store');
            Route::get('/export-csv', [CustomerController::class, 'exportCsv'])->name('customers.exportCsv');
            Route::get('/export-excel', [CustomerController::class, 'exportExcel'])->name('customers.exportExcel');
            Route::post('/import', [CustomerController::class, 'CustomerImport'])->name('customers.import');
            Route::get('/Ficha/{id}Imprimir', [CustomerController::class, 'ImprimirFicha'])->name('customers.ficha_imprimir');
            Route::prefix('/conta_corrente')->group(function () {
                Route::get('/create/{cliente_id}', [ContaCorrenteController::class, 'create'])->name('conta_corrente.create');
                Route::post('/store/{cliente_id}', [ContaCorrenteController::class, 'store'])->name('conta_corrente.store');
                Route::get('/Listagem', [CustomerController::class, 'index_conta'])->name('customers.listagem_cc');
                Route::get('/{id}/show', [CustomerController::class, 'conta'])->name('cliente.cc');
                Route::get('/avenca/listagem', [CustomerController::class, 'avenca_list'])->name('cliente.listagem.avenca');
                Route::get('/{id}/avenca', [CustomerController::class, 'avenca'])->name('cliente.avenca');
            });
        });

        // Arquivos no S3
        Route::get('/arquivo/download/{key}', [ArquivoController::class, 'download'])->name('arquivos.dowload');
        Route::post('/arquivo/MoveMassa', [ArquivoController::class, 'bulkActions'])->name('arquivos.bulkActions');
        Route::get('/arquivo/pasta-view', [ArquivoController::class, 'PastaView'])->name('PastaAbrir');
        Route::post('/arquivo/criar-pasta', [ArquivoController::class, 'criarPasta'])->name('arquivos.criarPasta');
        Route::get('/arquivo/visualizar/{key}', [ArquivoController::class, 'visualizar'])->name('arquivos.visualizar');


        Route::get('relatorios/licenciamento/{tipo}', [RelatorioController::class, 'RelatorioLicenciamento'])->name('relatorio.visualizar');
        Route::get('licenciamento/relatorio', [RelatorioController::class, 'SelecionarRelatorio'])->name('relatorio.licenciamento');

        // Rota personalizada para criar mercadorias com licenciamento ou processo
        Route::get('/mercadorias/create/{licenciamento_id?}{processo_id?}', [MercadoriaController::class, 'create'])->name('mercadorias.createWithParams');
        
        // web.php
        Route::get('get-codigo-aduaneiro/{cod_pauta}', [MercadoriaController::class, 'getCodigosAduaneiros'])->name('pauta.get');
        Route::get('get-portos/{paisId}', [PortoController::class, 'getPortos'])->name('portos.get');

        // Rota o Rascunho do Licenciamento
        Route::post('licenciamento/rascunho', [LicenciamentoController::class, 'storeDraft'])->name('licenciamento.rascunho.store');
        Route::get('licenciamento/gerar-txt/{IdProcesso}', [LicenciamentoController::class, 'GerarTxT'])->name('gerar.txt');
        Route::get('licenciamento/export-csv', [LicenciamentoController::class, 'exportCsv'])->name('licenciamentos.exportCsv');
        Route::get('licenciamento/export-excel', [LicenciamentoController::class, 'exportExcel'])->name('licenciamentos.exportExcel');
        Route::post('/licenciamentos/import', [LicenciamentoController::class, 'import'])->name('licenciamentos.import');
        Route::get('licenciamentos/gerarProcesso/{idLicenciamento}', [LicenciamentoController::class, 'ConstituirProcesso'])->name('gerar.processo');
        Route::get('licenciamentos/duplicar/{idLicenciamento}', [LicenciamentoController::class, 'DuplicarLicenciamento'])->name('licenciamentos.duplicar');
        Route::get('licenciamentos/listas/pice', [LicenciamentoController::class, 'pice'])->name('licenciamentos.pice');


        // Rota para Inserir Grupo/Categoria de Produtos
        Route::post('/produto/grupo/insert', [ProdutoController::class, 'InsertGrupo'])->name('insert.grupo.produto');
        // toggle.produto
        Route::get('/produto/toggle/{id}', [ProdutoController::class, 'updateStatus'])->name('toggle.produto'); //updateStatus


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

        Route::post('processo/buscar', [ProcessoController::class, 'buscarProcesso'])->name('processos.buscar');
        Route::post('processo/atualizar-codigo-aduaneiro', [ProcessoController::class, 'atualizarCodigoAduaneiro'])->name('processos.atualizarCodigoAduaneiro');
        Route::get('processo/gerar-xml/{IdProcesso}', [ProcessoController::class, 'GerarXml'])->name('gerar.xml');
        Route::post('/processo/finalizar/{processoID}', [ProcessoController::class, 'processoFinalizar'])->name('processo.finalizar');
        Route::get('/processo/nao-finalizados', [ProcessoController::class, 'processosNaoFinalizados']);

        Route::get('processos/report/{ProcessoID}/visualizar', [ProcessoController::class, 'printNotaDespesa'])->name('processos.print');
        Route::post('processos/report/{ProcessoID}/imprimir-carta', [ProcessoController::class, 'printCartaDiversa'])->name('processos.imprimirCarta');
        Route::get('processos/report/{ProcessoID}/Extrato-Mercadoria', [ProcessoController::class, 'printExtratoMercadoria'])->name('processos.Extrato_mercadoria');


        Route::get('processo/imprimir/{IdProcesso}/requisicao', [ProcessoController::class, 'printCartaDiversa'])->name('processo.print.requisicao');
        Route::post('licenciamento/mercadorias/reagrupar/{licenciamentoId}', [MercadoriaController::class, 'reagrupar'])->name('mercadorias.reagrupar');

        Route::get('/subscricao/{empresa}', [ModuleSubscriptionController::class, 'show'])->name('subscribe.view');
        Route::post('/subscricao/pagamentos', [ModuleSubscriptionController::class, 'pay'])->name('payment.pay');

        Route::get('empresa/migracao', [MigracaoController::class, 'create'])->name('empresa.migracao');
        Route::post('empresa/banco/inserir', [IbanController::class, 'insertConta'])->name('banco.inserir');
        Route::post('empresa/importar/clientes', [MigracaoController::class, 'importCustomers'])->name('import.customers');
        Route::post('empresa/importar/exportadores', [MigracaoController::class, 'importExportadores'])->name('import.exportadores');
        Route::post('empresa/importar/processos', [MigracaoController::class, 'importProcessos'])->name('import.processos');
        Route::post('empresa/logotipo/inserir', [EmpresaController::class, 'storeLogo'])->name('empresa.logotipo');

        // Pauta Aduaneira
        Route::get('pauta-aduaneira', [PautaAduaneiraController::class, 'index'])->name('pauta.index');
        Route::get('pauta-aduaneira/importar', [PautaAduaneiraController::class, 'import'])->name('pauta.import_view');
        Route::get('pauta-aduaneira/consultar', [PautaAduaneiraController::class, 'consultar'])->name('pauta.consultar');

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
        Route::get('documentos/facturas/{invoiceNo}/{destinatario}/email', [DocumentoController::class, 'EnviarPorEmail'])->name('documento.email');
        Route::get('documentos/efetuar-pagamento/{id}', [PagamentoController::class, 'ViewPagamento'])->name('documento.ViewPagamento');
        Route::post('documentos/efetuar-pagamento/{id}', [PagamentoController::class, 'efetuarPagamento'])->name('documento.efetuarPagamento');
        Route::get('documentos/filtrar', [DocumentoController::class, 'filtrar'])->name('faturas.filtrar');
        
        // API
        Route::get('/processos/{customerId}/{status}', [ProcessoController::class, 'getProcessesByIdAndStatus']);
        Route::get('/customers/{customerId}/{status}', [CustomerController::class, 'getProcessoByCustomer']); 
        Route::get('API/Services/GpsTraker/', [GpsTrakerController::class, 'index'])->name('gps.index');
        // /. API

        // Teste SAF-T
        Route::get('/saft/{year}/{start}/{end}/build', [SAFtController::class, 'buildSAFT'])->name('saft.build');

        // ------------- /.Rotas para os despachantes ------------ //

        // Rotas do Transitário
        Route::prefix('transitario')->group(function () {
            Route::get('/dashboard', [TransitarioDashboardController::class, 'dashboard'])->name('transitario.dashboard');
        });
        // ------------- /.Rotas do Transitário ------------ //

        // Rotas do Agente de Carga
        Route::prefix('agente_carga')->group(function () {
            Route::get('/dashboard', [AgenteCargaDashboardController::class, 'dashboard'])->name('agente_carga.dashboard');
        });
        // ------------- /.Rotas do Agente de Carga ------------ //
    });
    
