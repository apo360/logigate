<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivatedModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'module_id', 'activation_date'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
