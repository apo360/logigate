<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalInvoiceLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_invoice_id',
        'product_id',
        'service_id',
        'external_artigo_id',
        'type',
        'description',
        'quantity',
        'unit_price',
        'tax_percentage',
        'discount_amount',
        'line_net_total',
        'line_tax_total',
        'line_total',
        'payload',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'tax_percentage' => 'decimal:4',
        'discount_amount' => 'decimal:2',
        'line_net_total' => 'decimal:2',
        'line_tax_total' => 'decimal:2',
        'line_total' => 'decimal:2',
        'payload' => 'array',
    ];

    public function externalInvoice(): BelongsTo
    {
        return $this->belongsTo(ExternalInvoice::class);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'product_id');
    }

    public function product(): BelongsTo
    {
        return $this->produto();
    }
}
