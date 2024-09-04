<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceType extends Model
{
    use HasFactory;

    protected $table = 'InvoiceType';
    
    protected $fillable = [
        'Code',
        'Descriptions',
    ];

    public function getID($type)
    {
        $invoiceType = self::where('Code', $type)->first();

        if ($invoiceType) {
            return $invoiceType->Id;
        }

        // Retorna null (ou outra coisa que faça sentido no seu contexto)
        return null;
    }
}
