<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Perfil da Empresa</h2>

    <form wire:submit.prevent="save" class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium">Nome</label>
            <input wire:model.defer="form.Empresa" class="w-full rounded-md border-gray-300" type="text">
            @error('form.Empresa') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">NIF</label>
            <input wire:model.defer="form.NIF" class="w-full rounded-md border-gray-300" type="text">
            @error('form.NIF') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Cédula</label>
            <input wire:model.defer="form.Cedula" class="w-full rounded-md border-gray-300" type="text">
            @error('form.Cedula') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Designação</label>
            <select wire:model.defer="form.Designacao" class="w-full rounded-md border-gray-300">
                <option value="Despachante Oficial">Despachante Oficial</option>
                <option value="Praticante">Praticante</option>
                <option value="Outro">Outro</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input wire:model.defer="form.Email" class="w-full rounded-md border-gray-300" type="email">
        </div>
        <div>
            <label class="block text-sm font-medium">Contacto móvel</label>
            <input wire:model.defer="form.Contacto_movel" class="w-full rounded-md border-gray-300" type="text">
        </div>
        <div>
            <label class="block text-sm font-medium">Contacto fixo</label>
            <input wire:model.defer="form.Contacto_fixo" class="w-full rounded-md border-gray-300" type="text">
        </div>
        <div>
            <label class="block text-sm font-medium">Slogan</label>
            <input wire:model.defer="form.Slogan" class="w-full rounded-md border-gray-300" type="text">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium">Endereço</label>
            <input wire:model.defer="form.Endereco_completo" class="w-full rounded-md border-gray-300" type="text">
        </div>

        <div class="md:col-span-2">
            <button class="rounded-md bg-blue-600 px-4 py-2 text-white" type="submit">Salvar Empresa</button>
        </div>
    </form>
</div>
