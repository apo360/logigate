<?php

namespace App\Http\Controllers;

use App\Application\Arquivo\Actions\UploadDocumentoAction;
use App\Application\Arquivo\DTOs\UploadDocumentoDTO;
use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Domains\Customers\Actions\CreateOrAssociateCustomerAction;
use App\Domains\Customers\Actions\DeleteCustomerAction;
use App\Domains\Customers\Actions\ToggleCustomerStatusAction;
use App\Domains\Customers\Actions\UpdateCustomerAssociationAction;
use App\Domains\Customers\Actions\UpdateCustomerProfileAction;
use App\Domains\Customers\Data\CustomerFormData;
use App\Domains\Customers\Services\CustomerAccountStatementService;
use App\Exports\CustomersExport;
use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\CustomerRequest;
use App\Imports\CustomersImport;
use App\Models\Customer;
use App\Models\Municipio;
use App\Models\Pais;
use App\Models\Processo;
use App\Models\Provincia;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Validators\ValidationException;


class CustomerController extends AuthenticatedController
{
    // constructor with auth middleware
    public function __construct()
    {
        parent::__construct(); // Chama o construtor pai para aplicar o middleware global
        $this->authorizeResource(Customer::class, 'customer');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = $this->empresa->customers()->get();
        return view('customer.customer_pesquisar', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provincias = Provincia::all();
        $paises = Pais::all();
        return view('customer.create', compact('provincias', 'paises'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        $formType = $request->get('formType'); 

        try {
            $empresa = Auth::user()->empresas->first();
            $customer = app(CreateOrAssociateCustomerAction::class)->execute(
                CustomerFormData::fromArray($request->validated() + $request->only([
                    'BuildingNumber',
                    'StreetName',
                    'AddressDetail',
                    'Province',
                    'City',
                    'PostalCode',
                    'Country',
                    'AddressType',
                    'codigo_cliente',
                    'status',
                ])),
                $empresa
            );

            if ($formType === 'modal') {
                return response()->json([
                    'message' => 'Cliente adicionado com sucesso!',
                    'cliente_id' => $customer->id,
                    'codCli' => $customer->CustomerTaxID,
                ], 200);
            }

            return redirect()->route('customers.edit', $customer->id)
                ->with('success', 'Cliente inserido com sucesso!');

        } catch (QueryException $e) {
            DB::rollBack();
            return DatabaseErrorHandler::handle($e, $request);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $processosMes = $customer->processos()
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $licenciamentosMes = $customer->licenciamento()
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        // Meses fixos (1–12)
        $meses = collect(range(1, 12));

        // Dataset
        $atividadeMes = [
            'processos' => $meses->map(fn ($m) => $processosMes[$m] ?? 0)->values(),
            'licenciamentos' => $meses->map(fn ($m) => $licenciamentosMes[$m] ?? 0)->values(),
        ];

        // Labels corretos
        $mapMeses = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
        ];

        $labels = $meses->map(fn ($m) => $mapMeses[$m])->values();

        return view(
            'customer.customer_show',
            compact('customer', 'atividadeMes', 'labels')
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $paises = Pais::all();
        $provincias = Provincia::all();
        $municipios = Municipio::all();
        $typeContacts = '';
        $paymentModes = '';
        $ivaExercises = '';
        return view('customer.customer_edit', compact('customer', 'paises', 'provincias', 'municipios', 'typeContacts', 'paymentModes', 'ivaExercises'));
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {

        try {
            $user = Auth::user();
            $empresa = $user->empresas->first();
            $dadosValidados = $request->validated();
            $escopo = $request->get('escopo', 'local'); // padrão: local

            if ($escopo === 'global') {
                if ($user->hasRole('admin') || $user->can('update-global-customer')) {
                    app(UpdateCustomerProfileAction::class)->execute(
                        $customer,
                        CustomerFormData::fromArray($dadosValidados + $request->only([
                            'BuildingNumber',
                            'StreetName',
                            'AddressDetail',
                            'Province',
                            'City',
                            'PostalCode',
                            'Country',
                            'AddressType',
                        ]))
                    );
                } else {
                    throw new \Exception('Sem permissão para atualização global.');
                }
            } else {
                app(UpdateCustomerAssociationAction::class)->execute($customer, $empresa, $dadosValidados);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $escopo === 'global'
                        ? 'Cliente atualizado globalmente com sucesso!'
                        : 'Cliente atualizado localmente com sucesso!',
                ]);
            }

            // Redirecionamento normal
            return redirect()
                ->route('customers.edit', $customer->id)
                ->with('success', $escopo === 'global'
                    ? 'Cliente atualizado globalmente com sucesso!'
                    : 'Cliente atualizado localmente com sucesso!');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withErrors('Erro: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Customer $customer)
    {
        try {
            app(DeleteCustomerAction::class)->execute($customer);

            return redirect()->route('customers.index')->with('success', 'Cliente removido com sucesso!');

        } catch (\InvalidArgumentException $e) {
            return redirect()->route('customers.index')->with('error', $e->getMessage());
        } catch (QueryException $e) {
            return DatabaseErrorHandler::handle($e, $request);
        }
    }

    public function index_conta(CustomerAccountStatementService $statementService)
    {
        $this->authorize('viewAny', Customer::class);

        $clientes = $this->tenantCustomerQuery()->get();
        $summary = $statementService->resumoPorClientes($clientes);

        return view('customer.index_conta_c', $summary);
    }

    public function conta($id, CustomerAccountStatementService $statementService){

        $cliente = $this->resolveAuthorizedCustomer($id, 'view');
        $transacoes = $statementService->movimentos((int) $cliente->id);
        $saldo = $statementService->saldo((int) $cliente->id);

        return view('customer.conta_c', compact('cliente', 'transacoes', 'saldo'));
    }

    public function CustomerImport(Request $request)
    {
        $this->authorize('create', Customer::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new CustomersImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'message' => 'Ficheiro importado com sucesso!',
            ], 200);

        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = "Linha {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return response()->json([
                'message' => 'Erro de validação durante a importação.',
                'errors' => $errors,
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Erro no import: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erro ao importar o ficheiro.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function export()
    {
        return Excel::download(new CustomersExport, 'customers.xlsx');
    }

    // Pegar processos por cliente.
    public function getProcessoByCustomer($CustomerId, $status){
        $customer = $this->resolveAuthorizedCustomer($CustomerId, 'view');

        $processo = Processo::where('empresa_id', $this->empresa->id)
            ->where('customer_id', $customer->id)
            ->where('Situacao', $status)
            ->get();
        
        return response()->json(['processo' => $processo]); 
    }


    public function obterUltimoClienteAdicionado()
    {
        $this->authorize('viewAny', Customer::class);

        $ultimoCliente = $this->tenantCustomerQuery()->latest('customers.created_at')->firstOrFail();
        
        return response()->json(['cliente_id' => $ultimoCliente->id]);
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            $customer = $this->resolveAuthorizedCustomer($id, 'update');
            app(ToggleCustomerStatusAction::class)->execute($customer, (bool) $request->is_active);

            return response()->json(['success' => true, 'message' => 'Status atualizado com sucesso.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Cliente não encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar o status.'], 500);
        }
    }

    public function ImprimirFicha($id){

        return '';
    }

    /**
     * Documentos Store do Cliente para o S3
     */
    public function documentosStore(Request $request, $id, UploadDocumentoAction $action)
    {
        // Validação do arquivo
        $request->validate([
            'documento' => 'required|file|max:10240',
            'categoria' => 'nullable|string',
        ]);

        $customer = $this->resolveAuthorizedCustomer($id, 'view');

        $documento = $action->execute(new UploadDocumentoDTO(
            file: $request->file('documento'),
            contexto: DocumentoContextoEnum::CUSTOMER,
            categoria: DocumentoCategoriaEnum::tryFrom((string) $request->input('categoria')) ?? DocumentoCategoriaEnum::DOCUMENTOS,
            entidadeId: (int) $customer->id,
            uploadedBy: (int) Auth::id(),
        ));

        return response()->json(['message' => 'Documento armazenado com sucesso!', 'documento_id' => $documento->id]);
    }

    private function resolveAuthorizedCustomer(mixed $customer, string $ability): Customer
    {
        if (!$customer instanceof Customer) {
            $customer = $this->tenantCustomerQuery()
                ->whereKey($customer)
                ->firstOrFail();
        }

        $this->authorize($ability, $customer);

        return $customer;
    }

    private function tenantCustomerQuery()
    {
        $query = Customer::query();

        if (!Schema::hasColumn('customers', 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query->where(function ($tenantQuery): void {
            $tenantQuery->where('empresa_id', $this->empresa->id);

            if (Schema::hasTable('customers_empresas')) {
                $tenantQuery->orWhereHas('empresas', fn ($empresaQuery) => $empresaQuery->where('empresas.id', $this->empresa->id));
            }
        });
    }
}
