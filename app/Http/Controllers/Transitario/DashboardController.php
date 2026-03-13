<?php

namespace App\Http\Controllers\Transitario;

use App\Http\Controllers\AuthenticatedController;
use Illuminate\Http\Request;

class DashboardController extends AuthenticatedController
{
    public function dashboard()
    {
        return view('transitario.dashboard');
    }
}
