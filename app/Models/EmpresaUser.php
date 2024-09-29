<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmpresaUser extends Model
{
    use HasFactory;

    protected $table = "empresa_users";

    public function usuarios()
    {
        return $this->hasMany(User::class, 'user_id', 'id');
    }

    public function empresa()
    {
        return $this->hasMany(Empresa::class, 'empresa_id');
    }
}
