<div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
        <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900 dark:text-white">
            <i class="fas fa-university text-blue-600"></i>
            Contas bancárias
        </h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Contas usadas em facturas, recibos e documentos financeiros.</p>
    </div>

    <div class="space-y-6 p-5">
        <form wire:submit.prevent="save" class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-slate-950 dark:text-white">{{ $editingId ? 'Editar conta bancária' : 'Adicionar conta bancária' }}</h4>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Preencha banco, IBAN e número de conta.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="empresa-banco" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Banco</label>
                    <select id="empresa-banco" wire:model="banco" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('banco') border-red-500 @enderror">
                        <option value="">Selecione um banco</option>
                        @foreach($listaBancos as $codigo => $nomeBanco)
                            <option value="{{ $codigo }}">{{ $nomeBanco }}</option>
                        @endforeach
                    </select>
                    @error('banco') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="empresa-iban" class="block text-sm font-medium text-slate-700 dark:text-slate-200">IBAN</label>
                    <input id="empresa-iban" wire:model.defer="iban" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('iban') border-red-500 @enderror">
                    @error('iban') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="empresa-conta" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Número da conta</label>
                    <input id="empresa-conta" wire:model.defer="conta" type="text" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('conta') border-red-500 @enderror">
                    @error('conta') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:justify-end">
                @if($editingId)
                    <button type="button" wire:click="cancelEdit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        Cancelar
                    </button>
                @endif
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-70">
                    <i class="fas fa-spinner fa-pulse" wire:loading wire:target="save"></i>
                    <i class="fas {{ $editingId ? 'fa-save' : 'fa-plus' }}" wire:loading.remove wire:target="save"></i>
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Guardar alterações' : 'Adicionar conta' }}</span>
                    <span wire:loading wire:target="save">A guardar...</span>
                </button>
            </div>
        </form>

        <section>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Contas registadas</h4>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ count($contasList) }}</span>
            </div>

            <div wire:loading.flex wire:target="save,edit,delete,cancelEdit" class="mb-3 items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300">
                <i class="fas fa-spinner fa-pulse"></i>
                A actualizar contas bancárias...
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 dark:border-slate-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Banco</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">IBAN</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Conta</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-200">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                            @forelse($contasList as $ct)
                                <tr>
                                    <td class="px-4 py-3 text-slate-800 dark:text-slate-200">{{ $listaBancos[$ct->code_banco] ?? $ct->code_banco }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $ct->iban }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $ct->conta }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <button type="button" wire:click="edit({{ $ct->id }})" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                                <i class="fas fa-pen"></i> Editar
                                            </button>
                                            <button type="button" wire:click="delete({{ $ct->id }})" wire:confirm="Eliminar esta conta bancária?" class="inline-flex items-center gap-2 rounded-lg border border-red-300 px-3 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-900/70 dark:text-red-300 dark:hover:bg-red-950/40">
                                                <i class="fas fa-trash"></i> Remover
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center">
                                        <div class="mx-auto max-w-sm text-sm text-slate-500 dark:text-slate-400">
                                            <i class="fas fa-university mb-3 text-2xl text-slate-300 dark:text-slate-600"></i>
                                            <p class="font-medium text-slate-700 dark:text-slate-200">Nenhuma conta bancária registada.</p>
                                            <p class="mt-1">Adicione uma conta para aparecer em documentos financeiros.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
