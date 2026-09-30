<?php

declare(strict_types=1);

namespace App\Application\Contentor\Actions;

use App\Application\Mercadoria\Services\MercadoriaTenantAccessService;
use App\Models\Contentor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CriarContentorAction
{
    public function __construct(private readonly MercadoriaTenantAccessService $tenantAccess)
    {
    }

    public function execute(int $processoId, array $data): Contentor
    {
        return DB::transaction(function () use ($processoId, $data): Contentor {
            $processo = $this->tenantAccess->authorizeContext(Auth::user(), 'processo', $processoId, 'mercadorias.create');
            $numero = trim((string) ($data['numero'] ?? ''));
            if ($numero === '' || mb_strlen($numero) > 50) {
                throw ValidationException::withMessages(['contentor.numero' => 'Informe um número de contentor válido.']);
            }

            return Contentor::query()->create([
                'processo_id' => $processo->id,
                'empresa_id' => $processo->empresa_id,
                'numero' => $numero,
                'tipo' => self::nullable($data['tipo'] ?? null),
                'indicador_carga' => self::nullable($data['indicador_carga'] ?? null),
                'peso_tara' => self::nullableNumber($data['peso_tara'] ?? null),
                'peso_bruto' => self::nullableNumber($data['peso_bruto'] ?? null),
                'numero_volumes' => self::nullableInteger($data['numero_volumes'] ?? null),
            ]);
        });
    }

    private static function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private static function nullableNumber(mixed $value): ?float
    {
        return $value === '' || $value === null ? null : (float) $value;
    }

    private static function nullableInteger(mixed $value): ?int
    {
        return $value === '' || $value === null ? null : (int) $value;
    }
}
