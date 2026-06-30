<x-ui.modal id="empresa-user-form" :title="$editing ? 'Editar Usuário' : 'Novo Usuário'" maxWidth="lg">
    <form wire:submit.prevent="save" class="space-y-6">
        <section class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Dados pessoais</h3>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="empresa-user-name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nome</label>
                    <input id="empresa-user-name" wire:model.defer="form.name" class="mt-1 w-full rounded-md border-slate-300 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="text" autocomplete="name">
                    @error('form.name') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="empresa-user-email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
                    <input id="empresa-user-email" wire:model.defer="form.email" class="mt-1 w-full rounded-md border-slate-300 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="email" autocomplete="email">
                    @error('form.email') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Acesso</h3>

            <div class="mt-4">
                <label for="empresa-user-role" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Papel principal</label>
                <select id="empresa-user-role" wire:model.defer="form.role" class="mt-1 w-full rounded-md border-slate-300 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Selecione</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('form.role') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
            </div>
        </section>

        @unless($editing)
            <section class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Segurança inicial</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Defina a senha inicial do novo usuário.</p>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="empresa-user-password" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Senha</label>
                        <input id="empresa-user-password" wire:model.defer="form.password" class="mt-1 w-full rounded-md border-slate-300 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="password" autocomplete="new-password">
                        @error('form.password') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="empresa-user-password-confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Confirmar senha</label>
                        <input id="empresa-user-password-confirmation" wire:model.defer="form.password_confirmation" class="mt-1 w-full rounded-md border-slate-300 text-slate-900 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white" type="password" autocomplete="new-password">
                        @error('form.password_confirmation') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>
        @else
            <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-900/70 dark:bg-blue-950/40 dark:text-blue-200">
                Senhas e bloqueios são tratados no painel de segurança do usuário.
            </div>
        @endunless

        <div wire:loading.delay wire:target="save" class="rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800 dark:border-blue-900/70 dark:bg-blue-950/40 dark:text-blue-200">
            A guardar alterações...
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 dark:border-slate-700 sm:flex-row sm:justify-end">
            <button type="button"
                    wire:click="cancelEdit"
                    wire:loading.attr="disabled"
                    class="inline-flex min-h-10 items-center justify-center rounded-md border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                Cancelar
            </button>
            <button class="inline-flex min-h-10 items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    type="submit">
                <span wire:loading.remove wire:target="save">{{ $editing ? 'Guardar alterações' : 'Cadastrar usuário' }}</span>
                <span wire:loading wire:target="save">A guardar...</span>
            </button>
        </div>
    </form>
</x-ui.modal>
