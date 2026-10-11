<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivatedModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'subscricao_id', 'module_id', 'activation_date', 'data_expiracao', 'menu_id', 'active'
    ];

    protected $casts = ['active' => 'boolean', 'activation_date' => 'datetime', 'data_expiracao' => 'datetime'];

    public function subscricao()
    {
        return $this->belongsTo(Subscricao::class, 'subscricao_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
