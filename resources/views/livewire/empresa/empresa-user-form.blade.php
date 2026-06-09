<x-ui.modal id="empresa-user-form" :title="$editing ? 'Editar Utilizador' : 'Novo Utilizador'" maxWidth="lg">
    <form wire:submit.prevent="save" class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-200">Nome</label>
            <input wire:model.defer="form.name" class="mt-1 w-full rounded-md border-slate-700 bg-slate-950 text-white" type="text">
            @error('form.name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-200">Email</label>
            <input wire:model.defer="form.email" class="mt-1 w-full rounded-md border-slate-700 bg-slate-950 text-white" type="email">
            @error('form.email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        @unless($editing)
            <div>
                <label class="block text-sm font-medium text-slate-200">Senha</label>
                <input wire:model.defer="form.password" class="mt-1 w-full rounded-md border-slate-700 bg-slate-950 text-white" type="password">
                @error('form.password') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-200">Confirmar senha</label>
                <input wire:model.defer="form.password_confirmation" class="mt-1 w-full rounded-md border-slate-700 bg-slate-950 text-white" type="password">
            </div>
        @endunless

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-200">Papel</label>
            <select wire:model.defer="form.role" class="mt-1 w-full rounded-md border-slate-700 bg-slate-950 text-white">
                <option value="">Selecione</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
            @error('form.role') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-3 md:col-span-2">
            <button type="button" wire:click="close" class="rounded-md border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800">
                Cancelar
            </button>
            <button class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800" type="submit">
                {{ $editing ? 'Guardar alterações' : 'Cadastrar utilizador' }}
            </button>
        </div>
    </form>
</x-ui.modal>
