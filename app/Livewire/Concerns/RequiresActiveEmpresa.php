<?php

namespace App\Livewire\Concerns;

use App\Support\TenantContext;
use Livewire\Attributes\Locked;

/** Reject snapshots produced before a company switch, before any action runs. */
trait RequiresActiveEmpresa
{
    #[Locked]
    public ?int $activeEmpresaSnapshot = null;

    public function mountRequiresActiveEmpresa(): void
    {
        $this->activeEmpresaSnapshot = TenantContext::empresaId();
        abort_unless($this->activeEmpresaSnapshot !== null, 403);
    }

    public function hydrateRequiresActiveEmpresa(): void
    {
        $activeId = TenantContext::empresaId();
        abort_unless($activeId !== null && $activeId === $this->activeEmpresaSnapshot, 403);
    }
}
