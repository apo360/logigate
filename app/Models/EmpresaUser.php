<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class EmpresaUser extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = "empresa_users";

    protected $fillable = [
        'conta',
        'user_id',
        'empresa_id',  // Adicionando empresa_id para atribuição em massa
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class, 'user_id', 'id');
    }

    public function empresa()
    {
        return $this->hasMany(Empresa::class, 'empresa_id');
    }
}
