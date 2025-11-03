<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\CustomerRequest;
use App\Imports\CustomersImport;
use App\Models\ContaCorrente;
use App\Models\Customer;
use App\Models\Endereco;
use App\Models\Municipio;
use App\Models\Pais;
use App\Models\Processo;
use App\Models\Provincia;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Validators\ValidationException;


class CustomerController extends Controller
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
            DB::beginTransaction();

            $custValidate = $request->validated();

            // Verificar se o cliente já existe
            $existingCustomer = Customer::where('CustomerTaxID', $custValidate['CustomerTaxID'])->first();

            if (!$existingCustomer) {
                // Cria novo cliente
                $customer = Customer::create($custValidate);
            } else {
                $customer = $existingCustomer;
            }

            // Verificar se já está associado à empresa
            $empresa = Auth::user()->empresas->first();
            $jaAssociado = $customer->empresas()->where('empresa_id', $empresa->id)->exists();

            if (!$jaAssociado) {
                DB::table('customers_empresas')->insert([
                    'customer_id' => $customer->id,
                    'empresa_id' => $empresa->id,
                    'codigo_cliente' => null,
                    'status' => 'ativo',
                    'data_associacao' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            // ✅ Resposta para formulários do tipo "modal"
            if ($formType === 'modal') {
                return response()->json([
                    'message' => 'Cliente adicionado com sucesso!',
                    'cliente_id' => $customer->id,
                    'codCli' => $customer->CustomerTaxID,
                ], 200);
            }

            // ✅ Criação de endereço (apenas quando não é modal)
            $enderecoData = $request->only([
                'BuildingNumber',
                'StreetName',
                'AddressDetail',
                'Province',
                'City',
                'PostalCode',
                'Country',
            ]);

            $enderecoData['AddressType'] = 'Facturamento';
            $enderecoData['customer_id'] = $customer->id;

            $enderecoController = new EnderecoController();
            $enderecoController->store(new Request($enderecoData));

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
        $customer = Customer::findOrFail($customer->id);
        return view('customer.customer_show', compact('customer'));
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
            DB::beginTransaction();

            $user = Auth::user();
            $empresa = $user->empresas->first();
            $dadosValidados = $request->validated();
            $escopo = $request->get('escopo', 'local'); // padrão: local

            // Verifica se o cliente está associado à empresa
            $associacao = $customer->empresas()->where('empresa_id', $empresa->id)->first();

            if ($escopo === 'global') {
                // Atualização global (dados centrais do cliente)
                if ($user->hasRole('admin') || $user->can('update-global-customer')) {
                    $customer->update($dadosValidados);
                } else {
                    throw new \Exception('Sem permissão para atualização global.');
                }
            } else {
                // Atualização local (dados da associação)
                if ($associacao) {
                    $customer->empresas()->updateExistingPivot($empresa->id, [
                        'codigo_cliente'   => $dadosValidados['codigo_cliente'] ?? $associacao->pivot->codigo_cliente,
                        'additional_info'  => $dadosValidados['additional_info'] ?? $associacao->pivot->additional_info,
                        'status'           => $dadosValidados['status'] ?? $associacao->pivot->status,
                        'data_associacao'  => $associacao->pivot->data_associacao ?? now(),
                    ]);
                } else {
                    // Cria associação se não existir
                    $customer->empresas()->attach($empresa->id, [
                        'codigo_cliente'   => $dadosValidados['codigo_cliente'] ?? null,
                        'additional_info'  => $dadosValidados['additional_info'] ?? null,
                        'status'           => $dadosValidados['status'] ?? 'ATIVO',
                        'data_associacao'  => now(),
                    ]);
                }
            }

            // Atualiza ou cria endereço (somente se for global)
            if ($escopo === 'global') {
                $endereco = $customer->endereco ?? Endereco::create(['customer_id' => $customer->id]);
                $enderecoController = new EnderecoController();

                $enderecoRequest = new Request($request->only([
                    'BuildingNumber',
                    'StreetName',
                    'AddressDetail',
                    'Province',
                    'City',
                    'PostalCode',
                    'Country',
                ]));

                $enderecoController->update($enderecoRequest, $endereco);
            }

            DB::commit();

            // Se for AJAX
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
            DB::rollBack();

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
            DB::beginTransaction();

            // Check if the customer has any related  processes
            if ($customer->processos()->exists()) {
                // If the customer has related processes, don't delete and alert the administrator.
                return redirect()->route('customers.index')->with('error', 'O cliente possui processos relacionados e não pode ser removido!');
            }

            // Check if the customer has any related invoices
            if ($customer->invoices()->exists()) {
                // If the customer has related invoices, don't delete and alert the administrator.
                return redirect()->route('customers.index')->with('error', 'O cliente possui faturas relacionados e não pode ser removido!');
            }

            // If the customer does not have any related invoices or processes, proceed with deletion
            $customer->delete();

            DB::commit();

            // Redirect to the customer listing page
            return redirect()->route('customers.index')->with('success', 'Cliente removido com sucesso!');

        } catch (QueryException $e) {
            DB::rollBack();

            return DatabaseErrorHandler::handle($e, $request);
        }
    }

    public function index_conta()
    {
        // Listar todos os clientes
        $clientes = Customer::all();
        $resultados = [];

        // Inicializar as variaveis (Totais gerais)
        $totalSaldo = 0;
        $totalDividaCorrente = 0;
        $totalDividaVencida = 0;

        foreach ($clientes as $cliente) {
            // Obter todas as transações da conta corrente do cliente
            $transacoes = ContaCorrente::where('cliente_id', $cliente->id)->orderBy('data', 'desc')->get();

            // Calcular o saldo
            $saldo = $transacoes->sum(function ($transacao) {
                return $transacao->tipo === 'credito' ? $transacao->valor : -$transacao->valor;
            });

            // Inicializar variaveis de dívidas
            $dividaCorrente = 0;
            $dividaVencida = 0;

            // Obter as faturas associadas ao cliente, se houver
            $facturas = SalesInvoice::where('customer_id', $cliente->id)->get();

            if ($facturas->isNotEmpty()) {
                foreach ($facturas as $invoice) {
                    $grossTotal = $invoice->salesdoctotal->gross_total ?? 0;

                    if ($invoice->invoice_date_end >= Carbon::now()) {
                        $dividaCorrente += $grossTotal;
                    } else {
                        $dividaVencida += $grossTotal;
                    }
                }
            }

            // Somente armazena resultados para clientes com valores diferentes de zero
            if ($saldo != 0 || $dividaCorrente != 0 || $dividaVencida != 0) {
                $resultados[] = [
                    'cliente' => $cliente,
                    'saldo' => $saldo,
                    'dividaCorrente' => $dividaCorrente,
                    'dividaVencida' => $dividaVencida,
                ];
            }

            // Atualizar totais gerais
            $totalSaldo += $saldo;
            $totalDividaCorrente += $dividaCorrente;
            $totalDividaVencida += $dividaVencida;
        }

        return view('customer.index_conta_c', compact('resultados', 'totalSaldo', 'totalDividaCorrente', 'totalDividaVencida'));
    }

    public function conta($id){

        $cliente = Customer::findOrFail($id);
        
        $transacoes = ContaCorrente::where('cliente_id', $id)->orderBy('data', 'desc')->get();

        // Calcular o saldo baseado nas transações
        $saldo = $transacoes->sum(function ($transacao) {
            return $transacao->tipo === 'credito' ? $transacao->valor : -$transacao->valor;
        });

        return view('customer.conta_c', compact('cliente', 'transacoes', 'saldo'));
    }

    public function CustomerImport(Request $request)
    {
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
        $processo = Processo::where('customer_id', $CustomerId)->where('Situacao', $status)->get();
        
        return response()->json(['processo' => $processo]); 
    }


    public function obterUltimoClienteAdicionado()
    {
        // Lógica para obter o ID do último cliente adicionado
        $ultimoCliente = Customer::latest()->first();
        
        return response()->json(['cliente_id' => $ultimoCliente->id]);
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);

            $customer->is_active = $request->is_active;
            $customer->save();

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
    public function documentosStore(Request $request, $id)
    {
        // Validação do arquivo
        $request->validate([
            'documento' => 'required|file|max:5120', // Máximo 5MB
        ]);

        $customer = Customer::findOrFail($id);

        // Armazenar o arquivo no S3
        $path = $request->file('documento')->store('customer_documents/' . $customer->id, 's3');

        // Salvar o caminho do arquivo no banco de dados (se necessário)
        // Exemplo: $customer->document_path = $path; $customer->save();

        return response()->json(['message' => 'Documento armazenado com sucesso!', 'path' => $path]);
    }
}
