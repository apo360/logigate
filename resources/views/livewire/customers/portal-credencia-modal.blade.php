<div>
    @if($open)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:key="portal-credentials-modal">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/60"
                     wire:click="closeModal"></div>

                <div class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                    <div class="mb-5 flex items-start justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">
                                Credenciais do Cliente Portal
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $customer?->CompanyName }}
                            </p>
                        </div>

                        <button type="button"
                                wire:click="closeModal"
                                class="rounded-lg px-2 py-1 text-gray-500 hover:bg-gray-100">
                            ✕
                        </button>
                    </div>

                    <div class="rounded-lg bg-blue-50 p-4 text-sm text-blue-800">
                        As credenciais permitem que o cliente aceda ao Portal do Cliente para consultar processos,
                        licenciamentos e documentos autorizados.
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <button type="button"
                                wire:click="createCredentials"
                                wire:loading.attr="disabled"
                                wire:target="createCredentials"
                                class="flex-1 rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="createCredentials">
                                Criar Credenciais
                            </span>
                            <span wire:loading wire:target="createCredentials">
                                A criar...
                            </span>
                        </button>

                        <button type="button"
                                wire:click="resetCredentials"
                                wire:loading.attr="disabled"
                                wire:target="resetCredentials"
                                class="flex-1 rounded-lg bg-amber-600 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="resetCredentials">
                                Redefinir Password
                            </span>
                            <span wire:loading wire:target="resetCredentials">
                                A redefinir...
                            </span>
                        </button>
                    </div>

                    @if($credentials)
                        <div class="mt-5 rounded-lg border border-green-200 bg-green-50 p-4 text-sm">
                            <h3 class="mb-2 font-semibold text-green-800">
                                Resultado
                            </h3>

                            <div class="space-y-1 text-green-900">
                                <div>
                                    <strong>Utilizador:</strong>
                                    {{ $credentials['username'] ?? 'Não informado' }}
                                </div>

                                @if(!empty($credentials['password']))
                                    <div>
                                        <strong>Password temporária:</strong>
                                        {{ $credentials['password'] }}
                                    </div>
                                @endif

                                @if(!empty($credentials['message']))
                                    <div class="pt-2 text-green-700">
                                        {{ $credentials['message'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex justify-end">
                        <button type="button"
                                wire:click="closeModal"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
