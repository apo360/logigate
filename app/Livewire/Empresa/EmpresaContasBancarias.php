<?php

namespace App\Livewire\Empresa;

use App\Domains\Banco\Services\BancoListService;
use App\Models\Empresa;
use App\Models\EmpresaBanco;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class EmpresaContasBancarias extends Component
{
    public Empresa $empresa;

    public ?int $editingId = null;
    public ?string $banco = null;
    public ?string $iban = null;
    public ?string $conta = null;

    public $contasList = [];

    public $listaBancos = [];

    public function mount(Empresa $empresa): void
    {
        $this->empresa = $this->resolveEmpresaAtiva($empresa);
        $this->listaBancos = BancoListService::getOptions();
        $this->refreshContas();
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
        $this->empresa = $this->resolveEmpresaAtiva($this->empresa);

        $validated = $this->validate([
            'banco' => ['required', 'string', 'max:10'],
            'conta' => ['required', 'string', 'max:255'],
            'iban' => ['required', 'string', 'max:255'],
        ]);

        abort_unless(array_key_exists($validated['banco'], BancoListService::getOptions()), 422);

        $attributes = [
            'code_banco' => $validated['banco'],
            'iban' => $validated['iban'],
            'conta' => $validated['conta'],
        ];

        if ($this->editingId) {
            $this->contaForEmpresa($this->editingId)->update($attributes);
        } else {
            EmpresaBanco::create([
                'empresa_id' => $this->empresa->id,
                ...$attributes,
            ]);
        }

        $this->resetForm();
        $this->refreshContas();

        $this->dispatch('toast', type: 'success', message: 'Conta bancária guardada com sucesso.');
    }

    public function edit(int $id): void
    {
        $this->empresa = $this->resolveEmpresaAtiva($this->empresa);

        $conta = $this->contaForEmpresa($id);

        $this->editingId = $conta->id;
        $this->banco = $conta->code_banco;
        $this->iban = $conta->iban;
        $this->conta = $conta->conta;
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->empresa = $this->resolveEmpresaAtiva($this->empresa);

        $this->contaForEmpresa($id)->delete();

        $this->resetForm();
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

    private function contaForEmpresa(int $id): EmpresaBanco
    {
        return EmpresaBanco::query()
            ->where('empresa_id', $this->empresa->id)
            ->findOrFail($id);
    }

    private function resolveEmpresaAtiva(Empresa $empresa): Empresa
    {
        $activeEmpresa = auth()->user()?->empresaAtiva();

        abort_unless($activeEmpresa && (int) $activeEmpresa->id === (int) $empresa->id, 403);
        Gate::forUser(auth()->user())->authorize('update', $activeEmpresa);

        return $activeEmpresa->refresh();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'banco', 'iban', 'conta']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.empresa.empresa-contas-bancarias');
    }
}
