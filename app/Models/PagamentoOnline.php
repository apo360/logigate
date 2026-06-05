<?php

namespace App\Models;

use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class PagamentoOnline extends Model
{
    protected $table = 'pagamento_onlines';

    protected $fillable = [
        'empresa_id',
        'method',
        'merchant_transaction_id',
        'gateway_id',
        'amount',
        'currency',
        'status',
        'reference_entity',
        'reference_number',
        'reference_due_date',
        'phone',
        'failure_reason',
        'raw_response',
        'subscription_id',
        'paid_at',
        'failed_at',
        'idempotency_key',
    ];

    protected $casts = [
        'amount' => 'float',
        'raw_response' => 'array',
        'reference_due_date' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscricao::class);
    }

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    /**
     * Payment method constants
     */
    public const METHOD_GPO = 'GPO';
    public const METHOD_REF = 'REF';

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function methodEnum(): PaymentMethod
    {
        return PaymentMethod::fromInput($this->method);
    }

    public function statusEnum(): PaymentStatus
    {
        return PaymentStatus::fromPersisted($this->status);
    }
}
