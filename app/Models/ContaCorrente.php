<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaCorrente extends Model
{
    use HasFactory;

    protected $table = 'conta_correntes';

    protected $fillable = ['cliente_id', 'valor', 'tipo', 'descricao', 'data', 'saldo_contablistico'];

    public function cliente()
    {
        return $this->belongsTo(Customer::class);
    }
    

    public function getSaldoAttribute()
    {
        // Calcula o saldo acumulado de todas as transações do cliente
        $creditos = $this->where('cliente_id', $this->cliente_id)->where('tipo', 'credito')->sum('valor');
        $debitos = $this->where('cliente_id', $this->cliente_id)->where('tipo', 'debito')->sum('valor');

        return $creditos - $debitos;
    }
}
