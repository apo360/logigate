<?php

namespace App\Livewire\Processo;

use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Application\Processo\Support\ProcessoFormSupport;
use App\Domains\Processo\Enums\NaturezaOperacaoProcesso;
use App\Application\Processo\Support\ExportacaoCrudeFormSupport;
use App\Models\Empresa;
use App\Models\Processo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProcessoCreate extends Component
{
    use AuthorizesRequests;

    public string $mode = 'create';

    // customer_id vindo da URL
    public ?int $customerId = null;

    // Propriedades validadas pelas regras centrais de ProcessoFormSupport.

    public ?int $customer_id = null;

    public ?int $exportador_id = null;

    public ?int $estancia_id = null;

    public ?string $vinheta = null;

    public string $TipoProcesso = '';

    public string $Estado = 'Aberto';

    public ?string $RefCliente = null;

    public ?string $Descricao = null;

    public ?string $DataAbertura = null;

    public ?string $DataPartida = null;

    public ?string $DataChegada = null;

    public ?string $Moeda = 'USD';

    public ?float $Cambio = 1.0;

    public ?float $fob_total = 0;

    public ?float $frete = 0;

    public ?float $seguro = 0;

    public ?float $cif = 0;

    public ?float $ValorAduaneiro = 0;

    public $TipoTransporte = null;

    public ?string $registo_transporte = null;

    public ?int $nacionalidade_transporte = null;

    public ?string $NrDU = null;

    public $NrDAR = null;

    public ?string $NrMarcaFiscal = null;

    public ?string $BLC_Porte = null;

    public ?int $Pais_origem = null;

    public ?int $Pais_destino = null;

    public ?string $PortoOrigem = null;

    public $porto_desembarque_id = null;

    public ?int $localizacao_mercadoria_id = null;

    public ?string $forma_pagamento = null;

    public ?string $codigo_banco = null;

    public ?int $condicao_pagamento_id = null;

    public ?string $observacoes = null;

    public ?float $peso_bruto = null;

    // Campos específicos de exportação (CRUD petróleo)
    public ?string $data_carregamento = null;

    public ?float $quantidade_barris = null;

    public ?float $valor_barril_usd = null;

    public ?string $num_deslocacoes = null;

    public ?string $rsm_num = null;

    public ?string $certificado_origem = null;

    public ?string $guia_exportacao = null;

    // Listas para selects
    public $tipoProcessoOptions = [];
    public $clientes = [];
    public $exportadores = [];
    public $estancias = [];
    public $paises = [];
    public $portos = [];
    public $localMercadoria = [];
    public $formaPagamentoOptions = [];
    public $EstadoOptions = [];
    public $condicaoPagamentoOptions = [];
    public $listaBancos = [];
    public $tipoTransporte = [];

    // Listeners para modais
    protected $listeners = [
        'clienteCriado' => 'adicionarClienteNaLista',
        'exportadorCriado' => 'adicionarExportadorNaLista',
    ];

    public function adicionarClienteNaLista(int $clienteId, $nome = null): void
    {
        $this->clientes = app(ProcessoFormSupport::class)->options($this->empresa())['clientes'];
        $this->customer_id = $clienteId;
        $this->dispatch('fecharModalCliente');
    }

    public function adicionarExportadorNaLista(int $exportadorId, $nome = null): void
    {
        $this->exportadores = app(ProcessoFormSupport::class)->options($this->empresa())['exportadores'];
        $this->exportador_id = $exportadorId;
        $this->dispatch('fecharModalExportador');
    }

    public function abrirModalCliente(): void
    {
        $this->dispatch('abrirModalCliente');
    }

    public function abrirModalExportador(): void
    {
        $this->dispatch('abrirModalExportador');
    }

    public function mount(?int $customer_id = null, bool $exportacao = false): void
    {
        $this->authorize('create', Processo::class);

        $this->customerId = $customer_id;

        $empresa = $this->empresa();
        $options = app(ProcessoFormSupport::class)->options($empresa);

        if ($this->customerId) {
            abort_unless(
                $options['clientes']->contains('id', (int) $this->customerId),
                404
            );

            $this->customer_id = (int) $this->customerId;
        }

        foreach ($options as $property => $value) {
            $this->{$property} = $value;
        }

        // Valores padrão
        $this->DataAbertura = now()->toDateString();
        $this->Moeda = 'USD';
        $this->Cambio = 1.0;
        $this->fob_total = 0;
        $this->frete = 0;
        $this->seguro = 0;
        $this->cif = 0;
        $this->ValorAduaneiro = 0;
        if ($this->naturezaOperacao() === NaturezaOperacaoProcesso::EXPORTACAO_CRUDE) {
            $this->tipoProcessoOptions = app(ExportacaoCrudeFormSupport::class)->options();
            $exportacao = true;
        }
        $tipo = $this->tipoProcessoOptions->first(fn ($tipo) =>
            $tipo->abrev === ($exportacao ? 'EX' : 'IM') && (string) $tipo->codigo === ($exportacao ? '1' : '4')
        );
        $this->TipoProcesso = $tipo ? (string) $tipo->id : '';

        $this->nacionalidade_transporte = $this->paises->firstWhere('pais', 'Angola')?->id
            ?? $this->paises->first()?->id
            ?? null;
    }

    public function updated($field, $value): void
    {
        // Recalcular CIF sempre que fob_total, frete ou seguro mudarem
        if (in_array($field, ['fob_total', 'frete', 'seguro'], true)) {
            $this->cif = (float)($this->fob_total ?? 0) + (float)($this->frete ?? 0) + (float)($this->seguro ?? 0);
            $this->recalcularValorAduaneiro();
        }

        // Recalcular Valor Aduaneiro quando o câmbio mudar
        if ($field === 'Cambio') {
            $this->recalcularValorAduaneiro();
        }

    }

    private function recalcularValorAduaneiro(): void
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

    public function rules(): array
    {
        $specific = app(ExportacaoCrudeFormSupport::class);
        return array_replace(app(ProcessoFormSupport::class)->rules($this->empresa()->id),
            $this->naturezaOperacao() === NaturezaOperacaoProcesso::EXPORTACAO_CRUDE ? $specific->rules() : $specific->generalRules());
    }

    public function messages(): array
    {
        return app(ProcessoFormSupport::class)->messages();
    }

    public function validationAttributes(): array
    {
        return app(ProcessoFormSupport::class)->attributes();
    }

    public function save(CriarProcessoAction $action)
    {
        $this->authorize('create', Processo::class);
        $this->resetErrorBag('save');

        $validated = $this->validate();

        $user = Auth::user();
        $empresa = $this->empresa();

        // Preparar dados para o DTO
        $data = array_merge($validated, [
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'ValorAduaneiro' => $this->ValorAduaneiro,
            'N_Dar' => $this->NrDAR,
            'MarcaFiscal' => $this->NrMarcaFiscal,
            'natureza_operacao' => $this->naturezaOperacao()->value,
        ]);

        try {
            $processo = $this->persist(CriarProcessoDTO::fromArray($data), $action);

            session()->flash('success', 'Processo criado com sucesso!');
            return redirect()->route('processos.show', $processo);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->addError('save', 'Erro ao criar processo: ' . $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        return view('livewire.processo.processo-create', ['isCrude' => false]);
    }

    protected function naturezaOperacao(): NaturezaOperacaoProcesso
    {
        return NaturezaOperacaoProcesso::GERAL;
    }

    protected function persist(CriarProcessoDTO $dto, CriarProcessoAction $action): Processo
    {
        return $action->execute($dto);
    }

    private function empresa(): Empresa
    {
        $user = Auth::user();
        abort_if(! $user, 403, 'Usuário autenticado não encontrado.');

        $empresaId = app(ProcessoTenantAccessService::class)->empresaIdFor($user);
        abort_if(! $empresaId, 403, 'Nenhuma empresa associada ao usuário autenticado.');

        $empresa = $user->empresas()->where('empresas.id', $empresaId)->first();
        abort_if(!$empresa, 403, 'Nenhuma empresa associada ao usuário autenticado.');

        return $empresa;
    }
}
