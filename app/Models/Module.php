<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';
    
    protected $fillable = [
        'module_name',
        'description',
        'price',
        'codigo',
        'tipo',
        'icone',
        'parent_id',
        'order_priority',
        'ativo'
    ];

    protected $casts = [
        'price' => 'float',
        'order_priority' => 'integer',
        'ativo' => 'boolean'
    ];

    // Para compatibilidade
    public function getNomeAttribute()
    {
        return $this->module_name;
    }

    public function parent()
    {
        return $this->belongsTo(Module::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Module::class, 'parent_id')->orderBy('order_priority');
    }

    public function planos()
    {
        return $this->belongsToMany(Plano::class, 'plano_modulo', 'module_id', 'plano_id')
                    ->withPivot(['incluido', 'limite', 'preco_adicional']);
    }

    public function activatedForEmpresa($empresaId)
    {
        return $this->hasOne(ActivatedModule::class, 'module_id')
                    ->where('empresa_id', $empresaId)
                    ->where('active', true);
    }

    public function isAddon()
    {
        return $this->tipo === 'addon';
    }
}