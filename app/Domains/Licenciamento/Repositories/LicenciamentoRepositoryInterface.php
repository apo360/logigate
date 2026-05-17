<?php

namespace App\Domains\Licenciamento\Repositories;

use App\Application\Licenciamento\DTOs\CriarLicenciamentoDTO;
use App\Application\Licenciamento\DTOs\AtualizarLicenciamentoDTO;
use App\Models\Licenciamento;
use Illuminate\Support\Collection;


interface LicenciamentoRepositoryInterface
{
    public function create(CriarLicenciamentoDTO $data): Licenciamento;
    public function update(int $id, AtualizarLicenciamentoDTO $data): Licenciamento;
    public function find(int $id): ?Licenciamento;
    public function findByCustomer(int $customerId): Collection;
}