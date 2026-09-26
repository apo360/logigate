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
        'role',  // Adicionando role para atribuição em massa
    ];

    /** EmpresaUser PERTENCE a um User */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** EmpresaUser PERTENCE a uma Empresa */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
