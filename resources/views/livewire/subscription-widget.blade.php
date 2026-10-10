<div class="flex h-14 max-w-sm items-center gap-2 rounded-lg border border-gray-200 bg-white px-2 dark:border-gray-700 dark:bg-gray-900" aria-label="Estado da subscrição">
    @php
        $dias = $this->diasRestantes;
        $percent = $this->percentualRestante;
        $pendente = $subscricao?->hasStatus(\App\Models\Subscricao::STATUS_PENDENTE);
        $ativa = $subscricao?->hasStatus(\App\Models\Subscricao::STATUS_ATIVA);
    @endphp
    <div class="relative h-10 w-10 shrink-0" aria-hidden="true">
        <svg class="h-full w-full -rotate-90" viewBox="0 0 36 36"><circle cx="18" cy="18" r="15.9155" fill="none" stroke="currentColor" stroke-width="3" class="text-gray-200 dark:text-gray-700"/><circle cx="18" cy="18" r="15.9155" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $ativa ? $percent : 0 }}, 100" class="text-logigate-primary"/></svg>
        <span class="absolute inset-0 flex items-center justify-center text-[10px] font-semibold text-gray-700 dark:text-gray-200">{{ $ativa && $dias !== null ? ($this->expirada ? 'Exp' : $dias.'d') : '—' }}</span>
    </div>
    <div class="min-w-0 text-xs">
        <p class="max-w-40 truncate font-semibold text-gray-800 dark:text-gray-100" title="{{ $subscricao?->plano?->nome ?? 'Sem subscrição' }}">{{ $subscricao?->plano?->nome ?? 'Sem subscrição' }}</p>
        @if(!$subscricao)
            <span class="text-gray-500 dark:text-gray-400">Nenhuma subscrição activa</span>
        @elseif($pendente)
            <span class="text-amber-700 dark:text-amber-400">Pagamento pendente</span>
        @elseif($subscricao->hasStatus(\App\Models\Subscricao::STATUS_CANCELADA))
            <span class="text-gray-500 dark:text-gray-400">Cancelada</span>
        @elseif($subscricao->hasStatus(\App\Models\Subscricao::STATUS_SUSPENSA))
            <span class="text-amber-700 dark:text-amber-400">Suspensa</span>
        @elseif($this->expirada || $subscricao->hasStatus(\App\Models\Subscricao::STATUS_EXPIRADA))
            <span class="text-red-600 dark:text-red-400">Expirada</span>
        @elseif($this->dataExpiracao)
            <span class="text-gray-500 dark:text-gray-400">Expira {{ $this->dataExpiracao->format('d/m/Y') }}</span>
        @else
            <span class="text-gray-500 dark:text-gray-400">Sem data de expiração</span>
        @endif
    </div>
    @if($pendente)
        <button type="button" wire:click="checkout" wire:loading.attr="disabled" class="min-h-10 shrink-0 rounded px-2 text-xs font-semibold text-logigate-primary focus-visible:ring-2 focus-visible:ring-logigate-primary">Pagar</button>
    @elseif(!$subscricao || $this->expirada || !$ativa)
        <button type="button" wire:click="renovar" wire:loading.attr="disabled" class="min-h-10 shrink-0 rounded px-2 text-xs font-semibold text-logigate-primary focus-visible:ring-2 focus-visible:ring-logigate-primary">Ver planos</button>
    @endif
</div>