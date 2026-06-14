<?php

declare(strict_types=1);

namespace App\Domains\Processo\Repositories;

use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Domains\Processo\Exceptions\ProcessoNaoEncontradoException;
use App\Models\Processo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;

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
        $processo = Processo::query()->create($this->onlyExistingColumns($dto->toArray()));

        return $processo->load($this->relations());
    }

    public function update(int $id, AtualizarProcessoDTO $dto): Processo
    {
        $processo = $this->findOrFail($id);
        $processo->fill($this->onlyExistingColumns($dto->toArray()));
        $processo->save();

        return $processo->refresh()->load($this->relations());
    }

    public function find(int $id): ?Processo
    {
        return $this->processoQuery()->with($this->relations())->find($id);
    }

    public function findOrFail(int $id): Processo
    {
        return $this->find($id) ?? throw ProcessoNaoEncontradoException::comId($id);
    }

    public function findByNumero(string $numero): ?Processo
    {
        return $this->processoQuery()
            ->with($this->relations())
            ->where('NrProcesso', $numero)
            ->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->processoQuery()
            ->with($this->relations(['cliente', 'exportador', 'estancia']))
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

    private function onlyExistingColumns(array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(Schema::getColumnListing('processos')));
    }

    private function relations(?array $relations = null): array
    {
        $relations ??= self::RELATIONS;

        if (!Schema::hasColumn('customers', 'deleted_at')) {
            $relations = array_values(array_diff($relations, ['cliente']));
        }

        if (!Schema::hasTable('estancias')) {
            $relations = array_values(array_diff($relations, ['estancia']));
        }

        $optionalTables = [
            'mercadorias' => 'mercadorias',
            'mercadoriasAgrupadas' => 'mercadoria_agrupadas',
            'procLicenFaturas' => 'proc_licen_sales',
            'emolumentoTarifa' => 'emolumento_tarifas',
        ];

        foreach ($optionalTables as $relation => $table) {
            if (!Schema::hasTable($table)) {
                $relations = array_values(array_diff($relations, [$relation]));
            }
        }

        if (in_array('emolumentoTarifa', $relations, true) && !Schema::hasColumn('emolumento_tarifas', 'deleted_at')) {
            $relations = array_values(array_diff($relations, ['emolumentoTarifa']));
        }

        return $relations;
    }

    private function processoQuery(): Builder
    {
        $query = Processo::query();

        if (!Schema::hasColumn('processos', 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query;
    }
}
