<?php

namespace App\Livewire\Licenciamento;

use App\Application\Licenciamento\Actions\AtualizarLicenciamentoAction;
use App\Application\Licenciamento\DTOs\AtualizarLicenciamentoDTO;
use App\Models\Licenciamento;
use App\Domains\Licenciamento\Enums\TipoDeclaracao;
use App\Domains\Licenciamento\Enums\TipoTransporte;
use App\Domains\Licenciamento\Enums\MetodoAvaliacao;
use App\Domains\Licenciamento\Services\CalcularCifLicenciamentoService;
use App\Domains\Banco\Services\BancoListService;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LiicenciamentoEdit extends Component
{
    public Licenciamento $licenciamento;

    // Campos do formulário (mesmos do create)
    #[Rule('required|exists:customers,id')]
    public $cliente_id;

    #[Rule('required|exists:exportadors,id')]
    public $exportador_id;

    #[Rule('required|exists:estancias,id')]
    public $estancia_id;

    #[Rule('required|in:11,21')]
    public $tipo_declaracao;

    #[Rule('required|string')]
    public $referencia_cliente;

    #[Rule('required|string')]
    public $factura_proforma;

    #[Rule('required|string')]
    public $descricao;

    #[Rule('required|string|size:3')]
    public $moeda;

    #[Rule('required|in:1,2,3,4,5,6,7,8')]
    public $tipo_transporte;

    public $registo_transporte;
    public $nacionalidade_transporte;
    public $manifesto;
    public $data_entrada;
    public $porto_entrada;
    public $peso_bruto;
    public $adicoes;

    #[Rule('required|in:GATT,Outro')]
    public $metodo_avaliacao;

    #[Rule('required|in:B,F,G,L,N')]
    public $codigo_volume;

    #[Rule('required|integer|min:1')]
    public $qntd_volume;

    #[Rule('required|string')]
    public $forma_pagamento;

    public $codigo_banco;

    #[Rule('required|numeric|min:0')]
    public $fob_total;

    #[Rule('numeric|min:0')]
    public $frete;

    #[Rule('numeric|min:0')]
    public $seguro;

    public $cif; // não obrigatório, pode ser calculado

    public $pais_origem;
    public $porto_origem;
    public $Nr_factura;
    public $status_fatura;

    // Auxiliares
    public $listaBancos = [];
    public $cifManuallyChanged = false;

    public function mount(Licenciamento $licenciamento)
    {
        $this->licenciamento = $licenciamento;
        
        // Preencher as propriedades com os dados atuais
        $this->cliente_id = $licenciamento->cliente_id;
        $this->exportador_id = $licenciamento->exportador_id;
        $this->estancia_id = $licenciamento->estancia_id;
        $this->tipo_declaracao = $licenciamento->tipo_declaracao;
        $this->referencia_cliente = $licenciamento->referencia_cliente;
        $this->factura_proforma = $licenciamento->factura_proforma;
        $this->descricao = $licenciamento->descricao;
        $this->moeda = $licenciamento->moeda;
        $this->tipo_transporte = $licenciamento->tipo_transporte;
        $this->registo_transporte = $licenciamento->registo_transporte;
        $this->nacionalidade_transporte = $licenciamento->nacionalidade_transporte;
        $this->manifesto = $licenciamento->manifesto;
        $this->data_entrada = $licenciamento->data_entrada;
        $this->porto_entrada = $licenciamento->porto_entrada;
        $this->peso_bruto = $licenciamento->peso_bruto;
        $this->adicoes = $licenciamento->adicoes;
        $this->metodo_avaliacao = $licenciamento->metodo_avaliacao;
        $this->codigo_volume = $licenciamento->codigo_volume;
        $this->qntd_volume = $licenciamento->qntd_volume;
        $this->forma_pagamento = $licenciamento->forma_pagamento;
        $this->codigo_banco = $licenciamento->codigo_banco;
        $this->fob_total = $licenciamento->fob_total;
        $this->frete = $licenciamento->frete;
        $this->seguro = $licenciamento->seguro;
        $this->cif = $licenciamento->cif;
        $this->pais_origem = $licenciamento->pais_origem;
        $this->porto_origem = $licenciamento->porto_origem;
        $this->Nr_factura = $licenciamento->Nr_factura;
        $this->status_fatura = $licenciamento->status_fatura;

        $this->listaBancos = BancoListService::getOptions();
    }

    public function updated($field)
    {
        if (in_array($field, ['fob_total', 'frete', 'seguro']) && !$this->cifManuallyChanged) {
            $this->cif = app(CalcularCifLicenciamentoService::class)
                ->calcular((float) $this->fob_total, (float) $this->frete, (float) $this->seguro)
                ->getValor();
        }
    }

    public function updatedCif()
    {
        $this->cifManuallyChanged = true;
    }

    public function update(AtualizarLicenciamentoAction $action)
    {
        $this->validate();

        $dto = new AtualizarLicenciamentoDTO([
            'id' => $this->licenciamento->id,
            'cliente_id' => $this->cliente_id,
            'exportador_id' => $this->exportador_id,
            'estancia_id' => $this->estancia_id,
            'tipo_declaracao' => $this->tipo_declaracao,
            'referencia_cliente' => $this->referencia_cliente,
            'factura_proforma' => $this->factura_proforma,
            'descricao' => $this->descricao,
            'moeda' => $this->moeda,
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
            'status_fatura' => $this->status_fatura,
        ]);

        $licenciamento = $action->execute($dto);

        session()->flash('success', 'Licenciamento atualizado com sucesso!');
        return redirect()->route('licenciamentos.show', $licenciamento);
    }

    public function render()
    {
        // Carregar listas para selects
        $empresa = Auth::user()->empresas->first();
        $clientes = $empresa->customers()->get();
        $exportadores = $empresa->exportadors()->get();
        $estancias = \App\Models\Estancia::all();
        $paises = \App\Models\Pais::all();
        $portos = \App\Models\Porto::all();

        return view('livewire.licenciamento.liicenciamento-edit', [
            'clientes' => $clientes,
            'exportadores' => $exportadores,
            'estancias' => $estancias,
            'paises' => $paises,
            'portos' => $portos,
        ]);
    }
}
