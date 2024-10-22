<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenciamentoRascunho extends Model
{
    use HasFactory;

    protected $table = "licenciamento_rascunho";

    // Definindo os campos que podem ser atribuídos em massa
    protected $fillable = [
        'estancia_id',
        'customer_id',
        'exportador_id',
        'empresa_id',
        'referencia_cliente',
        'factura_proforma',
        'descricao',
        'moeda',
        'tipo_declaracao',
        'tipo_transporte',
        'registo_transporte',
        'nacionalidade_transporte',
        'manifesto',
        'data_entrada',
        'porto_entrada',
        'peso_bruto',
        'adicoes',
        'metodo_avaliacao',
        'codigo_volume',
        'qntd_volume',
        'forma_pagamento',
        'codigo_banco',
        'fob_total',
        'frete',
        'seguro',
        'cif',
        'pais_origem',
        'porto_origem',
        'Nr_factura',
        'user_id',
    ];

    // Definindo casts para transformar automaticamente os tipos de dados
    protected $casts = [
        'data_entrada' => 'date',
        'peso_bruto' => 'decimal:2',
        'fob_total' => 'decimal:2',
        'frete' => 'decimal:2',
        'seguro' => 'decimal:2',
        'cif' => 'decimal:2',
    ];
}
