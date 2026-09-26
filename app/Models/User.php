<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use HasRoles;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use \OwenIt\Auditing\Auditable;
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'otp',
        'otp_expires_at',
        'otp_verified_at',
        'is_active',
        'is_blocked',
        'password_changed',
        'last_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'password_changed' => 'boolean',
            'last_change_password' => 'datetime',
        ];
    }

    public function hasAnyAppPermission($permissions): bool
    {
        return $this->hasAnyPermission($permissions); // chama o do Spatie
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_users')
            ->withPivot(['id', 'conta', 'role'])
            ->withTimestamps();
    }

    /**
     * Empresa actual do utilizador (via sessão, ou primeira como fallback).
     */
    public function empresaAtiva(): ?Empresa
    {
        $empresaId = session('empresa_id');

        if ($empresaId) {
            return $this->empresas()->where('empresas.id', $empresaId)->first();
        }

        return $this->empresas()->first();
    }

    /** Role do utilizador NA empresa indicada. */
    public function roleNaEmpresa(?Empresa $empresa = null): ?string
    {
        $empresa ??= $this->empresaAtiva();

        if (! $empresa) {
            return null;
        }

        return $this->empresas()
            ->where('empresas.id', $empresa->id)
            ->first()?->pivot->role;
    }

    public function hasActiveSubscription(): bool
    {
        $empresa = $this->empresaAtiva();

        if (! $empresa) {
            return false;
        }

        // Route dashboard access through the company-level relation so expired
        // records or legacy uppercase values do not bypass subscription checks.
        return $empresa->subscricaoAtiva()->exists();
    }
}
