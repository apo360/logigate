<?php

namespace App\Livewire\Customers;

use App\Application\Customer\Actions\CriarCredenciaisClientePortalAction;
use App\Application\Customer\Actions\RedefinirCredenciaisClientePortalAction;
use App\Application\Customer\Queries\BuscarCustomerQuery;
use App\Models\Customer;
use Livewire\Attributes\On;
use Livewire\Component;

class PortalCredenciaModal extends Component
{
    public bool $open = false;

    public ?int $customerId = null;

    public ?Customer $customer = null;

    public ?array $credentials = null;

    public function mount(?int $customerId = null): void
    {
        $this->customerId = $customerId;

        if ($customerId) {
            $this->loadCustomer($customerId);
        }
    }

    #[On('open-customer-portal-credentials-modal')]
    public function openModal(int $customerId): void
    {
        $this->customerId = $customerId;
        $this->credentials = null;

        $this->loadCustomer($customerId);

        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->credentials = null;
    }

    public function createCredentials(CriarCredenciaisClientePortalAction $action): void
    {
        if (!$this->customerId) {
            session()->flash('error', 'Cliente não informado.');
            return;
        }

        try {
            $dto = $action->execute($this->customerId);

            $this->credentials = $dto->toArray();

            $this->dispatch(
                'toast',
                type: 'success',
                message: $dto->message ?? 'Credenciais criadas com sucesso.'
            );
        } catch (\Throwable $e) {
            report($e);

            $this->dispatch(
                'toast',
                type: 'error',
                message: $e->getMessage()
            );
        }
    }

    public function resetCredentials(RedefinirCredenciaisClientePortalAction $action): void
    {
        if (!$this->customerId) {
            session()->flash('error', 'Cliente não informado.');
            return;
        }

        try {
            $dto = $action->execute($this->customerId);

            $this->credentials = $dto->toArray();

            $this->dispatch(
                'toast',
                type: 'success',
                message: $dto->message ?? 'Credenciais redefinidas com sucesso.'
            );
        } catch (\Throwable $e) {
            report($e);

            $this->dispatch(
                'toast',
                type: 'error',
                message: $e->getMessage()
            );
        }
    }

    private function loadCustomer(int $customerId): void
    {
        $this->customer = app(BuscarCustomerQuery::class)->execute($customerId);
    }
    
    public function render()
    {
        return view('livewire.customers.portal-credencia-modal');
    }
}
