<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $empresa;

    public function __construct()
    {
        // Middleware global para todos os controladores
        $this->middleware(function ($request, $next) {
            $this->empresa = Auth::check() ? Auth::user()->empresas->first() : null;
            return $next($request);
        });
    }
}

class HomeController extends Controller
{
    //
}
