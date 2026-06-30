<div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="border-b border-slate-200 p-5 dark:border-slate-700">
        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Segurança de {{ $managedUser->name }}</h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Operações sensíveis da conta do usuário.</p>
    </div>

    <div class="space-y-5 p-5">
        @if($temporaryPassword)
            <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4 dark:border-yellow-900/70 dark:bg-yellow-950/30">
                <p class="font-semibold text-yellow-900 dark:text-yellow-200">Senha temporária</p>
                <p class="mt-2 break-all rounded-md bg-white px-3 py-2 font-mono text-lg text-yellow-950 dark:bg-slate-950 dark:text-yellow-100">{{ $temporaryPassword }}</p>
                <p class="mt-2 text-sm text-yellow-800 dark:text-yellow-200">Mostrada apenas nesta sessão do painel. Copie antes de sair.</p>
            </div>
        @endif

        <section class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">Estado da conta</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $managedUser->is_blocked ? 'A conta está bloqueada e sem acesso operacional.' : 'A conta pode autenticar conforme as permissões atribuídas.' }}
                    </p>
                </div>

                @if($managedUser->is_blocked)
                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-700 dark:bg-amber-950/50 dark:text-amber-200">Bloqueado</span>
                @else
                    <span class="inline-flex rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700 dark:bg-green-950/50 dark:text-green-200">Activo</span>
                @endif
            </div>
        </section>

        @if(auth()->id() === $managedUser->id)
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                A própria conta não pode ser bloqueada nem resetada por este painel.
            </div>
        @else
            <div wire:loading.delay wire:target="block,unblock,resetPassword" class="rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800 dark:border-blue-900/70 dark:bg-blue-950/40 dark:text-blue-200">
                A processar operação...
            </div>

            <section class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-slate-100">Bloqueio de acesso</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Use esta acção quando o usuário deve perder acesso imediatamente.</p>

                <div class="mt-4">
                    @if($managedUser->is_blocked)
                        <button wire:click="unblock"
                                wire:confirm="Desbloquear este usuário?"
                                wire:loading.attr="disabled"
                                class="inline-flex min-h-10 items-center justify-center rounded-md bg-green-700 px-4 text-sm font-semibold text-white hover:bg-green-800 disabled:opacity-60"
                                type="button">
                            Desbloquear usuário
                        </button>
                    @else
                        <button wire:click="block"
                                wire:confirm="Bloquear este usuário?"
                                wire:loading.attr="disabled"
                                class="inline-flex min-h-10 items-center justify-center rounded-md bg-amber-600 px-4 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-60"
                                type="button">
                            Bloquear usuário
                        </button>
                    @endif
                </div>
            </section>

            <section class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900/70 dark:bg-red-950/20">
                <h3 class="font-semibold text-red-900 dark:text-red-200">Reset de senha</h3>
                <p class="mt-1 text-sm text-red-800 dark:text-red-200">Gera uma senha temporária e força o usuário a trocar a senha no próximo acesso.</p>

                <button wire:click="resetPassword"
                        wire:confirm="Gerar uma nova senha temporária?"
                        wire:loading.attr="disabled"
                        class="mt-4 inline-flex min-h-10 items-center justify-center rounded-md bg-red-700 px-4 text-sm font-semibold text-white hover:bg-red-800 disabled:opacity-60"
                        type="button">
                    Resetar senha
                </button>
            </section>
        @endif
    </div>
</div>
