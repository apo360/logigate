<?php

declare(strict_types=1);

namespace App\Livewire\Processo;

use App\Application\Processo\Actions\AtualizarProcessoAction;
use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Models\Estancia;
use App\Models\Processo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

final class ProcessoEdit extends Component
{
    public Processo $processo;

    public int $processoId;

    public array $form = [];

    public $clientes;

    public $exportadores;

    public $estancias;

    public function mount(Processo $processo): void
    {
        $empresa = Auth::user()->empresas->first();

        $this->processo = $processo;
        $this->processoId = (int) $processo->id;
        $this->clientes = $empresa?->customers()->orderBy('CompanyName')->get() ?? collect();
        $this->exportadores = $empresa?->exportadors()->orderBy('Exportador')->get() ?? collect();
        $this->estancias = Estancia::query()->orderBy('desc_estancia')->get();

        $this->form = [
            'customer_id' => $processo->customer_id,
            'exportador_id' => $processo->exportador_id,
            'estancia_id' => $processo->estancia_id,
            'TipoProcesso' => $processo->TipoProcesso,
            'Estado' => $processo->Estado,
            'RefCliente' => $processo->RefCliente,
            'Descricao' => $processo->Descricao,
            'DataAbertura' => $processo->DataAbertura,
            'fob_total' => $processo->fob_total,
            'frete' => $processo->frete,
            'seguro' => $processo->seguro,
            'cif' => $processo->cif,
        ];
    }

    public function update(AtualizarProcessoAction $action)
    {
        $data = $this->validate()['form'];
        
        $processo = $action->execute(AtualizarProcessoDTO::fromArray($data + ['id' => $this->processoId]));

        session()->flash('success', 'Processo atualizado com sucesso.');

        return redirect()->route('processos.show', $processo);
    }

    public function rules(): array
    {
        return [
            // Campos do formulário (schema/type-check). Regras de transição ficam 100% no action.
            'form.customer_id' => ['required', 'exists:customers,id'],
            'form.exportador_id' => ['required', 'exists:exportadors,id'],
            'form.estancia_id' => ['required', 'exists:estancias,id'],
            'form.TipoProcesso' => ['required', 'string'],
            'form.Estado' => ['required', Rule::enum(EstadoProcessoEnum::class)],
            'form.RefCliente' => ['nullable', 'string', 'max:200'],
            'form.Descricao' => ['nullable', 'string', 'max:200'],
            'form.DataAbertura' => ['nullable', 'date'],
            // Valores monetários
            'form.fob_total' => ['nullable', 'numeric', 'min:0'],
            'form.frete' => ['nullable', 'numeric', 'min:0'],
            'form.seguro' => ['nullable', 'numeric', 'min:0'],
            'form.cif' => ['nullable', 'numeric', 'min:0'],
        ];
    }


    public function render()
    {
        return view('livewire.processo.processo-edit', [
            'estados' => EstadoProcessoEnum::cases(),
        ]);
    }
}
