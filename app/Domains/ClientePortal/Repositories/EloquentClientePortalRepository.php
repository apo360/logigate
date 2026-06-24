<?php

namespace App\Domains\ClientePortal\Repositories;

use App\Domains\ClientePortal\DTOs\CredenciaisClienteDTO;
use App\Models\ClientePortal;
use App\Models\Customer;
use App\Models\Processo;
use Illuminate\Support\Facades\Hash;

final class EloquentClientePortalRepository implements ClientePortalRepositoryInterface
{
    public function clientePossuiCredenciais(int|string $clienteId): bool
    {
        return ClientePortal::query()
            ->where('customer_id', $clienteId)
            ->exists();
    }

    public function criarCredenciaisCliente(int|string $clienteId, CredenciaisClienteDTO $dto): void
    {
        $customer = Customer::query()->findOrFail($clienteId);

        ClientePortal::query()->create([
            'customer_id' => $customer->id,
            'empresa_id' => $customer->empresa_id,
            'username' => $dto->username,
            'email' => $dto->email,
            'phone' => $customer->Telephone,
            'password' => Hash::make($dto->password),
            'is_active' => true,
        ]);
    }

    public function verificarClienteVinculado(int|string $clienteId): bool
    {
        return ClientePortal::query()
            ->where('customer_id', $clienteId)
            ->exists();
    }

    public function redefinirPasswordCliente(int|string $clienteId, string $novaPassword): void
    {
        ClientePortal::query()
            ->where('customer_id', $clienteId)
            ->firstOrFail()
            ->update([
                'password' => Hash::make($novaPassword),
                'password_reset_at' => now(),
            ]);
    }

    public function listarProcessosCliente(int|string $clienteId, array $filtros = []): array
    {
        return Processo::query()
            ->where('customer_id', $clienteId)
            ->latest('id')
            ->get()
            ->toArray();
    }

    public function getUsuarioVinculado(int|string $clienteId): ?array
    {
        $portal = ClientePortal::query()
            ->where('customer_id', $clienteId)
            ->first();

        return $portal?->only(['id', 'customer_id', 'username', 'email']);
    }
}
