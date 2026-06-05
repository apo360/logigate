<div class="space-y-5">
    @if($paymentView['fallback_applied'] ?? false)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            O GPO nao ficou disponivel para este pagamento. Geramos uma referencia Multicaixa para continuar.
        </div>
    @endif

    <div class="rounded-lg border border-purple-200 bg-purple-50 p-5 text-center">
        <div class="text-sm text-purple-800">Entidade</div>
        <div class="mt-1 text-2xl font-bold text-purple-950">{{ data_get($paymentView, 'reference.entity') ?? '-' }}</div>

        <div class="mt-5 text-sm text-purple-800">Referencia</div>
        <div class="mt-1 font-mono text-3xl font-bold tracking-wide text-slate-950">
            {{ data_get($paymentView, 'reference.reference_number') ? chunk_split(data_get($paymentView, 'reference.reference_number'), 3, ' ') : '-' }}
        </div>

        <div class="mt-5 text-sm text-purple-800">Validade</div>
        <div class="mt-1 text-lg font-semibold text-slate-950">
            {{ data_get($paymentView, 'reference.due_date') ? \Carbon\Carbon::parse(data_get($paymentView, 'reference.due_date'))->format('d/m/Y') : '-' }}
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <h3 class="font-semibold text-slate-900">Como pagar</h3>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-700">
            <li>Abra o Express ou um terminal Multicaixa.</li>
            <li>Escolha pagamento por referencia.</li>
            <li>Insira a entidade, referencia e confirme o valor.</li>
            <li>A activacao acontece depois da confirmacao por webhook.</li>
        </ol>
    </div>
</div>
