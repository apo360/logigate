<?php

namespace App\Http\Controllers\WebPage;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class CustomerAuthController extends BaseController
{
    public function verifyNif(Request $request)
    {
        abort(403, 'O acesso ao portal do cliente por NIF está temporariamente bloqueado até ser migrado para autenticação segura.');
    }
}
