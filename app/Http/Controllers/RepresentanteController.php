<?php

namespace App\Http\Controllers;

use App\Http\Requests\RepresentanteRequest;
use App\Models\Representante;
use Illuminate\Http\Request;

class RepresentanteController extends Controller
{
     public function store(RepresentanteRequest $request)
    {
        $representante = Representante::create($request->validated());

        return response()->json([
            'message' => 'Representante criado com sucesso!',
            'data' => $representante,
        ], 201);
    }
}
