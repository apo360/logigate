<div>
    @if($open)
    <div class="fixed inset-0 bg-black/40"></div>

    <div class="fixed inset-0 flex items-center justify-center">
        <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-lg">

            <h2 class="text-xl font-bold mb-4">Criar Nova Tarefa</h2>

            <div class="space-y-4">

                <div>
                    <label class="font-semibold">Nome</label>
                    <input type="text" wire:model="title" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="font-semibold">Tipo</label>
                    <select wire:model="type" class="w-full border rounded p-2">
                        <option value="">-- Escolher --</option>
                        <option value="invoice">Emitir Fatura</option>
                        <option value="payment">Cobrança</option>
                        <option value="alert">Alerta</option>
                        <option value="backup">Backup</option>
                        <option value="custom">Personalizada</option>
                    </select>
                </div>

                <div>
                    <label class="font-semibold">Data/Hora</label>
                    <input type="datetime-local" wire:model="schedule_date" class="w-full border rounded p-2">
                </div>

                <div>
                    <label class="font-semibold">Recorrência</label>
                    <select wire:model="recurrence" class="w-full border rounded p-2">
                        <option value="none">Nenhuma</option>
                        <option value="daily">Diária</option>
                        <option value="weekly">Semanal</option>
                        <option value="monthly">Mensal</option>
                        <option value="yearly">Anual</option>
                    </select>
                </div>

                <div class="flex justify-between mt-6">
                    <button wire:click=\"$set('open', false)\" class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>
                    <button wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
                </div>

            </div>

        </div>
    </div>
    @endif
</div>

