<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalApiLog extends Model
{
    use HasFactory;

    public const PROVIDER_HONGAYETU_FACTURACAO = 'hongayetu_facturacao';

    protected $fillable = [
        'empresa_id',
        'empresa_integracao_id',
        'external_invoice_id',
        'provider',
        'endpoint',
        'method',
        'request_payload',
        'response_payload',
        'status_code',
        'success',
        'error_code',
        'error_message',
        'duration_ms',
        'user_id',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'success' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function empresaIntegracao(): BelongsTo
    {
        return $this->belongsTo(EmpresaIntegracao::class);
    }

    public function externalInvoice(): BelongsTo
    {
        return $this->belongsTo(ExternalInvoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
