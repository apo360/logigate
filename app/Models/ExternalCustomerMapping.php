<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalCustomerMapping extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const PROVIDER_HONGAYETU_FACTURACAO = 'hongayetu_facturacao';

    protected $fillable = [
        'empresa_id',
        'empresa_integracao_id',
        'customer_id',
        'provider',
        'external_customer_id',
        'external_customer_name',
        'external_customer_nif',
        'external_payload',
        'request_payload',
        'response_payload',
        'synced_at',
        'last_checked_at',
        'last_error',
    ];

    protected $casts = [
        'external_payload' => 'array',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'synced_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function empresaIntegracao(): BelongsTo
    {
        return $this->belongsTo(EmpresaIntegracao::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function externalInvoices(): HasMany
    {
        return $this->hasMany(ExternalInvoice::class);
    }
}
