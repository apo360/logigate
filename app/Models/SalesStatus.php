<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesStatus extends Model
{
    use HasFactory;

    protected $table = 'sales_document_status';

    public $timestamps = ['created_at', 'updated_at'];

    protected $fillable = [
        'invoice_status',
        'invoice_status_date',
        'invoice_available_date',
        'source_id',
        'source_billing',
        'situacao',
        'documentoID',
    ];

    /**
     * Relacionamento com SalesInvoice
     */
    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'documentoID', 'id');
    }

}
