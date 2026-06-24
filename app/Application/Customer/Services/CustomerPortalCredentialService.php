<?php

namespace App\Application\Customer\Services;

use App\Application\Customer\DTOs\CredenciaisClientePortalDTO;
use App\Domains\ClientePortal\Actions\GerarCredenciaisClienteAction;
use App\Domains\ClientePortal\Actions\RedefinirPasswordClienteAction;
use App\Domains\ClientePortal\DTOs\CredenciaisClienteDTO;
use App\Models\ClientePortal;
use App\Models\Customer;
use Illuminate\Support\Str;

class CustomerPortalCredentialService
{
    public function hasCredentials(Customer $customer): bool
    {
        if (method_exists($customer, 'clientePortal')) {
            return $customer->clientePortal()->exists();
        }

        if (class_exists(ClientePortal::class)) {
            return ClientePortal::query()
                ->where('customer_id', $customer->id)
                ->exists();
        }

        return false;
    }

    public function create(Customer $customer): CredenciaisClientePortalDTO
    {
        $username = $this->generateUsername($customer);
        $password = Str::password(10);

        app(GerarCredenciaisClienteAction::class)->execute(
            $customer->id,
            new CredenciaisClienteDTO(
                username: $username,
                email: $customer->Email,
                password: $password,
            )
        );

        return new CredenciaisClientePortalDTO(
            customerId: $customer->id,
            username: $username,
            password: $password,
            created: true,
            message: 'Credenciais criadas.'
        );
    }

    public function reset(Customer $customer): CredenciaisClientePortalDTO
    {
        $password = Str::password(10);

        app(RedefinirPasswordClienteAction::class)->execute($customer->id, $password);

        $portal = $customer->clientePortal()->first();

        return new CredenciaisClientePortalDTO(
            customerId: $customer->id,
            username: $portal?->username ?? $this->generateUsername($customer),
            password: $password,
            created: false,
            message: 'Password redefinida.'
        );
    }

    private function generateUsername(Customer $customer): string
    {
        return strtolower(
            preg_replace('/[^a-zA-Z0-9]/', '', $customer->CustomerTaxID ?: 'cliente' . $customer->id)
        );
    }
}
