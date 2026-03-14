<?php

namespace App\Services\Dashboard;

use App\Models\Empresa;

abstract class BaseDashboardService
{
    public function __construct(protected readonly Empresa $empresa)
    {
    }

    protected function empresaId(): int
    {
        return (int) $this->empresa->getKey();
    }

    protected function cacheKey(string $suffix): string
    {
        return sprintf('dashboard:%s:%d', $suffix, $this->empresaId());
    }
}
