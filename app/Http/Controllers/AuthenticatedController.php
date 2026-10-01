<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;

abstract class AuthenticatedController extends BaseController
{
    protected ?Empresa $empresa = null;

    public function __construct()
    {
        $this->middleware('auth');

        // Centralize tenant resolution for authenticated SaaS controllers.
        $this->middleware(function ($request, $next) {
            $this->empresa = \App\Support\TenantContext::empresa();

            return $next($request);
        });
    }
}
