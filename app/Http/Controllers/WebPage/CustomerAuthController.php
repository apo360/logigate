<?php

namespace App\Http\Controllers\WebPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerAuthController extends Controller
{
    public function verifyNif(Request $request)
    {
        $request->validate([
            'nif' => 'required|string|size:12',
        ]);

        // Verificar a existencia do nif no banco de dados
        $customer = Customer::where('CustomerTaxID', $request->nif)->first();

        if ($customer) {
            session(['customer_logged_in' => true]);
             return response()->json([
                'success' => true,
                'redirect_url' => route('customer.web.dashboard'),
            ]);
        }

        return back()->withErrors(['nif' => 'NIF inválido.']);
    }
}
