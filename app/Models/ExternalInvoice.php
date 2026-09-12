<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalInvoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const PROVIDER_HONGAYETU_FACTURACAO = 'hongayetu_facturacao';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const DOC_TYPE_FR = 'FR';
    public const DOC_TYPE_FT = 'FT';
    public const DOC_TYPE_FP = 'FP';
    public const DOC_TYPE_NC = 'NC';

    public const API_TIPO_FR = 0;
    public const API_TIPO_FT = 1;
    public const API_TIPO_NC = 2;
    public const API_TIPO_FP = 3;

    protected $fillable = [
        'empresa_id',
        'empresa_integracao_id',
        'external_customer_mapping_id',
        'customer_id',
        'contract_id',
        'avenca_id',
        'processo_id',
        'provider',
        'external_customer_id',
        'external_invoice_id',
        'external_invoice_number',
        'local_reference',
        'document_type',
        'api_tipo',
        'api_estado',
        'status',
        'issue_date',
        'due_date',
        'currency',
        'moeda',
        'cambio',
        'subtotal',
        'tax_total',
        'discount_total',
        'gross_total',
        'paid_total',
        'balance_due',
        'request_payload',
        'response_payload',
        'pdf_disk',
        'pdf_path',
        'pdf_filename',
        'pdf_mime',
        'pdf_size',
        'pdf_stored_at',
        'last_error',
        'synced_at',
        'created_by',
        'issued_by',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'pdf_stored_at' => 'datetime',
        'synced_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'deleted_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'gross_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'cambio' => 'decimal:6',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function empresaIntegracao(): BelongsTo
    {
        return $this->belongsTo(EmpresaIntegracao::class);
    }

    public function externalCustomerMapping(): BelongsTo
    {
        return $this->belongsTo(ExternalCustomerMapping::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ExternalInvoiceLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function avenca(): BelongsTo
    {
        return $this->belongsTo(CustomerAvenca::class, 'avenca_id');
    }

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }

    public function apiLogs(): HasMany
    {
        return $this->hasMany(ExternalApiLog::class);
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function hasPdfStored(): bool
    {
        return filled($this->pdf_disk) && filled($this->pdf_path);
    }

    public function markAsFailed(string $message): void
    {
        $this->forceFill([
            'status' => self::STATUS_FAILED,
            'last_error' => mb_substr($message, 0, 1000),
        ])->save();
    }

    public function markAsIssued(?int $externalId, ?string $number, array $response = []): void
    {
        $this->forceFill([
            'status' => self::STATUS_ISSUED,
            'external_invoice_id' => $externalId,
            'external_invoice_number' => $number,
            'response_payload' => $response,
            'last_error' => null,
            'synced_at' => now(),
        ])->save();
    }
}
