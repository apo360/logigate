<div class="space-y-5">
    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
        <h2 class="font-semibold text-blue-950">GPO aguardando autorizacao</h2>
        <p class="mt-1 text-sm text-blue-800">
            Autorize o pagamento no MCX App para o numero +244 {{ $phone }}.
        </p>
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <div class="text-sm text-slate-600">Transacao</div>
        <div class="mt-1 font-mono text-sm font-semibold text-slate-900">{{ $merchantTransactionId }}</div>
    </div>

    @if($paymentStatus === 'failed' || $paymentStatus === 'expired')
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            O pedido GPO nao foi confirmado. Pode tentar novamente ou gerar uma referencia Multicaixa.
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="button" wire:click="retryGpo" class="rounded-lg bg-blue-700 px-4 py-2 font-semibold text-white hover:bg-blue-800">
                Tentar GPO
            </button>
            <button type="button" wire:click="switchToRef" class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-800 hover:bg-slate-50">
                Usar referencia
            </button>
        </div>
    @else
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
            Aguardando confirmacao automatica.
        </div>
    @endif
</div>
