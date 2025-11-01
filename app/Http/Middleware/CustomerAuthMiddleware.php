<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('customer_logged_in') || !$request->session()->get('customer_logged_in')) {
            return redirect()->route('customer.verify-nif')->with('error', 'Acesso restrito. Insira o NIF corretamente.');
        }
        return $next($request);
    }
}
