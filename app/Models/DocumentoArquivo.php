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
        'folder_id',
        'contexto',
        'categoria',
        'visibilidade',
        'status',
        'storage_disk',
        'bucket',
        'storage_key',
        'stored_name',
        'nome_original',
        'mime_type',
        'extension',
        'size_bytes',
        'sha256_hash',
        'metadata',
        'is_confidential',
        'uploaded_by',
        'deleted_by',
        'retention_until',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_confidential' => 'boolean',
        'retention_until' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pasta(): BelongsTo
    {
        return $this->belongsTo(ArquivoPasta::class, 'folder_id');
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
