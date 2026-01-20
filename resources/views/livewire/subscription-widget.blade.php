<!-- resources/views/livewire/subscription-widget.blade.php -->
<div class="flex items-center gap-4 bg-white dark:bg-gray-900 p-4 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-800"
     x-data="{ percent: 0 }"
    x-init="setTimeout(() => percent = {{ $this->percentualRestante }}, 300)">

    <!-- PIE -->
    <div class="relative"
         :class="{
            'w-16 h-16': true,
            'animate-[pulse-glow_3s_ease-in-out_infinite]': percent < 20
         }">

        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
            <!-- trilho -->
            <path class="text-gray-300 dark:text-gray-700"
                  stroke-width="3"
                  stroke="currentColor"
                  fill="none"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>

            <!-- gradiente -->
            <defs>
                <linearGradient id="pieGradient" x1="1" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#2fe6a7"/>
                    <stop offset="50%" stop-color="#16b0f8"/>
                    <stop offset="100%" stop-color="#8752ff"/>
                </linearGradient>
            </defs>

            <!-- progresso -->
            <path stroke-width="3"
                  stroke-linecap="round"
                  fill="none"
                  stroke="url(#pieGradient)"
                  :stroke-dasharray="percent + ', 100'"
                  class="transition-all duration-[1200ms] ease-out"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
        </svg>

        <!-- texto centro -->
        <div class="absolute inset-0 flex flex-col items-center justify-center">
            @if($subscricao && $this->diasRestantes >= 0)
                <span class="text-sm font-bold {{ $diasRestantes < 7 ? 'text-red-600' : 'text-gray-800 dark:text-gray-200' }}">
                    {{ $this->diasRestantes }}d
                </span>
                <span class="text-[10px] text-gray-400">left</span>
            @elseif($subscricao)
                <span class="text-base font-extrabold text-red-600 animate-pulse">Exp</span>
            @else
                <span class="text-sm font-bold text-gray-400">---</span>
            @endif
        </div>
    </div>

    <!-- INFO -->
    <div class="flex flex-col">
        <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">
            {{ $subscricao?->plano?->nome ?? 'Sem subscrição' }}
        </div>

        @if($subscricao)
            @if($this->diasRestantes >= 0)
                <div class="text-xs text-gray-500">
                    Expira {{ $subscricao->data_expiracao->format('d/m/Y') }}
                </div>
                <div class="text-[11px] mt-1 text-gray-400">
                    {{ $this->percentualRestante }}% restante
                </div>
            @else
                <div class="text-xs text-red-500">
                    Expirada há {{ abs($this->diasRestantes) }} dias
                </div>
                <button wire:click="renovar"
                        class="mt-2 text-xs bg-logigate-primary text-white px-3 py-1 rounded-lg">
                    Renovar Agora
                </button>
            @endif
        @else
            <div class="text-xs text-gray-500">Nenhuma subscrição ativa</div>
            <button wire:click="renovar"
                    class="mt-2 text-xs bg-logigate-primary text-white px-3 py-1 rounded-lg">
                Subscrever
            </button>
        @endif
    </div>
</div>
