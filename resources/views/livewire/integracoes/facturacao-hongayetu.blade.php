<div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
    <header class="space-y-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase text-blue-700 dark:text-blue-300">Integrações</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Facturação Hongayetu</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Teste e active a comunicação fiscal externa da empresa com a API Hongayetu.
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full px-3 py-1 text-sm font-semibold {{ $isActive ? 'bg-green-50 text-green-700 dark:bg-green-950/50 dark:text-green-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                {{ $integracao?->estado?->label() ?? 'Não configurada' }}
            </span>
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-900/70 dark:bg-blue-950/40 dark:text-blue-100">
                Esta integração é opcional. Se estiver inactiva, o sistema continua a funcionar normalmente sem emissão fiscal externa.
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-100">
                A API URL e o token desta integração são configurados internamente pela equipa técnica. O administrador da empresa não pode alterar estes dados.
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                Activar esta integração apenas permite comunicação com a Facturação Hongayetu. Nenhuma factura será emitida automaticamente nesta fase.
            </div>
        </div>

        @if($isActive)
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900 dark:border-green-900/70 dark:bg-green-950/40 dark:text-green-100">
                Integração activa. Os menus e acções de facturação externa poderão ficar disponíveis para utilizadores autorizados.
            </div>
        @endif
    </header>

    @unless($schemaReady)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            A tabela <strong>empresa_integracoes</strong> ainda não existe. Execute a migration aprovada antes de gerir integrações.
        </div>
    @endunless

    <section class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Estado</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Último teste</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">
                            {{ $integracao?->ultimo_teste_em?->format('d/m/Y H:i') ?? 'Nunca' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Estado do teste</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">
                            {{ $integracao?->ultimo_teste_status ?? 'Por testar' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Mensagem</dt>
                        <dd class="mt-1 text-slate-700 dark:text-slate-200">
                            {{ $integracao?->ultimo_erro ?? ($integracao?->ultimo_teste_status === 'sucesso' ? 'Credenciais verificadas.' : 'Sem mensagem.') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Empresa fiscal remota</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ $integracao?->config['remote_empresa_nome'] ?? 'Não identificada' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">NIF fiscal remoto</dt>
                        <dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ $integracao?->config['remote_empresa_nif'] ?? 'Não identificado' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Configuração interna da aplicação</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">API URL</dt>
                        <dd class="font-semibold {{ $internalApiUrlConfigured ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                            {{ $internalApiUrlConfigured ? 'Configurada internamente' : 'Não configurada' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Token</dt>
                        <dd class="font-semibold {{ $internalTokenConfigured ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                            {{ $internalTokenConfigured ? 'Configurado internamente' : 'Não configurado' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">API Key</dt>
                        <dd class="font-semibold {{ $internalApiKeyConfigured ? 'text-green-700 dark:text-green-300' : 'text-slate-600 dark:text-slate-300' }}">
                            {{ $internalApiKeyConfigured ? 'Configurada internamente' : 'Não configurada' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Ambiente</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $internalEnvironment ?? 'Não definido' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Timeout</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $internalTimeout ?? 'Não definido' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Retry</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $internalRetry ?? 'Não definido' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <form wire:submit.prevent="save" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900 lg:col-span-2">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Preferências operacionais</h2>
                <a href="{{ route('integracoes.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800 dark:text-blue-300">
                    Voltar às integrações
                </a>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Empresa fiscal ID</label>
                    <input wire:model.defer="form.config.empresa_fiscal_id" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    @error('form.config.empresa_fiscal_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">NIF local esperado</label>
                    <input wire:model.defer="form.config.nif" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    @error('form.config.nif') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estabelecimento padrão</label>
                    <input wire:model.defer="form.config.estabelecimento_id_padrao" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    @error('form.config.estabelecimento_id_padrao') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Observações</label>
                    <textarea wire:model.defer="form.config.observacoes" rows="4" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
                    @error('form.config.observacoes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            @error('test') <p class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950/40 dark:text-red-300">{{ $message }}</p> @enderror
            @error('activation') <p class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950/40 dark:text-red-300">{{ $message }}</p> @enderror

            <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-70">
                    <i class="fas fa-spinner fa-pulse" wire:loading wire:target="save"></i>
                    <span wire:loading.remove wire:target="save">Guardar preferências</span>
                    <span wire:loading wire:target="save">A guardar...</span>
                </button>

                <button type="button" wire:click="testCredentials" wire:loading.attr="disabled" wire:target="testCredentials" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-blue-300 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-70 dark:border-blue-900/70 dark:text-blue-300 dark:hover:bg-blue-950/40">
                    <span wire:loading.remove wire:target="testCredentials">Testar credenciais</span>
                    <span wire:loading wire:target="testCredentials">A testar...</span>
                </button>

                @if($isActive)
                    <button type="button" wire:click="deactivate" wire:loading.attr="disabled" wire:target="deactivate" class="inline-flex min-h-10 items-center rounded-lg border border-amber-300 px-4 text-sm font-semibold text-amber-700 hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-70 dark:border-amber-900/70 dark:text-amber-300 dark:hover:bg-amber-950/40">
                        Desactivar integração
                    </button>
                @else
                    <button type="button" wire:click="activate" wire:loading.attr="disabled" wire:target="activate" class="inline-flex min-h-10 items-center rounded-lg border border-green-300 px-4 text-sm font-semibold text-green-700 hover:bg-green-50 disabled:cursor-not-allowed disabled:opacity-70 dark:border-green-900/70 dark:text-green-300 dark:hover:bg-green-950/40">
                        Activar integração
                    </button>
                @endif
            </div>
        </form>
    </section>
</div>
