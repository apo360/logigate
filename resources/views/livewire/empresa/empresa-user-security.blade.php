<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Segurança de {{ $managedUser->name }}</h2>

    @if($temporaryPassword)
        <div class="mb-4 rounded border border-yellow-300 bg-yellow-50 p-3">
            <p class="font-semibold">Senha temporária</p>
            <p class="font-mono text-lg">{{ $temporaryPassword }}</p>
        </div>
    @endif

    @if(auth()->id() === $managedUser->id)
        <p class="text-sm text-gray-600">A própria conta não pode ser bloqueada nem resetada por este painel.</p>
    @else
    <div class="flex flex-wrap gap-3">
        @if($managedUser->is_blocked)
            <button wire:click="unblock" class="rounded-md bg-green-600 px-4 py-2 text-white" type="button">Desbloquear</button>
        @else
            <button wire:click="block" class="rounded-md bg-yellow-600 px-4 py-2 text-white" type="button">Bloquear</button>
        @endif

        <button wire:click="resetPassword" class="rounded-md bg-purple-600 px-4 py-2 text-white" type="button">Resetar senha</button>
    </div>
    @endif
</div>
