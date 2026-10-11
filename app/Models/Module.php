<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $table = 'modules';
    
    protected $fillable = [
        'module_name',
        'description',
        'price',
        'parent_id',
    ];

    protected $casts = [
        'price' => 'float',
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
        return $this->hasMany(Module::class, 'parent_id')->orderBy('id');
    }

    //Inverso de Menu::module()
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'module_id')
                    ->orderBy('order_priority');
    }

    public function planos()
    {
        return $this->belongsToMany(Plano::class, 'plano_modulos', 'modulo_id', 'plano_id')->withTimestamps();
    }

    public function activatedForEmpresa($empresaId)
    {
        return $this->hasOne(ActivatedModule::class, 'module_id')
                ->where('empresa_id', $empresaId)
                ->whereIn('module_id', app(\App\Services\EffectiveModuleResolver::class)->ids((int) $empresaId))
                ->where('active', true);
    }

    public function isAddon()
    {
        // The catalogue has no addon type; extra rights are explicitly provisioned.
        return false;
    }
}
