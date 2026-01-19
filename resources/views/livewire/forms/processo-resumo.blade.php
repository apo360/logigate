<div class="bg-white dark:bg-gray-900 rounded-xl p-3 border">
    <h6 class="font-semibold">Resumo</h6>
    <div class="mt-2 text-sm space-y-1">
        <div><strong>Cliente:</strong> {{ $data['customer_id'] ?? '—' }}</div>
        <div><strong>Exportador:</strong> {{ $data['exportador_id'] ?? '—' }}</div>
        <div><strong>FOB:</strong> {{ $data['fob_total'] ?? '0.00' }}</div>
        <div><strong>CIF (USD):</strong> {{ $data['cif'] ?? '0.00' }}</div>
        <div><strong>Valor Aduaneiro (Kz):</strong> {{ $data['ValorAduaneiro'] ?? '0.00' }}</div>
    </div>
</div>
