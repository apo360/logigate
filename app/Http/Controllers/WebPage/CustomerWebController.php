<?php

namespace App\Http\Controllers\WebPage;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class CustomerWebController extends BaseController
{
    public function index()
    {
        return view('WebSite.ClienteAppPage.index');
    }

    public function profile()
    {
        return view('WebSite.ClienteAppPage.profile');
    }

    // Logout do cliente
    public function logout(Request $request)
    {
        // Invalida a sessão do cliente
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('welcome');
        //return redirect()->route('login');
    }
}
