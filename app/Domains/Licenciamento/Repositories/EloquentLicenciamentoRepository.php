<?php

namespace App\Domains\Licenciamento\Repositories;

use App\Application\Licenciamento\Support\LicenciamentoFormSupport;
use App\Application\Licenciamento\DTOs\CriarLicenciamentoDTO;
use App\Application\Licenciamento\DTOs\AtualizarLicenciamentoDTO;
use App\Models\Licenciamento;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class EloquentLicenciamentoRepository implements LicenciamentoRepositoryInterface
{
    public function create(CriarLicenciamentoDTO $dto): Licenciamento
    {
        $data = $this->onlyExistingColumns($dto->toArray());

        return Licenciamento::create($data);
    }

    public function update(int $id, AtualizarLicenciamentoDTO $dto): Licenciamento
    {
        $licenciamento = Licenciamento::findOrFail($id);
        $data = $this->onlyExistingColumns($dto->toArray());
        $licenciamento->update($data);
        return $licenciamento->fresh();
    }

    public function find(int $id): ?Licenciamento
    {
        return Licenciamento::with(app(LicenciamentoFormSupport::class)->relations())->find($id);
    }

    public function findByCustomer(int $customerId): Collection
    {
        return Licenciamento::where('cliente_id', $customerId)->get();
    }

    public function delete(int $id): bool
    {
        return Licenciamento::destroy($id) > 0;
    }

    private function onlyExistingColumns(array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(Schema::getColumnListing('licenciamentos')));
    }
}
