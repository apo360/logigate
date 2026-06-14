<div class="mx-auto max-w-7xl space-y-5">
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Empresa</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Integrações</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
            Configure providers por empresa. A facturação via Hongayetu Facturação é prioritária e comunica apenas por API interna.
        </p>
    </section>

    @unless($schemaReady)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            A tabela <strong>empresa_integracoes</strong> ainda não existe. Execute a migration aprovada antes de activar configurações.
        </div>
    @endunless

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($cards as $card)
            @php
                $integration = $integrations->get($card['tipo'] . ':' . $card['provedor']);
                $estado = $integration?->estado?->label() ?? 'Não configurada';
                $isActive = $integration?->estado?->value === 'activo';
                $iconClass = str_starts_with($card['icon'], 'fa-brands') ? $card['icon'] : 'fa ' . $card['icon'];
            @endphp

            <article class="rounded-lg border {{ $card['featured'] ? 'border-blue-300 ring-2 ring-blue-100' : 'border-slate-200' }} bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg {{ $card['featured'] ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                            <i class="{{ $iconClass }}"></i>
                        </span>
                        <div>
                            <h2 class="font-semibold text-slate-950 dark:text-white">{{ $card['title'] }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $card['provedor'] }}</p>
                        </div>
                    </div>

                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $isActive ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ $estado }}
                    </span>
                </div>

                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Provider</dt>
                        <dd class="font-medium text-slate-800 dark:text-slate-200">{{ $integration?->provedor?->label() ?? 'Por configurar' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Último teste</dt>
                        <dd class="font-medium text-slate-800 dark:text-slate-200">
                            {{ $integration?->ultimo_teste_em?->format('d/m/Y H:i') ?? 'Nunca' }}
                        </dd>
                    </div>
                    @if($integration?->ultimo_teste_status)
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 dark:text-slate-400">Estado do teste</dt>
                            <dd class="font-medium text-slate-800 dark:text-slate-200">{{ $integration->ultimo_teste_status }}</dd>
                        </div>
                    @endif
                </dl>

                @if($integration?->ultimo_erro)
                    <p class="mt-4 rounded-lg bg-red-50 p-3 text-xs text-red-700">{{ $integration->ultimo_erro }}</p>
                @endif

                <div class="mt-5 flex flex-wrap gap-2">
                    <button type="button" wire:click="openConfigure('{{ $card['tipo'] }}', '{{ $card['provedor'] }}')" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Configurar
                    </button>

                    @if($integration)
                        <button type="button" wire:click="test({{ $integration->id }})" class="rounded-lg border border-blue-300 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                            Testar
                        </button>

                        @if($isActive)
                            <button type="button" wire:click="deactivate({{ $integration->id }})" class="rounded-lg border border-amber-300 px-3 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50">
                                Desactivar
                            </button>
                        @else
                            <button type="button" wire:click="activate({{ $integration->id }})" class="rounded-lg border border-green-300 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-50">
                                Activar
                            </button>
                        @endif
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    <x-ui.modal id="empresa-integracao-config" title="Configurar integração">
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">API URL</label>
                    <input wire:model.defer="form.config.api_url" type="url" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    @error('form.config.api_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Ambiente</label>
                    <select wire:model.defer="form.config.ambiente" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                        <option value="teste">Teste</option>
                        <option value="producao">Produção</option>
                    </select>
                    @error('form.config.ambiente') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Timeout</label>
                    <input wire:model.defer="form.config.timeout" type="number" min="1" max="120" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tentativas</label>
                    <input wire:model.defer="form.config.retry_attempts" type="number" min="0" max="5" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Intervalo entre tentativas (ms)</label>
                    <input wire:model.defer="form.config.retry_sleep" type="number" min="0" max="10000" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                @if($selectedProvedor === 'hongayetu_facturacao')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Empresa fiscal ID</label>
                        <input wire:model.defer="form.config.empresa_fiscal_id" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">NIF</label>
                        <input wire:model.defer="form.config.nif" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Estratégia de idempotência</label>
                        <input wire:model.defer="form.config.idempotency_strategy" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">API token</label>
                        <input wire:model.defer="form.credentials.api_token" type="password" placeholder="{{ $form['masked_credentials']['api_token'] ?? 'Novo token' }}" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">API key</label>
                        <input wire:model.defer="form.credentials.api_key" type="password" placeholder="{{ $form['masked_credentials']['api_key'] ?? 'Nova API key' }}" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                @elseif(in_array($selectedProvedor, ['meta_whatsapp', 'generic_whatsapp'], true))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Idioma padrão</label>
                        <input wire:model.defer="form.config.default_language" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Phone number ID</label>
                        <input wire:model.defer="form.credentials.phone_number_id" type="password" placeholder="{{ $form['masked_credentials']['phone_number_id'] ?? 'Novo valor' }}" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Business account ID</label>
                        <input wire:model.defer="form.credentials.business_account_id" type="password" placeholder="{{ $form['masked_credentials']['business_account_id'] ?? 'Novo valor' }}" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Webhook verify token</label>
                        <input wire:model.defer="form.credentials.webhook_verify_token" type="password" placeholder="{{ $form['masked_credentials']['webhook_verify_token'] ?? 'Novo token' }}" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Access token</label>
                        <input wire:model.defer="form.credentials.access_token" type="password" placeholder="{{ $form['masked_credentials']['access_token'] ?? 'Novo token' }}" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Templates</label>
                        <textarea wire:model.defer="form.config.templates" rows="3" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
                    </div>
                @else
                    <div class="md:col-span-2 rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        Este provider fica registado na base comum de integrações. Campos específicos serão adicionados quando a migração operacional do provider for aprovada.
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                <button type="button" x-on:click="open = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Cancelar
                </button>
                <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    Guardar
                </button>
            </div>
        </form>
    </x-ui.modal>
</div>
