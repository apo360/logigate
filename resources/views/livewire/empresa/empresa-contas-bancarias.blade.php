<div class="rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="flex items-center gap-2 text-lg font-semibold text-slate-900">
            <i class="fas fa-university text-blue-600"></i>
            Contas Bancárias
        </h3>
        <p class="mt-1 text-sm text-slate-500">Adicione as contas bancárias para emissão de facturas e recibos.</p>
    </div>

    <div class="p-6">
        <form wire:submit.prevent="save" class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-building mr-1 text-slate-400"></i> Banco
                </label>
                <select wire:model="banco"
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('banco') border-red-500 @enderror">
                    <option value="">Selecione um banco</option>
                    @foreach($listaBancos as $codigo => $nomeBanco)
                        <option value="{{ $codigo }}">{{ $nomeBanco }}</option>
                    @endforeach
                </select>
                @error('banco')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-barcode mr-1 text-slate-400"></i> IBAN
                </label>
                <input wire:model.defer="iban" type="text"
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('iban') border-red-500 @enderror">
                @error('iban')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-credit-card mr-1 text-slate-400"></i> Número da Conta
                </label>
                <input wire:model.defer="conta" type="text"
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('conta') border-red-500 @enderror">
                @error('conta')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-end sm:col-span-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-plus"></i>
                    Adicionar Conta
                </button>
            </div>
        </form>
        <div class="mt-8">
            <h4 class="mb-3 flex items-center gap-2 text-base font-semibold text-slate-800">
                <i class="fas fa-list text-blue-600"></i>
                Contas registadas
            </h4>
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full border-collapse bg-white text-left text-sm text-slate-500">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-medium text-slate-900">Banco</th>
                            <th scope="col" class="px-6 py-4 font-medium text-slate-900">IBAN</th>
                            <th scope="col" class="px-6 py-4 font-medium text-slate-900">Número da Conta</th>
                            <th scope="col" class="px-6 py-4 font-medium text-slate-900">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 border-t border-slate-100">
                        @foreach($contasList as $ct)
                            <tr>
                                <td class="px-6 py-4">{{ $listaBancos[$ct->code_banco] ?? $ct->code_banco }}</td>
                                <td class="px-6 py-4">{{ $ct->iban }}</td>
                                <td class="px-6 py-4">{{ $ct->conta }}</td>
                                <td class="px-6 py-4">
                                    <button wire:click="delete({{ $ct->id }})"
                                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
