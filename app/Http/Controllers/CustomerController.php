<?php

namespace App\Http\Controllers;

use App\Application\Arquivo\Actions\UploadDocumentoAction;
use App\Application\Arquivo\DTOs\UploadDocumentoDTO;
use App\Application\Customer\Queries\BuscarCustomerQuery;
use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Domains\Customers\Actions\DeleteCustomerAction;
use App\Domains\Customers\Actions\ToggleCustomerStatusAction;
use App\Domains\Customers\Services\CustomerAccountStatementService;
use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use App\Models\Customer;
use App\Models\Processo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
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
        $this->authorize('viewAny', Customer::class);
        $customers = $this->empresa->customers()->get();
        return view('customer.customer_pesquisar', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('customer.create');
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

    public function edit(Customer $customer, BuscarCustomerQuery $query): View
    {
        $model = $query->execute($customer->id);

        $this->authorize('update', $model);

        return view('customer.customer_edit', [
            'customer' => $model,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $customer, DeleteCustomerAction $action): RedirectResponse
    {
        try {
            $model = Customer::query()->findOrFail($customer);

            $this->authorize('delete', $model);

            $action->execute($customer);

            return redirect()->route('customers.index')->with('success', 'Cliente eliminado com sucesso.');
        } catch (\Throwable $e) {

            return back()->with('error', $e->getMessage());
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
