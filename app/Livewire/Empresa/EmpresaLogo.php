<?php

namespace App\Livewire\Empresa;

use App\Domains\Empresa\Actions\AtualizarLogotipoEmpresaAction;
use App\Models\Empresa;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class EmpresaLogo extends Component
{
    use WithFileUploads;

    public Empresa $empresa;

    public ?TemporaryUploadedFile $logotipo = null;

    public function mount(Empresa $empresa): void
    {
        $this->empresa = $this->resolveEmpresaAtiva($empresa);
    }

    public function save(): void
    {
        $this->empresa = $this->resolveEmpresaAtiva($this->empresa);

        $this->validate([
            'logotipo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $this->empresa = app(AtualizarLogotipoEmpresaAction::class)->execute(auth()->user(), $this->empresa, $this->logotipo);
        $this->logotipo = null;

        $this->dispatch('toast', type: 'success', message: 'Logotipo atualizado com sucesso.');
    }

    public function render()
    {
        return view('livewire.empresa.empresa-logo');
    }

    private function resolveEmpresaAtiva(Empresa $empresa): Empresa
    {
        $activeEmpresa = auth()->user()?->empresaAtiva();

        abort_unless($activeEmpresa && (int) $activeEmpresa->id === (int) $empresa->id, 403);
        Gate::forUser(auth()->user())->authorize('update', $activeEmpresa);

        return $activeEmpresa->refresh();
    }
}
