<?php
namespace App\Application\Importacao;

use App\Models\Empresa;
use Illuminate\Validation\ValidationException;

final class ImportReferences
{
    public function validate(Empresa $empresa, mixed $customerId, mixed $exportadorId): void
    {
        if (! $empresa->customers()->where('customers.id', $customerId)->exists()) {
            throw ValidationException::withMessages(['customer_id' => 'Cliente sem associação à empresa activa.']);
        }
        if (! $empresa->exportadors()->where('exportadors.id', $exportadorId)->exists()) {
            throw ValidationException::withMessages(['exportador_id' => 'Exportador sem associação à empresa activa.']);
        }
    }
}
