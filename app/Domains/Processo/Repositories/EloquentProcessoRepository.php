<?php

declare(strict_types=1);

namespace App\Domains\Processo\Repositories;

use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Domains\Processo\Exceptions\ProcessoNaoEncontradoException;
use App\Models\Processo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentProcessoRepository implements ProcessoRepositoryInterface
{
    private const RELATIONS = [
        'cliente',
        'exportador',
        'empresa',
        'estancia',
        'mercadorias',
        'mercadoriasAgrupadas',
        'procLicenFaturas',
        'emolumentoTarifa',
    ];

    public function create(CriarProcessoDTO $dto): Processo
    {
        $processo = Processo::query()->create($dto->toArray());

        return $processo->load(self::RELATIONS);
    }

    public function update(int $id, AtualizarProcessoDTO $dto): Processo
    {
        $processo = $this->findOrFail($id);
        $processo->fill($dto->toArray());
        $processo->save();

        return $processo->refresh()->load(self::RELATIONS);
    }

    public function find(int $id): ?Processo
    {
        return Processo::query()->with(self::RELATIONS)->find($id);
    }

    public function findOrFail(int $id): Processo
    {
        return $this->find($id) ?? throw ProcessoNaoEncontradoException::comId($id);
    }

    public function findByNumero(string $numero): ?Processo
    {
        return Processo::query()
            ->with(self::RELATIONS)
            ->where('NrProcesso', $numero)
            ->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Processo::query()
            ->with(['cliente', 'exportador', 'estancia'])
            ->when($filters['empresa_id'] ?? null, fn (Builder $query, int $empresaId) => $query->where('empresa_id', $empresaId))
            ->when($filters['customer_id'] ?? null, fn (Builder $query, int $customerId) => $query->where('customer_id', $customerId))
            ->when($filters['estado'] ?? null, fn (Builder $query, string $estado) => $query->where('Estado', $estado))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('NrProcesso', 'like', "%{$search}%")
                        ->orWhere('RefCliente', 'like', "%{$search}%")
                        ->orWhere('Descricao', 'like', "%{$search}%");
                });
            })
            ->latest('DataAbertura')
            ->paginate($perPage);
    }

    public function delete(int $id): bool
    {
        $processo = $this->findOrFail($id);

        return (bool) $processo->delete();
    }

    /**
     * Verifica quais campos importantes estão vazios e retorna um array com os campos não preenchidos.
     * 
     * @param array $camposImportantes Array associativo onde a chave é o nome do campo e o valor é o rótulo amigável do campo.
     * @return array Array associativo dos campos não preenchidos, onde a chave é o
     */
    public function verificarCamposImportantes(array $camposImportantes): array
    {
        $naoPreenchidos = [];

        foreach ($camposImportantes as $campo => $label) {
            if (empty($this->$campo)) {
                $naoPreenchidos[$campo] = $label;
            }
        }

        return $naoPreenchidos;
    }
}
