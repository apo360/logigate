<?php

namespace App\Http\Controllers;

use App\Models\Porto;
use Illuminate\Http\Request;

class PortoController extends Controller
{
    public function getPortos($paisId)
    {
        $portos = Porto::where('pais_id', $paisId)->get(); // Altere os campos conforme necessário
        return response()->json($portos);
    }
}
