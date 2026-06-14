<?php

namespace App\Livewire\Empresa;

use Livewire\Component;

use App\Models\EmpresaBanco;
use App\Domains\Banco\Services\BancoListService;
use App\Models\Empresa;

class EmpresaContasBancarias extends Component
{
    public Empresa $empresa;

    public ?string $banco = null;
    public ?string $iban = null;
    public ?string $conta = null;

    public $contasList = [];

    public $listaBancos = [];

    public function mount(Empresa $empresa): void
    {
        $this->empresa = $empresa;
        $this->refreshContas();
        $this->listaBancos = BancoListService::getOptions();
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName, [
            'banco' => ['required', 'string', 'max:10'],
            'conta' => ['required', 'string', 'max:255'],
            'iban' => ['required', 'string', 'max:255'],
        ]);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'banco' => ['required', 'string', 'max:10'],
            'conta' => ['required', 'string', 'max:255'],
            'iban' => ['required', 'string', 'max:255'],
        ]);

        abort_unless(array_key_exists($validated['banco'], BancoListService::getOptions()), 422);

        EmpresaBanco::create([
            'empresa_id' => $this->empresa->id,
            'code_banco' => $validated['banco'],
            'iban' => $validated['iban'],
            'conta' => $validated['conta'],
        ]);

        $this->reset(['banco', 'iban', 'conta']);
        $this->refreshContas();

        $this->dispatch('toast', type: 'success', message: 'Conta bancária atualizada com sucesso.');
    }

    public function delete(int $id): void
    {
        EmpresaBanco::query()
            ->where('empresa_id', $this->empresa->id)
            ->whereKey($id)
            ->delete();

        $this->refreshContas();

        $this->dispatch('toast', type: 'success', message: 'Conta bancária removida com sucesso.');
    }

    private function refreshContas(): void
    {
        $this->contasList = EmpresaBanco::query()
            ->where('empresa_id', $this->empresa->id)
            ->latest('id')
            ->get();
    }

    public function render()
    {
        return view('livewire.empresa.empresa-contas-bancarias');
    }
}
