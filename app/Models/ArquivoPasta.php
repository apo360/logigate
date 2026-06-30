<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArquivoPasta extends Model
{
    use SoftDeletes;

    protected $table = 'arquivo_pastas';

    protected $fillable = [
        'uuid',
        'empresa_id',
        'parent_id',
        'name',
        'slug',
        'path',
        'type',
        'is_system',
        'is_locked',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_locked' => 'boolean',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoArquivo::class, 'folder_id');
    }
}
