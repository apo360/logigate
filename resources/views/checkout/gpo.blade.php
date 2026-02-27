{{-- checkout/gpo.blade.php --}}

<div class="bg-white rounded-xl shadow p-6 text-center">

    @if($status === 'pending')
        <div class="flex flex-col items-center space-y-4">
            <svg class="animate-spin h-10 w-10 text-blue-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4" fill="none"/>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8z"/>
            </svg>

            <h2 class="text-xl font-semibold text-gray-800">
                Aguardando confirmação do pagamento
            </h2>

            <p class="text-gray-600">
                Enviámos um pedido de pagamento para o número:
            </p>

            <p class="text-lg font-bold text-gray-900">
                +244 {{ $phone }}
            </p>

            <p class="text-sm text-gray-500">
                Autorize o pagamento no aplicativo <strong>MCX App</strong>.
            </p>
        </div>
    @endif


    @if($status === 'paid')
        <div class="flex flex-col items-center space-y-4">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                <span class="text-green-600 text-3xl">✓</span>
            </div>

            <h2 class="text-xl font-semibold text-green-700">
                Pagamento confirmado com sucesso
            </h2>

            <p class="text-gray-600">
                O seu plano <strong>{{ $plano->nome }}</strong> já está ativo.
            </p>

            <a href="{{ route('dashboard') }}"
               class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Ir para o sistema
            </a>
        </div>
    @endif


    @if($status === 'failed')
        <div class="flex flex-col items-center space-y-4">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                <span class="text-red-600 text-3xl">✕</span>
            </div>

            <h2 class="text-xl font-semibold text-red-700">
                Pagamento não autorizado
            </h2>

            <p class="text-gray-600">
                O pagamento não foi confirmado no MCX App.
            </p>

            <div class="flex gap-4">
                <button wire:click="retryGpo"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg">
                    Tentar novamente
                </button>

                <button wire:click="switchToRef"
                        class="px-5 py-2 border border-gray-300 rounded-lg">
                    Usar referência Multicaixa
                </button>
            </div>
        </div>
    @endif

</div>
