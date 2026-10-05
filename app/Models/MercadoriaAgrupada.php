<?php

namespace App\Models;

use App\Application\Mercadoria\Services\MercadoriaAgrupamentoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MercadoriaAgrupada extends Model
{
    use HasFactory;
    protected $fillable = ['codigo_aduaneiro', 'licenciamento_id', 'processo_id', 'quantidade_total', 'peso_total', 'preco_total', 'mercadorias_ids'];

    public function pautaAduaneira()
    {
        return $this->belongsTo(PautaAduaneira::class, 'codigo_aduaneiro', 'codigo');
    }

    public function mercadoriasQuery()
    {
        return Mercadoria::query()->whereIn('id', json_decode($this->mercadorias_ids ?: '[]', true, 512, JSON_THROW_ON_ERROR))
            ->when($this->processo_id, fn ($query) => $query->where('Fk_Importacao', $this->processo_id))
            ->when($this->licenciamento_id, fn ($query) => $query->where('licenciamento_id', $this->licenciamento_id));
    }

    public function mercadoriasLicenciamento()
    {
        return $this->hasMany(Mercadoria::class, 'codigo_aduaneiro', 'codigo_aduaneiro')->where('licenciamento_id', $this->licenciamento_id);
    }

    public function mercadoriasProcesso()
    {
        return $this->hasMany(Mercadoria::class, 'codigo_aduaneiro', 'codigo_aduaneiro')->where('Fk_Importacao', $this->processo_id);
    }

    public static function storeAndUpdateAgrupamento(Mercadoria $mercadoria): void
    {
        app(MercadoriaAgrupamentoService::class)->addOrUpdate($mercadoria);
    }

    public static function removeFromAgrupamento(Mercadoria $mercadoria): void
    {
        app(MercadoriaAgrupamentoService::class)->remove($mercadoria);
    }
}
