<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DocumentosAduaneiros extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'processo_id',
        'licenciamento_id',
        'TipoDocumento',
        'NrDocumento',
        'DataEmissao',
        'Caminho'
    ];

    // Evento para gerar o código automaticamente
    protected static function boot()
    {
        parent::boot();

        // Gera o próximo código sequencial para a empresa
        static::creating(function ($documentos) {
            $documentos->NrDocumento = self::generateNrDoc();
        });

        // Impedir a alteração de moeda se houver uma fatura emitida ou paga
        static::updating(function ($documentos) {
            
        });
    }

    public function processo()
    {
        return $this->hasMany(Processo::class);
    }

    public function licenciamento()
    {
        return $this->hasMany(licenciamento::class);
    }

    private function generateNrDoc(){
        // Gerar o NrDocumento automaticamente tendo em conta Cod_licenciamento, empresa_id da tabela licenciamento
        return '';
    }
}
