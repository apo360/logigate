<?php

namespace App\Livewire\Licenciamento;

use Livewire\Component;
use App\Application\Licenciamento\Actions\CriarLicenciamentoAction;
use App\Application\Licenciamento\DTOs\CriarLicenciamentoDTO;
use App\Application\Licenciamento\Support\LicenciamentoFormSupport;
use App\Models\Empresa;
use App\Models\Licenciamento;
use App\Domains\Licenciamento\Services\CalcularCifLicenciamentoService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LiicenciamentoCreate extends Component
{
    use AuthorizesRequests;

    // customer_id vindo da URL
    public ?int $customerId = null;

    // Campos do formulário
    public $cliente_id;

    public $exportador_id;

    public $estancia_id;

    public $tipo_declaracao = '11';

    public $referencia_cliente = '';

    public $factura_proforma = '';

    public $descricao = '';

    public $moeda = 'AOA';

    public $cambio = 'USD';

    public $tipo_transporte = '3';

    public $registo_transporte = '';
    public $nacionalidade_transporte;
    public $manifesto = '';
    public $data_entrada = '';
    public $porto_entrada = '';
    public $peso_bruto = 0;
    public $adicoes = 0;
    
    public $metodo_avaliacao = 'GATT';

    public $codigo_volume = 'B';

    public $qntd_volume = 1;

    public $forma_pagamento = 'RD';

    public $listaBancos = [];
    public $codigo_banco = '';
    
    public $fob_total = 0;

    public $frete = 0;

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
        $this->exportadores = app(LicenciamentoFormSupport::class)->options($this->empresa())['exportadores'];
        $this->exportador_id = $exportadorId;
        $this->dispatch('fecharModalExportador');
    }

    public function adicionarClienteNaLista($clienteId, $nome = null)
    {
        $this->clientes = app(LicenciamentoFormSupport::class)->options($this->empresa())['clientes'];
        $this->cliente_id = $clienteId;
        $this->dispatch('fecharModalCliente');
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
        $this->authorize('create', Licenciamento::class);

        // apenas para debug
        Log::info('Mount executado', [
            'customer_id' => $request->query('customer_id'),
            'user_id' => Auth::id(),
            'empresa' => Auth::user()->empresas->first()->id ?? null,
        ]);

        $this->customerId = $request->query('customer_id');
        
        $empresa = $this->empresa();
        $options = app(LicenciamentoFormSupport::class)->options($empresa);
        
        if ($this->customerId) {
            abort_unless($options['clientes']->contains('id', (int) $this->customerId), 404);
            $this->cliente_id = $this->customerId;
        }

        foreach ($options as $property => $value) {
            $this->{$property} = $value;
        }

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
        $this->authorize('create', Licenciamento::class);

        $this->validate();

        $empresaId = $this->empresa()->id;

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

    public function rules(): array
    {
        return app(LicenciamentoFormSupport::class)->rules($this->empresa()->id);
    }

    private function empresa(): Empresa
    {
        $empresa = Auth::user()?->empresas()->first();
        abort_if(!$empresa, 403, 'Nenhuma empresa associada ao usuário autenticado.');

        return $empresa;
    }
}
