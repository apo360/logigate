<?php

namespace App\Livewire;

use App\Models\Subscricao;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class SubscriptionWidget extends Component
{
    public $empresa;
    public $subscricao;
    public ?int $subscricaoId = null;
    
    protected $listeners = ['subscricaoAtualizada' => 'carregarDados'];
    
    public function mount()
    {
        $this->carregarDados();
    }
    
    public function carregarDados()
    {
        $empresa = Auth::user()->empresas->first();
        
        $this->subscricao = $empresa?->subscricoes()->latest('data_expiracao')->with('plano')->first();
    }

    public function getDataInicioProperty(): Carbon
    {
        return $this->subscricao?->data_inicio
            ? Carbon::parse($this->subscricao->data_inicio)
            : now();
    }

    public function getDataExpiracaoProperty(): Carbon
    {
        return $this->subscricao?->data_expiracao
            ? Carbon::parse($this->subscricao->data_expiracao)
            : now();
    }

    /* Computed property */
    public function getSubscricaoProperty(): ?Subscricao
    {
        return $this->subscricaoId
            ? Subscricao::with('plano')->find($this->subscricaoId)
            : null;
    }

    public function getDiasRestantesProperty(): ?int
    {
        if (!$this->subscricao) {
            return null;
        }

        return now()->diffInDays($this->dataExpiracao, false);
    }


    public function getPercentualRestanteProperty(): int
    {
        if (!$this->subscricao) {
            return 0;
        }

        $totalDays = max(
            $this->dataInicio->diffInDays($this->dataExpiracao),
            1
        );

        $diasRestantes = max($this->diasRestantes ?? 0, 0);

        return (int) round(($diasRestantes / $totalDays) * 100);
    }

    
    public function renovar()
    {
        return redirect()->route('subscribe.view', auth()->user()->empresas->first()->id);
    }
    
    public function render()
    {
        return view('livewire.subscription-widget');
    }
}
