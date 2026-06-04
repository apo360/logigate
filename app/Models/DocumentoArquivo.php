<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class DocumentoArquivo extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'documentos_arquivos';

    protected $fillable = [
        'uuid',
        'empresa_id',
        'customer_id',
        'processo_id',
        'licenciamento_id',
        'documentable_type',
        'documentable_id',
        'contexto',
        'categoria',
        'visibilidade',
        'storage_disk',
        'bucket',
        'storage_key',
        'nome_original',
        'mime_type',
        'extension',
        'size_bytes',
        'sha256_hash',
        'uploaded_by',
        'deleted_by',
        'retention_until',
    ];

    protected $casts = [
        'retention_until' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function licenciamento(): BelongsTo
    {
        return $this->belongsTo(Licenciamento::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
