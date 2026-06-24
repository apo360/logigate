<?php

namespace App\Http\Controllers\ClientePortal\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClientePortalLoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::guard('cliente_portal')->check()) {
            return redirect()->route('cliente.portal.dashboard');
        }

        return view('WebSite.ClienteAppPage.portal_login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['login']);

        foreach (['username', 'email', 'phone'] as $field) {
            if (Auth::guard('cliente_portal')->attempt([
                $field => $login,
                'password' => $credentials['password'],
                'is_active' => true,
            ], $request->boolean('remember'))) {
                $request->session()->regenerate();

                Auth::guard('cliente_portal')->user()?->forceFill([
                    'last_login_at' => now(),
                ])->save();

                return redirect()->intended(route('cliente.portal.dashboard'));
            }
        }

        throw ValidationException::withMessages([
            'login' => 'As credenciais informadas não correspondem aos nossos registos.',
        ]);
    }
}
