<?php

declare(strict_types=1);

namespace App\Livewire\Processo;

use App\Application\Processo\Actions\AtualizarProcessoAction;
use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Application\Processo\Support\ProcessoFormSupport;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Models\Empresa;
use App\Models\Processo;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class ProcessoEdit extends Component
{
    use AuthorizesRequests;

    public Processo $processo;
    public int $processoId;

    public ?int $customer_id = null;
    public ?int $exportador_id = null;
    public ?int $estancia_id = null;
    public ?string $vinheta = null;
    public ?string $TipoProcesso = null;
    public string $Estado = 'Aberto';
    public ?string $RefCliente = null;
    public ?string $Descricao = null;
    public ?string $DataAbertura = null;
    public ?string $DataPartida = null;
    public ?string $DataChegada = null;
    public ?string $Moeda = 'USD';
    public ?float $Cambio = null;
    public ?float $fob_total = null;
    public ?float $frete = null;
    public ?float $seguro = null;
    public ?float $cif = null;
    public ?float $ValorAduaneiro = null;
    public $TipoTransporte = null;
    public ?string $registo_transporte = null;
    public $nacionalidade_transporte = null;
    public ?string $NrDU = null;
    public $N_Dar = null;
    public $NrDAR = null;
    public ?string $MarcaFiscal = null;
    public ?string $NrMarcaFiscal = null;
    public ?string $BLC_Porte = null;
    public ?int $Pais_origem = null;
    public ?int $Pais_destino = null;
    public ?string $PortoOrigem = null;
    public $porto_desembarque_id = null;
    public $localizacao_mercadoria_id = null;
    public ?string $forma_pagamento = null;
    public ?string $codigo_banco = null;
    public $condicao_pagamento_id = null;
    public ?string $observacoes = null;
    public ?float $peso_bruto = null;
    public $quantidade_barris = null;
    public ?string $data_carregamento = null;
    public ?float $valor_barril_usd = null;
    public ?string $num_deslocacoes = null;
    public ?string $rsm_num = null;
    public ?string $certificado_origem = null;
    public ?string $guia_exportacao = null;

    public bool $showCrudExportFields = false;

    public $clientes;
    public $exportadores;
    public $estancias;
    public $paises;
    public $portos;
    public $localMercadoria;
    public $condicaoPagamentoOptions;
    public array $listaBancos = [];
    public $tipoProcessoOptions;
    public array $EstadoOptions = [];
    public array $tipoTransporte = [];
    public array $formaPagamentoOptions = [];

    public function mount(Processo $processo): void
    {
        $this->authorize('update', $processo);

        $this->processo = $processo;
        $this->processoId = (int) $processo->id;

        foreach (app(ProcessoFormSupport::class)->options($this->empresa()) as $property => $value) {
            $this->{$property} = $value;
        }

        $this->fillFromProcesso($processo);
    }

    public function updated($field, $value): void
    {
        if (in_array($field, ['fob_total', 'frete', 'seguro', 'Cambio'], true)) {
            $this->recalcularValores();
        }

        if ($field === 'TipoProcesso') {
            $this->showCrudExportFields = ((string) $value === '21');
        }
    }

    public function update(AtualizarProcessoAction $action)
    {
        $this->authorize('update', $this->processo);

        $validated = $this->validate();
        $this->recalcularValores();

        $payload = array_merge($validated, [
            'id' => $this->processoId,
            'N_Dar' => $this->NrDAR,
            'MarcaFiscal' => $this->NrMarcaFiscal,
            'cif' => $this->cif,
            'ValorAduaneiro' => $this->ValorAduaneiro,
        ]);

        try {
            $processo = $action->execute(AtualizarProcessoDTO::fromArray($payload));
            session()->flash('success', 'Processo atualizado com sucesso.');

            return redirect()->route('processos.show', $processo);
        } catch (\Throwable $e) {
            session()->flash('error', 'Erro ao atualizar processo: ' . $e->getMessage());
            return null;
        }
    }

    public function rules(): array
    {
        return app(ProcessoFormSupport::class)->rules($this->empresa()->id, $this->processoId);
    }

    public function render()
    {
        return view('livewire.processo.processo-edit');
    }

    private function fillFromProcesso(Processo $processo): void
    {
        $this->customer_id = $this->nullableInt($processo->customer_id);
        $this->exportador_id = $this->nullableInt($processo->exportador_id);
        $this->estancia_id = $this->nullableInt($processo->estancia_id);
        $this->vinheta = $this->nullableString($processo->vinheta);
        $this->TipoProcesso = $this->nullableString($processo->TipoProcesso);
        $estado = $this->nullableString($processo->Estado);
        $this->Estado = EstadoProcessoEnum::tryFrom((string) $estado)?->value ?? EstadoProcessoEnum::ABERTO->value;
        $this->RefCliente = $this->nullableString($processo->RefCliente);
        $this->Descricao = $this->nullableString($processo->Descricao);
        $this->DataAbertura = $this->dateForInput($processo->DataAbertura);
        $this->DataPartida = $this->dateForInput($processo->DataPartida);
        $this->DataChegada = $this->dateForInput($processo->DataChegada);
        $this->Moeda = $this->nullableString($processo->Moeda) ?? 'USD';
        $this->Cambio = $this->nullableFloat($processo->Cambio);
        $this->fob_total = $this->nullableFloat($processo->fob_total);
        $this->frete = $this->nullableFloat($processo->frete);
        $this->seguro = $this->nullableFloat($processo->seguro);
        $this->cif = $this->nullableFloat($processo->cif);
        $this->ValorAduaneiro = $this->nullableFloat($processo->ValorAduaneiro);
        $this->TipoTransporte = $this->nullableString($processo->TipoTransporte);
        $this->registo_transporte = $this->nullableString($processo->registo_transporte);
        $this->nacionalidade_transporte = $this->nullableInt($processo->nacionalidade_transporte);
        $this->NrDU = $this->nullableString($processo->NrDU);
        $this->N_Dar = $this->nullableInt($processo->N_Dar);
        $this->NrDAR = $this->N_Dar;
        $this->MarcaFiscal = $this->nullableString($processo->MarcaFiscal);
        $this->NrMarcaFiscal = $this->MarcaFiscal;
        $this->BLC_Porte = $this->nullableString($processo->BLC_Porte);
        $this->Pais_origem = $this->nullableInt($processo->Pais_origem);
        $this->Pais_destino = $this->nullableInt($processo->Pais_destino);
        $this->PortoOrigem = $this->nullableString($processo->PortoOrigem);
        $this->porto_desembarque_id = $this->nullableInt($processo->porto_desembarque_id);
        $this->localizacao_mercadoria_id = $this->nullableInt($processo->localizacao_mercadoria_id);
        $this->forma_pagamento = $this->nullableString($processo->forma_pagamento);
        $this->codigo_banco = $this->nullableString($processo->codigo_banco);
        $this->condicao_pagamento_id = $this->nullableInt($processo->condicao_pagamento_id);
        $this->observacoes = $this->nullableString($processo->observacoes);
        $this->peso_bruto = $this->nullableFloat($processo->peso_bruto);
        $this->quantidade_barris = $this->nullableInt($processo->quantidade_barris);
        $this->data_carregamento = $this->dateForInput($processo->data_carregamento);
        $this->valor_barril_usd = $this->nullableFloat($processo->valor_barril_usd);
        $this->num_deslocacoes = $this->nullableString($processo->num_deslocacoes);
        $this->rsm_num = $this->nullableString($processo->rsm_num);
        $this->certificado_origem = $this->nullableString($processo->certificado_origem);
        $this->guia_exportacao = $this->nullableString($processo->guia_exportacao);
        $this->showCrudExportFields = ((string) $this->TipoProcesso === '21');
    }

    private function recalcularValores(): void
    {
        $values = app(ProcessoFormSupport::class)->calculatedValues(
            $this->fob_total,
            $this->frete,
            $this->seguro,
            $this->Cambio
        );

        $this->cif = $values['cif'];
        $this->ValorAduaneiro = $values['ValorAduaneiro'];
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' || $value === 0 || $value === '0' ? null : (int) $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        return $value === null || $value === '' ? null : (string) $value;
    }

    private function dateForInput(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d');
        }

        return date('Y-m-d', strtotime((string) $value));
    }

    private function empresa(): Empresa
    {
        $empresa = Auth::user()?->empresas()->first();
        abort_if(!$empresa, 403, 'Nenhuma empresa associada ao usuário autenticado.');

        return $empresa;
    }
}
