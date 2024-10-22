<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPagamento extends Model
{
    use HasFactory;

    protected $fillable = ['Code', 'Descriptions'];

    protected $table = "PaymentMechanism";

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }
}
