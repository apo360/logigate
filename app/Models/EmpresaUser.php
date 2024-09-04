<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmpresaUser extends Model
{
    use HasFactory;

    protected $table = "empresa_users";

    public function usuarios() : HasMany
    {
        return $this->hasMany(User::class, 'user_id');
    }
}
