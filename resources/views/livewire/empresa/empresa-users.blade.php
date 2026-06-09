<div class="space-y-4">
    @if($temporaryPassword)
        <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4">
            <p class="font-semibold text-yellow-900">Senha temporária gerada</p>
            <p class="mt-1 font-mono text-lg">{{ $temporaryPassword }}</p>
            <p class="text-sm text-yellow-800">Copie agora. A senha força troca no próximo acesso.</p>
        </div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="mb-4 flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Usuários e Permissões</h2>
                <p class="text-sm text-slate-600 dark:text-slate-300">Gerencie acessos, estados e permissões da empresa.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <input wire:model.live.debounce.300ms="search"
                       class="rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                       placeholder="Pesquisar">
                <button wire:click="openUserForm"
                        class="inline-flex items-center justify-center rounded-md bg-blue-700 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    <i class="fa fa-plus mr-2"></i>
                    Novo utilizador
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Nome</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Email</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Papel</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Estado</th>
                        <th class="px-4 py-2 text-right font-semibold text-slate-700 dark:text-slate-200">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($users as $user)
                        <tr wire:key="empresa-user-{{ $user->id }}">
                            <td class="px-4 py-3">
                                <a class="font-medium text-blue-700 hover:text-blue-800" href="{{ route('usuarios.show', $user->id) }}">
                                    {{ $user->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $user->roles->pluck('name')->join(', ') ?: 'Sem papel' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $user->is_blocked ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700' }}">
                                    {{ $user->is_blocked ? 'Bloqueado' : 'Ativo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="editUser({{ $user->id }})" class="font-medium text-slate-700 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white" type="button">
                                        Editar
                                    </button>
                                    <a href="{{ route('usuarios.permissions', $user->id) }}" class="font-medium text-blue-700 hover:text-blue-800">
                                        Permissões
                                    </a>
                                    @if(! auth()->id() || auth()->id() !== $user->id)
                                        @if($user->is_blocked)
                                            <button wire:click="unblock({{ $user->id }})" class="font-medium text-green-700 hover:text-green-800" type="button">Desbloquear</button>
                                        @else
                                            <button wire:click="block({{ $user->id }})" class="font-medium text-yellow-700 hover:text-yellow-800" type="button">Bloquear</button>
                                        @endif
                                        <button wire:click="resetPassword({{ $user->id }})" class="font-medium text-purple-700 hover:text-purple-800" type="button">Resetar senha</button>
                                        <button wire:click="remove({{ $user->id }})" wire:confirm="Remover usuário da empresa?" class="font-medium text-red-700 hover:text-red-800" type="button">Remover</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <livewire:empresa.empresa-user-form :empresa="$empresa" />
</div>
