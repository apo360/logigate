<div class="rounded-lg border border-green-200 bg-green-50 p-6 text-center">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-2xl font-bold text-green-700">
        ✓
    </div>
    <h2 class="mt-4 text-xl font-bold text-green-900">Pagamento confirmado</h2>
    <p class="mt-2 text-sm text-green-800">
        @if(($paymentView['starts_at'] ?? null) && \Carbon\Carbon::parse($paymentView['starts_at'])->isFuture())
            O próximo período foi confirmado e começa em {{ \Carbon\Carbon::parse($paymentView['starts_at'])->format('d/m/Y') }}. O acesso actual foi preservado.
        @else
            A sua subscrição foi activada. Pode continuar para o sistema.
        @endif
    </p>
    <a href="{{ route('dashboard') }}" class="mt-5 inline-flex rounded-lg bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
        Ir para o dashboard
    </a>
    <a href="{{ route('billing.receipt', $paymentId) }}" class="mt-4 block text-sm font-semibold text-green-800">Ver comprovativo</a>
</div>
