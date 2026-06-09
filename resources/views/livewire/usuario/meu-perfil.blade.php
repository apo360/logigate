<!-- 
 Minha Conta

Para o utilizador logado.

Funcionalidades:

Editar nome
Editar email
Alterar palavra-passe
Foto/avatar
Preferências
Sessões activas
2FA futuramente
-->
<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Meu Perfil</h2>
    <form wire:submit.prevent="save" class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium">Nome</label>
            <input wire:model.defer="form.name" class="w-full rounded-md border-gray-300" type="text">
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input wire:model.defer="form.email" class="w-full rounded-md border-gray-300" type="email">
        </div>
        <div class="md:col-span-2">
            <button class="rounded-md bg-blue-600 px-4 py-2 text-white" type="submit">Salvar Perfil</button>
        </div>
    </form>
</div>
