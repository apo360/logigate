<?php

namespace App\Livewire\Empresa;

use App\Domains\Empresa\Actions\AtualizarEmpresaAction;
use App\Domains\Empresa\Data\EmpresaData;
use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
        $this->empresa = $this->resolveEmpresaAtiva($empresa);

        $this->fillFromEmpresa();
    }

    public function update(AtualizarEmpresaAction $action): void
    {
        $this->empresa = $this->resolveEmpresaAtiva($this->empresa);

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
        $this->fillFromEmpresa();

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

    private function resolveEmpresaAtiva(Empresa $empresa): Empresa
    {
        $activeEmpresa = Auth::user()?->empresaAtiva();

        abort_unless($activeEmpresa && (int) $activeEmpresa->id === (int) $empresa->id, 403);
        Gate::forUser(Auth::user())->authorize('update', $activeEmpresa);

        return $activeEmpresa->refresh();
    }

    private function fillFromEmpresa(): void
    {
        $this->Empresa = $this->empresa->Empresa;
        $this->ActividadeComercial = $this->empresa->ActividadeComercial;
        $this->Designacao = $this->empresa->Designacao;
        $this->Slogan = $this->empresa->Slogan;
        $this->Provincia = $this->empresa->Provincia;
        $this->Cidade = $this->empresa->Cidade;
        $this->Dominio = $this->empresa->Dominio;
        $this->NIF = $this->empresa->NIF;
        $this->Cedula = $this->empresa->Cedula;
        $this->Endereco_completo = $this->empresa->Endereco_completo;
        $this->Email = $this->empresa->Email;
        $this->Contacto_movel = $this->empresa->Contacto_movel;
        $this->Contacto_fixo = $this->empresa->Contacto_fixo;
        $this->Fax = $this->empresa->Fax;
        $this->CodFactura = $this->empresa->CodFactura;
        $this->CodProcesso = $this->empresa->CodProcesso;
    }
}
