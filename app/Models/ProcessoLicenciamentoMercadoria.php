<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessoLicenciamentoMercadoria extends Model
{
    use HasFactory;

    protected $table = 'processo_licenciamento_mercadoria'; // Nome da tabela
    
    protected $fillable = [
        'processo_id',
        'licenciamento_id',
        'mercadoria_id',
        'quantidade',
    ];

    // Relacionamento com o Model Processo
    public function processo()
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }

    // Relacionamento com o Model Licenciamento
    public function licenciamento()
    {
        return $this->belongsTo(Licenciamento::class, 'licenciamento_id');
    }

    // Relacionamento com o Model Mercadoria
    public function mercadoria()
    {
        return $this->belongsTo(Mercadoria::class, 'mercadoria_id');
    }
}
