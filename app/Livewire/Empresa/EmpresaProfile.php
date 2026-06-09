<?php

namespace App\Livewire\Empresa;

use App\Domains\Empresa\Actions\AtualizarEmpresaAction;
use App\Domains\Empresa\Data\EmpresaData;
use App\Models\Empresa;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EmpresaProfile extends Component
{
    public Empresa $empresa;

    public array $form = [];

    public function mount(Empresa $empresa): void
    {
        $this->empresa = $empresa;
        $this->form = $empresa->only([
            'CodFactura',
            'CodProcesso',
            'Empresa',
            'ActividadeComercial',
            'Designacao',
            'NIF',
            'Cedula',
            'Slogan',
            'Endereco_completo',
            'Provincia',
            'Cidade',
            'Dominio',
            'Email',
            'Fax',
            'Contacto_movel',
            'Contacto_fixo',
            'Sigla',
        ]);
    }

    public function save(): void
    {
        $this->validate([
            'form.Empresa' => ['required', 'string', 'max:255'],
            'form.NIF' => ['required', 'string', 'max:255', Rule::unique('empresas', 'NIF')->ignore($this->empresa->id)],
            'form.Cedula' => ['nullable', 'string', 'max:255', Rule::unique('empresas', 'Cedula')->ignore($this->empresa->id)],
            'form.Endereco_completo' => ['nullable', 'string', 'max:255'],
            'form.Email' => ['nullable', 'email', 'max:255'],
            'form.Contacto_movel' => ['nullable', 'string', 'max:100'],
            'form.Contacto_fixo' => ['nullable', 'string', 'max:100'],
        ]);

        $this->empresa = app(AtualizarEmpresaAction::class)->execute(
            auth()->user(),
            $this->empresa,
            EmpresaData::fromArray($this->form),
        );

        $this->dispatch('toast', type: 'success', message: 'Empresa atualizada com sucesso.');
    }

    public function render()
    {
        return view('livewire.empresa.empresa-profile');
    }
}
