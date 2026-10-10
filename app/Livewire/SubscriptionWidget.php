<?php

namespace App\Livewire;

use App\Livewire\Concerns\RequiresActiveEmpresa;
use App\Models\Subscricao;
use App\Support\TenantContext;
use Carbon\Carbon;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SubscriptionWidget extends Component
{
    use RequiresActiveEmpresa;

    #[Locked]
    public ?Subscricao $subscricao = null;

    #[Locked]
    public ?string $checkoutConta = null;

    protected $listeners = ['subscricaoAtualizada' => 'carregarDados'];

    public function mount()
    {
        $this->carregarDados();
    }

    public function carregarDados()
    {
        unset($this->dataInicio, $this->dataExpiracao, $this->diasRestantes, $this->percentualRestante, $this->expirada);
        $empresa = TenantContext::empresa();
        $this->checkoutConta = $empresa?->conta;

        // Always prefer the active record, but safely fall back to the latest one
        // so pending subscriptions can still render without crashing the widget.
        $this->subscricao = $empresa?->subscricoes()
            ->with('plano')
            ->orderByRaw("CASE WHEN LOWER(status) = 'ativa' THEN 0 ELSE 1 END")
            ->latest('data_expiracao')
            ->latest('id')
            ->first();
    }

    public function getDataInicioProperty(): ?Carbon
    {
        return $this->subscricao?->data_inicio
            ? Carbon::parse($this->subscricao->data_inicio)
            : null;
    }

    public function getDataExpiracaoProperty(): ?Carbon
    {
        return $this->subscricao?->data_expiracao
            ? Carbon::parse($this->subscricao->data_expiracao)
            : null;
    }

    public function getExpiradaProperty(): bool
    {
        return $this->dataExpiracao !== null && $this->dataExpiracao->lessThanOrEqualTo(now());
    }

    public function getDiasRestantesProperty(): ?int
    {
        if (! $this->dataExpiracao) {
            return null;
        }

        $days = now()->diffInDays($this->dataExpiracao, false);

        return (int) ($days > 0 ? ceil($days) : floor($days));
    }

    public function getPercentualRestanteProperty(): int
    {
        if (! $this->dataInicio || ! $this->dataExpiracao) {
            return 0;
        }

        $totalSeconds = $this->dataInicio->diffInSeconds($this->dataExpiracao, false);
        if ($totalSeconds <= 0) {
            return 0;
        }
        $remaining = now()->diffInSeconds($this->dataExpiracao, false);

        return (int) round(max(0, min(100, ($remaining / $totalSeconds) * 100)));
    }

    public function renovar()
    {
        return redirect()->route('billing.plans');
    }

    public function checkout()
    {
        $conta = TenantContext::empresa()?->conta;
        if (! $conta) {
            return redirect()->route('billing.plans');
        }

        return redirect()->route('checkout', ['conta' => $conta]);
    }

    public function render()
    {
        return view('livewire.subscription-widget');
    }
}
