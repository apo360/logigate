<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagamentoOnline extends Model
{
    protected $table = 'pagamento_onlines';

    protected $fillable = [
        'method',
        'merchant_transaction_id',
        'gateway_id', //novo
        'amount',
        'status', //['pendente', 'processando', 'concluido', 'falhado']
        'raw_response',
        'subscription_id', //novo
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscricao::class);
    }

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pendente';
    public const STATUS_PROCESSING = 'processando';
    public const STATUS_COMPLETED = 'concluido';
    public const STATUS_FAILED = 'falhado';

    /**
     * Payment method constants
     */
    public const METHOD_GPO = 'GPO';
    public const METHOD_REF = 'REF';
    // Future methods can be added here (UnitelMoney, DebitoDireto, etc.)
}
