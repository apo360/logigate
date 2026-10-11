<x-app-layout>
    <x-slot name="header">Comprovativo de pagamento da subscrição</x-slot>
    <article class="mx-auto max-w-3xl space-y-4 rounded-lg bg-white p-6 text-slate-900 dark:bg-slate-900 dark:text-white">
        <h1 class="text-2xl font-bold">LogiGate — pagamento confirmado</h1>
        <p>Empresa: {{ $empresa->Empresa }}</p>
        <p>Plano: {{ data_get($payment->subscription?->commercial_snapshot, 'plan_name') ?? $payment->subscription?->tipo_plano ?? 'Plano' }}</p>
        <p>Transacção: <span class="font-mono">{{ $payment->merchant_transaction_id }}</span></p>
        <p>Método: {{ $payment->method }} · Data: {{ $payment->paid_at?->format('d/m/Y H:i') }}</p>
        <p class="text-xl font-semibold">Total pago: {{ number_format($payment->amount, 2, ',', '.') }} {{ $payment->currency }}</p>
        <p>Período: {{ $payment->subscription?->data_inicio?->format('d/m/Y') }} — {{ $payment->subscription?->data_expiracao?->format('d/m/Y') }}</p>
        <p class="text-sm text-slate-600 dark:text-slate-300">Este comprovativo confirma a transacção da subscrição. Não substitui a factura fiscal.</p>
        <a href="{{ route('billing.plans') }}" class="text-blue-700">Voltar à subscrição e pagamentos</a>
    </article>
</x-app-layout>
