<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Conta Corrente</h2>
                <p class="text-sm text-slate-600">{{ $customer->CompanyName }}</p>
            </div>

            <div class="flex flex-col gap-3 text-left md:items-end md:text-right">
                <div>
                    <div class="text-sm text-slate-500">Saldo actual</div>
                    <div class="text-3xl font-bold {{ $saldoAtual > 0 ? 'text-red-700' : 'text-green-700' }}">
                        {{ number_format($saldoAtual, 2, ',', '.') }} Kz
                    </div>
                    <div class="mt-1 text-sm text-slate-500">
                        Débitos: {{ number_format($totalDebitos, 2, ',', '.') }} Kz ·
                        Créditos: {{ number_format($totalCreditos, 2, ',', '.') }} Kz
                    </div>
                </div>

                @if($canRegisterMovimento)
                    <button type="button"
                            wire:click="openCreateModal"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Novo Movimento
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($showStructuredNotice)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Conta Corrente em estruturação</p>
            <p class="mt-1">
                O extrato usa a fonte oficial de cálculo. A criação manual de movimentos só fica disponível quando a
                tabela possuir suporte tenant com <code>empresa_id</code>; até lá, permanece bloqueada por segurança.
            </p>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-3 lg:grid-cols-[minmax(220px,1fr)_160px_160px_160px_auto]">
            <input type="search"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Pesquisar descrição, referência ou observação"
                   class="rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">

            <select wire:model.live="tipo" class="rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos tipos</option>
                <option value="credito">Crédito</option>
                <option value="debito">Débito</option>
            </select>

            <input type="date" wire:model.live="data_inicio" class="rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            <input type="date" wire:model.live="data_fim" class="rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">

            <button type="button"
                    wire:click="resetFilters"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Limpar
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Data</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Descrição</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Origem</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Referência</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Tipo</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Valor</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Saldo após</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movimentos as $movimento)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">
                                {{ ($movimento->data_movimento ?? $movimento->data)?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $movimento->descricao ?: 'Sem descrição' }}</div>
                                @if($movimento->observacoes)
                                    <div class="mt-1 text-xs text-slate-500">{{ $movimento->observacoes }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $movimento->origem_descricao }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $movimento->referencia ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $movimento->tipo === 'credito' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ ucfirst($movimento->tipo) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold {{ $movimento->tipo === 'credito' ? 'text-green-700' : 'text-red-700' }}">
                                {{ number_format((float) $movimento->valor, 2, ',', '.') }} Kz
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-700">
                                {{ $movimento->saldo_apos_movimento !== null ? number_format((float) $movimento->saldo_apos_movimento, 2, ',', '.') . ' Kz' : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                Nenhum movimento encontrado para este cliente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-4 py-3">
            {{ $movimentos->links() }}
        </div>
    </div>

    @if($showCreateModal && $canRegisterMovimento)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div class="w-full max-w-xl rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Novo Movimento</h3>
                    <button type="button" wire:click="closeCreateModal" class="text-slate-500 hover:text-slate-800">Fechar</button>
                </div>

                <form wire:submit.prevent="registrarMovimento" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Tipo</label>
                            <select wire:model="form.tipo" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="debito">Débito</option>
                                <option value="credito">Crédito</option>
                            </select>
                            @error('form.tipo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Valor</label>
                            <input type="number" min="0.01" step="0.01" wire:model="form.valor" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('form.valor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Descrição</label>
                        <input type="text" wire:model="form.descricao" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('form.descricao') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Referência</label>
                            <input type="text" wire:model="form.referencia" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('form.referencia') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Data</label>
                            <input type="date" wire:model="form.data_movimento" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('form.data_movimento') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Observações</label>
                        <textarea wire:model="form.observacoes" rows="3" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('form.observacoes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button"
                                wire:click="closeCreateModal"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Registar Movimento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
