<?php

namespace App\Livewire\Empresa;

use App\Domains\Empresa\Actions\AtualizarEmpresaAction;
use App\Domains\Empresa\Data\EmpresaData;
use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EmpresaProfile extends Component
{
    public Empresa $empresa;

    public $Empresa;
    public $ActividadeComercial;
    public $Designacao;
    public $Slogan;
    public $Provincia;
    public $Cidade;
    public $Dominio;
    public $NIF;
    public $Cedula;
    public $Endereco_completo;
    public $Email;
    public $Contacto_movel;
    public $Contacto_fixo;
    public $Fax;
    public ?string $CodFactura = null;
    public ?string $CodProcesso = null;

    public function mount(Empresa $empresa): void
    {
        $this->empresa = $empresa;

        $this->Empresa = $empresa->Empresa;
        $this->ActividadeComercial = $empresa->ActividadeComercial;
        $this->Designacao = $empresa->Designacao;
        $this->Slogan = $empresa->Slogan;
        $this->Provincia = $empresa->Provincia;
        $this->Cidade = $empresa->Cidade;
        $this->Dominio = $empresa->Dominio;
        $this->NIF = $empresa->NIF;
        $this->Cedula = $empresa->Cedula;
        $this->Endereco_completo = $empresa->Endereco_completo;
        $this->Email = $empresa->Email;
        $this->Contacto_movel = $empresa->Contacto_movel;
        $this->Contacto_fixo = $empresa->Contacto_fixo;
        $this->Fax = $empresa->Fax;
        $this->CodFactura = $empresa->CodFactura;
        $this->CodProcesso = $empresa->CodProcesso;
    }

    public function update(AtualizarEmpresaAction $action): void
    {
        $validated = $this->validate();

        $data = [
            'CodFactura' => $validated['CodFactura'] ?? null,
            'CodProcesso' => $validated['CodProcesso'] ?? null,
            'Empresa' => $validated['Empresa'] ?? null,
            'ActividadeComercial' => $validated['ActividadeComercial'] ?? null,
            'Designacao' => $validated['Designacao'] ?? null,
            'Slogan' => $validated['Slogan'] ?? null,
            'Provincia' => $validated['Provincia'] ?? null,
            'Cidade' => $validated['Cidade'] ?? null,
            'Dominio' => $validated['Dominio'] ?? null,
            'NIF' => $validated['NIF'] ?? null,
            'Cedula' => $validated['Cedula'] ?? null,
            'Endereco_completo' => $validated['Endereco_completo'] ?? null,
            'Email' => $validated['Email'] ?? null,
            'Contacto_movel' => $validated['Contacto_movel'] ?? null,
            'Contacto_fixo' => $validated['Contacto_fixo'] ?? null,
            'Fax' => $validated['Fax'] ?? null,
        ];

        $dto = EmpresaData::fromArray($data);

        $this->empresa = $action->execute(Auth::user(), $this->empresa, $dto);

        $this->dispatch('toast', type: 'success', message: 'Empresa atualizada com sucesso.');
    }

    protected function rules(): array
    {
        return [
            'Empresa' => ['required', 'string', 'max:255'],
            'ActividadeComercial' => ['nullable', 'string', 'max:255'],
            'Designacao' => ['nullable', 'string', 'max:255'],
            'Slogan' => ['nullable', 'string', 'max:100'],
            'Provincia' => ['nullable', 'string', 'max:100'],
            'Cidade' => ['nullable', 'string', 'max:100'],
            'Dominio' => ['nullable', 'string', 'max:255'],
            'NIF' => ['required', 'string', 'max:255', Rule::unique('empresas', 'NIF')->ignore($this->empresa->id)],
            'Cedula' => ['nullable', 'string', 'max:255', Rule::unique('empresas', 'Cedula')->ignore($this->empresa->id)],
            'Endereco_completo' => ['nullable', 'string', 'max:255'],
            'Email' => ['nullable', 'email', 'max:255'],
            'Contacto_movel' => ['nullable', 'string', 'max:100'],
            'Contacto_fixo' => ['nullable', 'string', 'max:100'],
            'Fax' => ['nullable', 'string', 'max:100'],
            'CodFactura' => ['nullable', 'string', 'max:50'],
            'CodProcesso' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function render()
    {
        return view('livewire.empresa.empresa-profile');
    }
}
