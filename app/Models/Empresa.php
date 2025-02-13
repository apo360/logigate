<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Empresa extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = "empresas";

    protected $fillable = [
        'CodFactura',
        'CodProcesso',
        'Empresa',
        'ActividadeComercial',
        'Designacao',
        'NIF',
        'Cedula',
        'Logotipo',
        'Slogan',
        'Endereco_completo',
        'Provincia',
        'Cidade',
        'Dominio',
        'Email',
        'Fax',
        'Contacto_movel',
        'Contacto_fixo',
        'Sigla',
    ];

    // Definir relacionamento com usuários
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'empresa_users');
    }

    public function activatedModules()
    {
        return $this->hasMany(ActivatedModule::class);
    }

    public function subscricoes(): HasMany
    {
        return $this->hasMany(Subscricao::class, 'empresa_id', 'id');
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'Provincia', 'id');
    }
}
