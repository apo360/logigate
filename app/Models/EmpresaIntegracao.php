<?php

namespace App\Models;

use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use OwenIt\Auditing\Contracts\Auditable;

class EmpresaIntegracao extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'empresa_integracoes';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'provedor',
        'estado',
        'config',
        'credentials_encrypted',
        'ultimo_teste_em',
        'ultimo_teste_status',
        'ultimo_erro',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'credentials_encrypted',
    ];

    protected $casts = [
        'tipo' => TipoIntegracaoEnum::class,
        'provedor' => ProvedorIntegracaoEnum::class,
        'estado' => EstadoIntegracaoEnum::class,
        'config' => 'array',
        'credentials_encrypted' => 'array',
        'ultimo_teste_em' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function setCredentials(array $credentials): void
    {
        $filtered = array_filter($credentials, fn ($value) => $value !== null && $value !== '');

        if ($filtered === []) {
            return;
        }

        $this->attributes['credentials_encrypted'] = json_encode([
            'payload' => Crypt::encryptString(json_encode($filtered, JSON_THROW_ON_ERROR)),
        ], JSON_THROW_ON_ERROR);
    }

    public function credentials(): array
    {
        $stored = $this->attributes['credentials_encrypted'] ?? null;

        if (! $stored) {
            return [];
        }

        $container = is_array($stored) ? $stored : json_decode($stored, true);
        $encrypted = is_array($container) ? ($container['payload'] ?? null) : $stored;

        if (! is_string($encrypted) || $encrypted === '') {
            return [];
        }

        $decoded = json_decode(Crypt::decryptString($encrypted), true);

        return is_array($decoded) ? $decoded : [];
    }

    public function maskedCredentials(): array
    {
        return collect($this->credentials())
            ->map(fn ($value) => $this->maskSecret((string) $value))
            ->all();
    }

    public function markTestResult(bool $success, ?string $message = null): void
    {
        $this->forceFill([
            'ultimo_teste_em' => now(),
            'ultimo_teste_status' => $success ? 'sucesso' : 'falha',
            'ultimo_erro' => $success ? null : $this->sanitizeError($message),
            'estado' => $success ? $this->estado : EstadoIntegracaoEnum::Erro,
        ])->save();
    }

    private function maskSecret(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (mb_strlen($value) <= 8) {
            return str_repeat('*', mb_strlen($value));
        }

        return mb_substr($value, 0, 4) . str_repeat('*', 8) . mb_substr($value, -4);
    }

    private function sanitizeError(?string $message): ?string
    {
        if ($message === null) {
            return null;
        }

        return mb_substr(preg_replace('/(token|secret|key|password|senha)[^,\s]*/i', '$1=***', $message) ?? $message, 0, 1000);
    }
}
