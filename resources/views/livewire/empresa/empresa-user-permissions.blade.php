<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Permissões de {{ $managedUser->name }}</h2>

    <form wire:submit.prevent="save" class="space-y-6">
        <div>
            <h3 class="mb-2 font-medium">Papéis</h3>
            <div class="grid gap-2 md:grid-cols-3">
                @foreach($availableRoles as $role)
                    <label class="flex items-center gap-2">
                        <input wire:model.defer="roles" type="checkbox" value="{{ $role->name }}">
                        <span>{{ $role->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="mb-2 font-medium">Permissões diretas</h3>
            <div class="grid gap-2 md:grid-cols-3">
                @foreach($availablePermissions as $permission)
                    <label class="flex items-center gap-2">
                        <input wire:model.defer="permissions" type="checkbox" value="{{ $permission->name }}">
                        <span>{{ $permission->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <button class="rounded-md bg-blue-600 px-4 py-2 text-white" type="submit">Salvar Permissões</button>
    </form>
</div>
