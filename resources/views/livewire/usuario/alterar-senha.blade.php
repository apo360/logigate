<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Alterar Senha</h2>
    <form wire:submit.prevent="save" class="grid gap-4 md:grid-cols-3">
        <input wire:model.defer="current_password" class="rounded-md border-gray-300" placeholder="Senha atual" type="password">
        <input wire:model.defer="password" class="rounded-md border-gray-300" placeholder="Nova senha" type="password">
        <input wire:model.defer="password_confirmation" class="rounded-md border-gray-300" placeholder="Confirmar senha" type="password">
        <div class="md:col-span-3">
            <button class="rounded-md bg-blue-600 px-4 py-2 text-white" type="submit">Alterar Senha</button>
        </div>
    </form>
</div>
