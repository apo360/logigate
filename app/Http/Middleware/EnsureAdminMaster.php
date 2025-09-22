<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminMaster
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated as an admin master
        if (!session(config('admin.session_key'))) {
            // Redirect to login or show an error
            return redirect()->route('login')->with('error', 'Acesso negado. Você não é um Administrador Master.');
        }

        return $next($request);
    }
}
