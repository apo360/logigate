<?php

namespace App\Livewire;

use App\Livewire\Concerns\RequiresActiveEmpresa;
use Livewire\Component;

/** Compatibility entry point; all purchasing uses the canonical checkout. */
class SubscriptionWizard extends Component
{
    use RequiresActiveEmpresa;

    public function mount(): void
    {
        $this->redirectRoute('billing.plans');
    }

    public function finalizarSubscricao()
    {
        return $this->redirectRoute('billing.plans');
    }

    public function proximoPasso()
    {
        return $this->redirectRoute('billing.plans');
    }

    public function passoAnterior()
    {
        return $this->redirectRoute('billing.plans');
    }

    public function render()
    {
        return view('livewire.billing-legacy-redirect');
    }
}
