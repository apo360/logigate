<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Pais;
use Livewire\Component;
use App\Models\Importacao;
use App\Models\Mercadoria;
use App\Models\PautaAduaneira;
use App\Models\Processo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProcessoForm extends Component
{
    public $NrProcesso;
    public $ContaDespacho;
    public $CustomerID;
    public $RefCliente;
    public $DataAbertura;
    public $TipoProcesso;
    public $Situacao;
    public $Fk_pais;
    public $TipoTransporte;
    public $NomeTransporte;
    public $PortoOrigem;
    public $moeda;
    public $DataChegada;
    public $MarcaFiscal;
    public $BLC_Porte;
    public $ValorAduaneiro = 0.0;
    public $ValorTotal = 0.0;
    public $Descricao;
    public $mercadorias = [];
    
    public $clientes;
    public $paises;
    public $pauta;
    public $newCustomerCode;

    public bool $showDrawer1 = false;
    public bool $showDrawer2 = false;

    public function mount()
    {
        $this->clientes = Customer::all();
        $this->paises = Pais::all();
        $this->pauta = PautaAduaneira::all();
        $this->newCustomerCode = Customer::generateNewCode();
    }

    public function submit()
    {
        $this->validate([
            'ContaDespacho' => 'required|string',
            'customer_id' => 'required',
            'RefCliente' => 'required|string',
            'DataAbertura' => 'required|date',
            'TipoProcesso' => 'required|string',
            'Situacao' => 'required|string',
            'Fk_pais' => 'required',
            'TipoTransporte' => 'required|string',
            'NomeTransporte' => 'required|string',
            'PortoOrigem' => 'required|string',
            'moeda' => 'required|string',
            'DataChegada' => 'nullable|date',
            'MarcaFiscal' => 'nullable|string',
            'BLC_Porte' => 'nullable|string',
            'ValorAduaneiro' => 'required|numeric',
            'ValorTotal' => 'required|numeric',
            'Descricao' => 'nullable|string',
            'mercadorias' => 'array',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $empresaId = $user->empresas->id;

            // Cria o processo
            $processo = Processo::create([
                'NrProcesso' => $this->NrProcesso,
                'ContaDespacho' => $this->ContaDespacho,
                'customer_id' => $this->CustomerID,
                'RefCliente' => $this->RefCliente,
                'DataAbertura' => $this->DataAbertura,
                'TipoProcesso' => $this->TipoProcesso,
                'Situacao' => $this->Situacao,
                'Descricao' => $this->Descricao,
                'user_id' => $user->id,
                'empresa_id' => $empresaId,
            ]);

            // Cria a importação
            $importacao = Importacao::create([
                'Fk_processo' => $processo->id,
                'Fk_pais' => $this->Fk_pais,
                'PortoOrigem' => $this->PortoOrigem,
                'TipoTransporte' => $this->TipoTransporte,
                'NomeTransporte' => $this->NomeTransporte,
                'DataChegada' => $this->DataChegada,
                'MarcaFiscal' => $this->MarcaFiscal,
                'BLC_Porte' => $this->BLC_Porte,
                'Moeda' => $this->moeda,
                'ValorAduaneiro' => $this->ValorAduaneiro,
                'ValorTotal' => $this->ValorTotal,
            ]);

            // Criar Mercadoria
            foreach ($this->mercadorias as $mercadoriaData) {
                Mercadoria::create([
                    'Fk_Importacao' => $importacao->id,
                    'Descricao' => $mercadoriaData['Descricao'],
                    'NCM_HS' => $mercadoriaData['NCM_HS'],
                    'NCM_HS_Numero' => $mercadoriaData['NCM_HS_Numero'],
                    'Quantidade' => $mercadoriaData['Quantidade'],
                    'Qualificacao' => $mercadoriaData['Qualificacao'],
                    'Unidade' => 'Kg',
                    'Peso' => $mercadoriaData['Peso'],
                ]);
            }

            DB::commit();

            session()->flash('message', 'Processo inserido com sucesso!');
            return redirect()->route('processos.index');
        
        } catch (QueryException $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao inserir processo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.processo-form');
    }
}
