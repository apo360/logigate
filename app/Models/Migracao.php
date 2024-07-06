<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Migracao extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'file_path', 'status', 'empresa_id'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
