<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMasterMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionKey = config('admin.session_key', 'is_admin_master');

        // Only accept the dedicated session flag that is set after the hashed
        // secret is verified by AdminAuthController.
        if (! $request->session()->get($sessionKey, false)) {
            return redirect()->route('verify-pin')->with('error', 'Acesso restrito. Insira o PIN de administrador.');
        }

        return $next($request);
    }
}
