<?php

namespace App\Livewire\Empresa;

use App\Http\Controllers\BillingPlanController;
use App\Livewire\Concerns\RequiresActiveEmpresa;
use Illuminate\Http\Request;
use Livewire\Component;

class EmpresaSubscricao extends Component
{
    use RequiresActiveEmpresa;

    public function mount(): void
    {
        $this->redirectRoute('billing.plans');
    }

    public function index()
    {
        return redirect()->route('billing.plans');
    }

    public function start(Request $request)
    {
        return app(BillingPlanController::class)->start($request);
    }

    public function render()
    {
        return view('livewire.billing-legacy-redirect');
    }
}
