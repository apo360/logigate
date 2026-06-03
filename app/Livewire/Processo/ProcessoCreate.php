<?php

namespace App\Livewire\Processo;

use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Domains\Banco\Services\BancoListService;
use App\Domains\Licenciamento\Enums\TipoTransporte;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Domains\Processo\Enums\FormaPagamentoEnum;
use App\Models\Customer;
use App\Models\Estancia;
use App\Models\Pais;
use App\Models\Porto;
use App\Models\RegiaoAduaneira;
use App\Models\CondicaoPagamento;
use App\Models\MercadoriaLocalizacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Component;

final class ProcessoCreate extends Component
{
    public string $mode = 'create';

    // customer_id vindo da URL
    public ?int $customerId = null;

    // Propriedades do formulário (todas dentro de $rules ou com validação individual)

    #[Rule('required|exists:customers,id')]
    public ?int $customer_id = null;

    #[Rule('required|exists:exportadors,id')]
    public ?int $exportador_id = null;

    #[Rule('required|exists:estancias,id')]
    public ?int $estancia_id = null;

    #[Rule('required|string|max:50')]
    public ?string $vinheta = null;

    #[Rule('required|in:11,21')]
    public string $TipoProcesso = '11';

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
    public ?string $tipo_transporte = null;

    #[Rule('nullable|string|max:100')]
    public ?string $registo_transporte = null;

    #[Rule('nullable|exists:paises,id')]
    public ?int $nacionalidade_transporte = null;

    #[Rule('nullable|string|max:20')]
    public ?string $NrDU = null;

    #[Rule('nullable|string|max:20')]
    public ?string $NrDAR = null;

    #[Rule('nullable|string|max:50')]
    public ?string $NrMarcaFiscal = null;

    #[Rule('nullable|string|max:50')]
    public ?string $BLC_Porte = null;

    #[Rule('nullable|exists:paises,id')]
    public ?int $Pais_origem = null;

    #[Rule('nullable|string|max:100')]
    public ?string $PortoOrigem = null;

    public ?string $porto_desembarque_id;

    #[Rule('nullable|exists:mercadoria_localizacaos,id')]
    public ?int $local_mercadoria_id = null;

    #[Rule('nullable|in:Tr,CK,RD,Ou')]
    public ?string $forma_pagamento = null;

    #[Rule('nullable|string|max:10')]
    public ?string $codigo_banco = null;

    #[Rule('nullable|exists:condicao_pagamentos,id')]
    public ?int $condicao_pagamento_id = null;

    #[Rule('nullable|string|max:500')]
    public ?string $observacoes = null;

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
        $empresa = Auth::user()->empresas->first();
        $this->clientes = $empresa->customers()->get();
        $this->customer_id = $clienteId;
        $this->dispatch('fecharModalCliente');
    }

    public function adicionarExportadorNaLista(int $exportadorId, $nome = null): void
    {
        $empresa = Auth::user()->empresas->first();
        $this->exportadores = $empresa->exportadors()->get();
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
        $this->customerId = $request->query('customer_id');

        $empresa = Auth::user()->empresas->first();

        if ($this->customerId) {
            $this->customer_id = $this->customerId;
            $this->clientes = Customer::where('id', $this->customerId)->get();
        } else {
            $this->clientes = $empresa->customers()->get();
        }

        // Preencher listas
        $this->exportadores = $empresa->exportadors()->orderBy('Exportador')->get();
        $this->estancias = Estancia::all();
        $this->paises = Pais::all();
        $this->portos = Porto::all();
        $this->listaBancos = BancoListService::getOptions();
        $this->tipoProcessoOptions = RegiaoAduaneira::all();
        $this->localMercadoria = MercadoriaLocalizacao::all();
        $this->formaPagamentoOptions = FormaPagamentoEnum::cases();
        $this->tipoTransporte = TipoTransporte::cases();
        $this->EstadoOptions = EstadoProcessoEnum::cases();
        $this->condicaoPagamentoOptions = CondicaoPagamento::all();

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
        $this->ValorAduaneiro = ($this->cif ?? 0) * ($this->Cambio ?? 1);
    }

    public function save(CriarProcessoAction $action)
    {
        $validated = $this->validate();

        $user = Auth::user();
        $empresa = $user->empresas->first();

        // Preparar dados para o DTO
        $data = array_merge($validated, [
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'ValorAduaneiro' => $this->ValorAduaneiro,
        ]);

        try {
            $processo = $action->execute(CriarProcessoDTO::fromArray($data));

            session()->flash('success', 'Processo criado com sucesso!');
            return redirect()->route('processos.show', $processo);
        } catch (\Exception $e) {
            dd($e->getMessage());
            session()->flash('error', 'Erro ao criar processo: ' . $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        return view('livewire.processo.processo-create');
    }
}