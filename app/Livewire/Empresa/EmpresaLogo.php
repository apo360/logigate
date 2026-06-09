<?php

namespace App\Livewire\Empresa;

use App\Domains\Empresa\Actions\AtualizarLogotipoEmpresaAction;
use App\Models\Empresa;
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
        $this->empresa = $empresa;
    }

    public function save(): void
    {
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
}
