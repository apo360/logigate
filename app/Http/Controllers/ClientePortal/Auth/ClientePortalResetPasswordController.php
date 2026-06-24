<?php

namespace App\Http\Controllers\ClientePortal\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientePortalResetPasswordController extends Controller
{
    public function show(): View
    {
        return view('WebSite.ClienteAppPage.portal_login')
            ->with('status', 'Para redefinir a senha do Portal Cliente, contacte o seu despachante.');
    }

    public function store(Request $request): RedirectResponse
    {
        return back()
            ->withInput($request->only('login'))
            ->with('status', 'A redefinição self-service ainda não está disponível. Contacte o seu despachante.');
    }
}
