<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'parent_id', 'module_name', 'description', 'price'
    ];

    public function parent()
    {
        return $this->belongsTo(Module::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Module::class, 'parent_id');
    }

    public function menus()
    {
        return $this->hasMany(Menu::class, 'module_id', 'id');
    }

    public function subscricoes()
    {
        return $this->hasMany(Subscricao::class, 'modulo_id', 'id');
    }

    public function activatedModules()
    {
        return $this->hasMany(ActivatedModule::class);
    }

    public function planos()
    {
        return $this->belongsToMany(Plano::class, 'plano_modulos', 'modulo_id', 'plano_id');
    }

}
