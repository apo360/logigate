<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Contentor extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use BelongsToTenant;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'contentores';

    protected $fillable = [
        'empresa_id',
        'processo_id',
        'asycuda_id',
        'numero',
        'tipo',
        'descricao',
        'indicador_carga',
        'peso_tara',
        'peso_bruto',
        'volume_bruto',
        'unidade_volume_bruto',
        'numero_volumes',
        'descarregado',
        'possui_selo',
        'resselado',
        'asycuda_declaration_item_id',
    ];

    protected $casts = [
        'peso_tara' => 'decimal:2',
        'peso_bruto' => 'decimal:2',
        'volume_bruto' => 'decimal:3',
        'numero_volumes' => 'integer',
        'descarregado' => 'boolean',
        'possui_selo' => 'boolean',
        'resselado' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function mercadorias(): BelongsToMany
    {
        return $this->belongsToMany(Mercadoria::class, 'contentor_mercadoria', 'contentor_id', 'mercadoria_id')
            ->withPivot(['asycuda_item_id', 'asycuda_link_id', 'codigo_item'])
            ->withTimestamps();
    }
}
