<?php

namespace App\Http\Controllers\Transitario;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return view('transitario.dashboard');
    }
}
