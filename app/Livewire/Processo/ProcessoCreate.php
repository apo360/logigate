<?php

namespace App\Livewire\Processo;

use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Application\Processo\Support\ProcessoFormSupport;
use App\Models\Empresa;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

final class ProcessoCreate extends Component
{
    use AuthorizesRequests;

    public string $mode = 'create';

    // customer_id vindo da URL
    public ?int $customerId = null;

    // Propriedades do formulário (todas dentro de $rules ou com validação individual)

    public ?int $customer_id = null;

    public ?int $exportador_id = null;

    public ?int $estancia_id = null;

    #[Rule('required|string|max:50')]
    public ?string $vinheta = null;

    public string $TipoProcesso = '11';

    #[Rule('required|string')]
    public string $Estado = 'Aberto';

    #[Rule('nullable|string|max:200')]
    public ?string $RefCliente = null;

    #[Rule('nullable|string|max:1000')]
    public ?string $Descricao = null;

    #[Rule('nullable|date')]
    public ?string $DataAbertura = null;

    #[Rule('nullable|date')]
    public ?string $DataPartida = null;

    #[Rule('nullable|date')]
    public ?string $DataChegada = null;

    #[Rule('nullable|string|size:3')]
    public ?string $Moeda = 'USD';

    #[Rule('nullable|numeric|min:0')]
    public ?float $Cambio = 1.0;

    #[Rule('nullable|numeric|min:0')]
    public ?float $fob_total = 0;

    #[Rule('nullable|numeric|min:0')]
    public ?float $frete = 0;

    #[Rule('nullable|numeric|min:0')]
    public ?float $seguro = 0;

    #[Rule('nullable|numeric|min:0')]
    public ?float $cif = 0;

    #[Rule('nullable|numeric|min:0')]
    public ?float $ValorAduaneiro = 0;

    #[Rule('nullable|string')]
    public $TipoTransporte = null;

    #[Rule('nullable|string|max:100')]
    public ?string $registo_transporte = null;

    #[Rule('nullable|exists:paises,id')]
    public ?int $nacionalidade_transporte = null;

    #[Rule('nullable|string|max:20')]
    public ?string $NrDU = null;

    #[Rule('nullable|integer|min:0')]
    public $NrDAR = null;

    #[Rule('nullable|string|max:50')]
    public ?string $NrMarcaFiscal = null;

    #[Rule('nullable|string|max:50')]
    public ?string $BLC_Porte = null;

    #[Rule('nullable|exists:paises,id')]
    public ?int $Pais_origem = null;

    #[Rule('nullable|exists:paises,id')]
    public ?int $Pais_destino = null;

    #[Rule('nullable|string|max:100')]
    public ?string $PortoOrigem = null;

    #[Rule('nullable|exists:portos,id')]
    public $porto_desembarque_id = null;

    #[Rule('nullable|exists:mercadoria_localizacaos,id')]
    public ?int $localizacao_mercadoria_id = null;

    #[Rule('nullable|in:Tr,CK,RD,Ou')]
    public ?string $forma_pagamento = null;

    #[Rule('nullable|string|max:10')]
    public ?string $codigo_banco = null;

    #[Rule('nullable|exists:condicao_pagamentos,id')]
    public ?int $condicao_pagamento_id = null;

    #[Rule('nullable|string|max:500')]
    public ?string $observacoes = null;

    #[Rule('nullable|numeric|min:0')]
    public ?float $peso_bruto = null;

    // Campos específicos de exportação (CRUD petróleo)
    #[Rule('nullable|date')]
    public ?string $data_carregamento = null;

    #[Rule('nullable|numeric|min:0')]
    public ?float $quantidade_barris = null;

    #[Rule('nullable|numeric|min:0')]
    public ?float $valor_barril_usd = null;

    #[Rule('nullable|integer|min:0')]
    public ?int $num_deslocacoes = null;

    #[Rule('nullable|string|max:50')]
    public ?string $rsm_num = null;

    #[Rule('nullable|string|max:50')]
    public ?string $certificado_origem = null;

    #[Rule('nullable|string|max:50')]
    public ?string $guia_exportacao = null;

    // Controle de exibição da secção de exportação
    public bool $showCrudExportFields = false;

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

    public function mount(Request $request): void
    {
        $this->authorize('create', \App\Models\Processo::class);

        $this->customerId = $this->customer_id ?: $request->query('customer_id');
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
        $this->showCrudExportFields = ($this->TipoProcesso === '21'); // 21 = Exportação
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

        // Mostrar/esconder campos de exportação conforme o tipo de processo
        if ($field === 'TipoProcesso') {
            $this->showCrudExportFields = ($value === '21');
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
        return app(ProcessoFormSupport::class)->rules($this->empresa()->id);
    }

    public function save(CriarProcessoAction $action)
    {
        $this->authorize('create', \App\Models\Processo::class);

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
        ]);

        try {
            $processo = $action->execute(CriarProcessoDTO::fromArray($data));

            session()->flash('success', 'Processo criado com sucesso!');
            return redirect()->route('processos.show', $processo);
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao criar processo: ' . $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        return view('livewire.processo.processo-create');
    }

    private function empresa(): Empresa
    {
        $empresa = Auth::user()?->empresas()->first();
        abort_if(!$empresa, 403, 'Nenhuma empresa associada ao usuário autenticado.');

        return $empresa;
    }
}
