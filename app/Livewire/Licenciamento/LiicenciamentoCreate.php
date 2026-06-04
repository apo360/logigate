<?php

namespace App\Livewire\Licenciamento;

use Livewire\Component;
use App\Application\Licenciamento\Actions\CriarLicenciamentoAction;
use App\Application\Licenciamento\DTOs\CriarLicenciamentoDTO;
use App\Domains\Banco\Services\BancoListService;
use App\Models\Customer;
use App\Models\Exportador;
use App\Models\Estancia;
use App\Models\Pais;
use App\Models\Porto;
use App\Domains\Licenciamento\Enums\TipoDeclaracao;
use App\Domains\Licenciamento\Enums\TipoTransporte;
use App\Domains\Licenciamento\Enums\MetodoAvaliacao;
use App\Domains\Licenciamento\Services\CalcularCifLicenciamentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;

class LiicenciamentoCreate extends Component
{
    // customer_id vindo da URL
    public ?int $customerId = null;

    // Campos do formulário
    #[Rule('required|exists:customers,id')]
    public $cliente_id;

    #[Rule('required|exists:exportadors,id')]
    public $exportador_id;

    #[Rule('required|exists:estancias,id')]
    public $estancia_id;

    #[Rule('required|in:11,21')]
    public $tipo_declaracao = '11';

    #[Rule('required|string')]
    public $referencia_cliente = '';

    #[Rule('required|string')]
    public $factura_proforma = '';

    #[Rule('required|string')]
    public $descricao = '';

    #[Rule('required|string|size:3')]
    public $moeda = 'AOA';

    #[Rule('required|string|size:3')]
    public $cambio = 'USD';

    #[Rule('required|in:1,2,3,4,5,6,7,8')]
    public $tipo_transporte = '3';

    public $registo_transporte = '';
    public $nacionalidade_transporte;
    public $manifesto = '';
    public $data_entrada = '';
    public $porto_entrada = '';
    public $peso_bruto = 0;
    public $adicoes = 0;
    
    #[Rule('required|in:GATT,Outro')]
    public $metodo_avaliacao = 'GATT';

    #[Rule('required|in:B,F,G,L,N')]
    public $codigo_volume = 'B';

    #[Rule('required|integer|min:1')]
    public $qntd_volume = 1;

    #[Rule('required|string')]
    public $forma_pagamento = 'RD';

    public $listaBancos = [];
    public $codigo_banco = '';
    
    #[Rule('required|numeric|min:0')]
    public $fob_total = 0;

    #[Rule('numeric|min:0')]
    public $frete = 0;

    #[Rule('numeric|min:0')]
    public $seguro = 0;

    public $cif = 0;

    public $pais_origem;
    public $porto_origem = '';
    public $Nr_factura = '';
    public $status_fatura = '';

    // Para listas
    public $clientes = [];
    public $exportadores = [];
    public $estancias = [];
    public $paises = [];
    public $portos = [];

    protected $listeners = [
        'clienteCriado' => 'adicionarClienteNaLista',
        'exportadorCriado' => 'adicionarExportadorNaLista',
    ];

    public function adicionarExportadorNaLista($exportadorId, $nome)
    {
        $empresa = Auth::user()->empresas->first();
        $this->exportadores = $empresa->exportadors()->get();
        $this->exportador_id = $exportadorId;
        $this->dispatch('fecharModalExportador');
    }

    // Métodos para abrir os modais
    public function abrirModalCliente()
    {
        $this->dispatch('abrirModalCliente');
    }

    public function abrirModalExportador()
    {
        $this->dispatch('abrirModalExportador');
    }

    public function mount(Request $request)
    {
        // apenas para debug
        Log::info('Mount executado', [
            'customer_id' => $request->query('customer_id'),
            'user_id' => Auth::id(),
            'empresa' => Auth::user()->empresas->first()->id ?? null,
        ]);

        $this->customerId = $request->query('customer_id');
        
        $empresa = Auth::user()->empresas->first(); // Ajuste conforme sua lógica
        
        if ($this->customerId) {
            $this->cliente_id = $this->customerId;
            $this->clientes = Customer::where('id', $this->customerId)->get();
        } else {
            $this->clientes = $empresa->customers()->get();
        }

        $this->exportadores = $empresa->exportadors()->get();
        $this->estancias = Estancia::all();
        $this->paises = Pais::all();
        $this->portos = Porto::all();
        $this->listaBancos = BancoListService::getOptions();

        // Valor padrão para nacionalidade
        $this->nacionalidade_transporte = 16; // Exemplo: Angola, ajuste conforme necessário
    }

    public function updated($field)
    {
        if (in_array($field, ['fob_total', 'frete', 'seguro']) && !$this->cifManuallyChanged) {
            $this->cif = app(CalcularCifLicenciamentoService::class)
                ->calcular((float) $this->fob_total, (float) $this->frete, (float) $this->seguro)
                ->getValor();
        }
    }

    public bool $cifManuallyChanged = false;

    public function updatedCif()
    {
        $this->cifManuallyChanged = true;
    }

    public function create(CriarLicenciamentoAction $action)
    {
        $this->validate();

        $empresaId = Auth::user()->empresas->first()->id;

        // Montar array de dados para o DTO
        $data = [
            'estancia_id' => $this->estancia_id,
            'cliente_id' => $this->cliente_id,
            'exportador_id' => $this->exportador_id,
            'empresa_id' => $empresaId,
            'referencia_cliente' => $this->referencia_cliente,
            'factura_proforma' => $this->factura_proforma,
            'descricao' => $this->descricao,
            'moeda' => $this->moeda,
            'tipo_declaracao' => $this->tipo_declaracao,
            'tipo_transporte' => $this->tipo_transporte,
            'registo_transporte' => $this->registo_transporte,
            'nacionalidade_transporte' => $this->nacionalidade_transporte,
            'manifesto' => $this->manifesto,
            'data_entrada' => $this->data_entrada,
            'porto_entrada' => $this->porto_entrada,
            'peso_bruto' => $this->peso_bruto,
            'adicoes' => $this->adicoes,
            'metodo_avaliacao' => $this->metodo_avaliacao,
            'codigo_volume' => $this->codigo_volume,
            'qntd_volume' => $this->qntd_volume,
            'forma_pagamento' => $this->forma_pagamento,
            'codigo_banco' => $this->codigo_banco,
            'fob_total' => $this->fob_total,
            'frete' => $this->frete,
            'seguro' => $this->seguro,
            'cif' => $this->cif,
            'pais_origem' => $this->pais_origem,
            'porto_origem' => $this->porto_origem,
            'Nr_factura' => $this->Nr_factura,
            'status_fatura' => 'pendente',
        ];

        $dto = new CriarLicenciamentoDTO($data);
        $licenciamento = $action->execute($dto);

        session()->flash('success', 'Licenciamento criado com sucesso!');
        return redirect()->route('licenciamentos.show', $licenciamento);
    }

    public function render()
    {
        return view('livewire.licenciamento.liicenciamento-create');
    }
}
