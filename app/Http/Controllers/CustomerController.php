<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\CustomerRequest;
use App\Imports\CustomersImport;
use App\Models\Customer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        // Determine the form type based on the request data
        $formType = $request->get('formType'); 

        try {
            // Inicia uma transação para garantir a integridade dos dados
            DB::beginTransaction();

            $user = Auth::user();

            $custValidate = $request->validated();
            
            $custValidate['AccountID'] = '0';

            $custValidate['user_id'] = $user->id;

            $custValidate['empresa_id'] = $user->empresas->first()->id;
            // Cria um novo registro de cliente na tabela 'customers' com os dados fornecidos
            $newCustomer = Customer::create($custValidate);

            // Confirma a transação, salvando as alterações no banco de dados
            DB::commit();

            // Prepare success response based on form type
            if ($formType === 'modal') {
                // Return success data for modal forms
                return response()->json([
                    'message' => 'Cliente adicionado com Sucesso',
                    'cliente_id' => $newCustomer->id,
                    'codCli' => $newCustomer->CustomerTaxID,
                ], 200);
            } else {
                // Redirect to 'form.edit' for the main form
                return redirect()->route('customers.edit', $newCustomer->id)->with('success', 'Cliente Inserido com sucesso');
            }
            
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {
        
        try {
            DB::beginTransaction();
            
            // Atualize os atributos do cliente com base nos dados recebidos no $request
            Customer::where('CustomerID',$customer->CustomerID)->update($request->validated());

            DB::commit();

            // Redirecione para a página de listagem de clientes
            return redirect()->route('customers.index')->with('success', 'Cliente atualizado com sucesso!');;
        } catch (QueryException $e) {
            DB::rollBack();

            return DatabaseErrorHandler::handle($e, $request);
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

    public function import(Request $request)
    {
        $file = $request->file('file');
        Excel::import(new CustomersImport, $file);

        return back()->with('success', 'Customers Imported Successfully');
    }

    public function export()
    {
        return Excel::download(new CustomersExport, 'customers.xlsx');
    }
}
