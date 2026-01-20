<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Plano;
use App\Models\Module;
use App\Models\Empresa;
use App\Models\Subscricao;
use App\Models\Pagamento;
use App\Models\MetodoPagamento;
use Illuminate\Support\Facades\Auth;

class SubscriptionWizard extends Component
{
    public ?int $empresaId = null;
    public ?int $subscricaoExistenteId = null;

    public $step = 1;
    public $empresa;
    public $subscricaoExistente = null;
    
    // Passo 1: Plano
    public $planoSelecionado;
    public $modalidade = 'mensal';
    
    // Passo 2: Módulos
    public $modulosSelecionados = [];
    public array $modulosDisponiveis = [];
    public $modulosAdicionais = [];
    
    // Passo 3: Extras
    public $usuariosExtras = 0;
    public $armazenamentoExtra = 0;
    public $precoUsuarioExtra = 500;
    public $precoGBExtra = 100;
    
    // Passo 4: Pagamento
    public $metodoPagamento = 'multicaixa';
    public $aceitaTermos = false;
     // Adicione esta propriedade para controlar o botão:
    public $canFinalize = false;
    
    // Calculados
    public $resumo = [];
    public $total = 0;
    
    protected $listeners = ['proximoPasso', 'passoAnterior', 'finalizarSubscricao'];

    public function mount()
    {
        $empresa = Auth::user()->empresas->first();

        $this->empresaId = $empresa?->id;
        $this->subscricaoExistenteId = $empresa?->subscricaoAtiva?->id;


        // Converter para array simples
        $this->modulosDisponiveis = Module::select('id', 'module_name', 'price', 'description')
            ->get()
            ->toArray();

        // Inicializar método de pagamento com valor padrão
        $this->metodoPagamento = 'multicaixa';
        $this->aceitaTermos = false;
    }

     /**
     * Alterna um módulo na lista de selecionados
     */
    public function toggleModulo($moduloId)
    {
        // Converter para inteiro para garantir comparação correta
        $moduloId = (int) $moduloId;
        
        // Se já está selecionado, remove
        if (in_array($moduloId, $this->modulosSelecionados)) {
            $this->modulosSelecionados = array_values(
                array_diff($this->modulosSelecionados, [$moduloId])
            );
        } 
        // Se não está selecionado, adiciona
        else {
            $this->modulosSelecionados[] = $moduloId;
            // Garantir valores únicos
            $this->modulosSelecionados = array_unique($this->modulosSelecionados);
        }
        
        // Recalcular total após alteração
        $this->calcularTotal();
    }

    /**
     * Alternativa: Método para marcar/desmarcar todos
     */
    public function toggleAllModulos()
    {
        if (empty($this->modulosDisponiveis)) return;
        
        $allIds = collect($this->modulosDisponiveis)->pluck('id')->toArray();
        
        if (count($this->modulosSelecionados) === count($allIds)) {
            // Se todos estão selecionados, desmarca todos
            $this->modulosSelecionados = [];
        } else {
            // Seleciona todos
            $this->modulosSelecionados = $allIds;
        }
        
        $this->calcularTotal();
    }
    
    /**
     * Verifica se um módulo está selecionado
     */
    public function moduloEstaSelecionado($moduloId)
    {
        return in_array((int) $moduloId, $this->modulosSelecionados);
    }
    
    /**
     * Método para lidar com cliques em checkboxes
     */
    public function updatedModulosSelecionados($value)
    {
        // Este método é automaticamente chamado quando $modulosSelecionados muda
        $this->calcularTotal();
    }

     /* =========================================
       👉 COMPUTED PROPERTIES (AQUI 👇)
       ========================================= */

    public function getEmpresaProperty(): ?Empresa
    {
        return $this->empresaId
            ? Empresa::find($this->empresaId)
            : null;
    }

    public function getSubscricaoExistenteProperty(): ?Subscricao
    {
        return $this->subscricaoExistenteId
            ? Subscricao::find($this->subscricaoExistenteId)
            : null;
    }

    // Adicione este método para verificar se pode finalizar
    public function updated($propertyName)
    {
        // Atualizar canFinalize quando termos ou método mudar
        if ($propertyName === 'aceitaTermos' || $propertyName === 'metodoPagamento') {
            $this->canFinalize = $this->aceitaTermos && !empty($this->metodoPagamento);
        }
    }
    
    public function calcularTotal()
    {
        if (!$this->planoSelecionado) return;
        
        $plano = Plano::find($this->planoSelecionado);
        $precoPlano = $plano->getPreco($this->modalidade);
        
        $this->resumo = [
            'plano' => [
                'nome' => $plano->nome,
                'preco' => $precoPlano,
                'modalidade' => $this->modalidade,
                'usuarios_base' => $plano->limite_utilizadores,
                'armazenamento_base' => $plano->limite_armazenamento_gb
            ]
        ];
        
        $this->total = $precoPlano;
        
        // Módulos adicionais
        if (!empty($this->modulosSelecionados)) {
            $modulos = Module::whereIn('id', $this->modulosSelecionados)->get();
            
            foreach ($modulos as $modulo) {
                $this->resumo['modulos'][] = [
                    'nome' => $modulo->module_name,
                    'preco' => $modulo->price
                ];
                
                $this->total += $modulo->price;
            }
        }
        
        // Adicionar usuários extras
        if ($this->usuariosExtras > 0) {
            $precoPorUsuario = 500; // Kz por usuário/mês
            $preco = $this->usuariosExtras * $precoPorUsuario;
            if ($this->modalidade === 'anual') {
                $preco *= 12 * 0.9; // 10% desconto anual
            }
            
            $this->resumo['usuarios_extra'] = [
                'quantidade' => $this->usuariosExtras,
                'preco' => $preco
            ];
            
            $this->total += $preco;
        }
        
        // Adicionar armazenamento extra
        if ($this->armazenamentoExtra > 0) {
            $precoPorGB = 100; // Kz por GB/mês
            $preco = $this->armazenamentoExtra * $precoPorGB;
            if ($this->modalidade === 'anual') {
                $preco *= 12 * 0.9; // 10% desconto anual
            }
            
            $this->resumo['armazenamento_extra'] = [
                'gb' => $this->armazenamentoExtra,
                'preco' => $preco
            ];
            
            $this->total += $preco;
        }
    }
    
    public function proximoPasso()
    {
        $this->validateStep();
        $this->step++;
        
        if ($this->step === 2 || $this->step === 3) {
            $this->calcularTotal();
        }
    }
    
    public function passoAnterior()
    {
        $this->step--;
    }
    
    public function validateStep()
    {
        if ($this->step === 1) {
            $this->validate([
                'planoSelecionado' => 'required|exists:planos,id',
                'modalidade' => 'required|in:mensal,anual'
            ]);
        }

        if ($this->step === 3) {
            $this->validate([
                'metodoPagamento' => 'required|exists:metodos_pagamento,id',
                'aceitaTermos' => 'accepted'
            ]);
        }
    }
    
    public function finalizarSubscricao()
    {
       // Validação
        $this->validate([
            'aceitaTermos' => 'accepted',
            'metodoPagamento' => 'required'
        ], [
            'aceitaTermos.accepted' => 'Deve aceitar os termos e condições',
            'metodoPagamento.required' => 'Selecione um método de pagamento'
        ]);
        
        // Criar subscrição
        $subscricao = Subscricao::create([
            'empresa_id' => $this->empresa->id,
            'plano_id' => $this->planoSelecionado,
            'tipo_plano' => Plano::find($this->planoSelecionado)->nome,
            'modalidade_pagamento' => $this->modalidade,
            'data_subscricao' => now(),
            'data_inicio' => now(),
            'data_expiracao' => now()->addMonth(), // Será ajustado após pagamento
            'status' => Subscricao::STATUS_PENDENTE,
            'valor_pago' => $this->total,
            'dados_personalizados' => [
                'modulos_extra' => $this->modulosSelecionados,
                'usuarios_extra' => $this->usuariosExtras,
                'armazenamento_extra' => $this->armazenamentoExtra
            ],
            'created_by' => Auth::id()
        ]);
        
        // Criar pagamento
        $pagamento = Pagamento::create([
            'empresa_id' => $this->empresa->id,
            'subscricao_id' => $subscricao->id,
            'referencia' => $this->gerarReferencia(),
            'valor' => $this->total,
            'metodo_pagamento' => $this->metodoPagamento,
            'data_expiracao' => now()->addDays(3),
            'status' => 'pendente'
        ]);
        
        // Redirecionar para página de referência
        return redirect()->route('pagamento.referencia', $pagamento->id);
    }
    
    private function gerarReferencia()
    {
        return 'REF' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    public function incrementarUsuarios()
    {
        $this->usuariosExtras++;
    }

    public function decrementarUsuarios()
    {
        if ($this->usuariosExtras > 0) {
            $this->usuariosExtras--;
        }
    }

    public function incrementarArmazenamento()
    {
        $this->armazenamentoExtra++;
    }

    public function decrementarArmazenamento()
    {
        if ($this->armazenamentoExtra > 0) {
            $this->armazenamentoExtra--;
        }
    }

    
    public function render()
    {
        return view('livewire.subscription-wizard', [
            'planos' => Plano::where('status', 'activo')
                ->select('id', 'nome', 'preco_mensal', 'preco_anual')
                ->get(),
            'modulos' => $this->modulosDisponiveis,
            'metodosPagamento' => MetodoPagamento::all()
        ]);

    }
}