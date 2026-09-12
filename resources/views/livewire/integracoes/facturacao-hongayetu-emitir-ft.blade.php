<!-- /var/www/logigate/resources/views/livewire/integracoes/facturacao-hongayetu-emitir-ft.blade.php -->
<div class="mx-auto max-w-8xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-700 dark:text-blue-300">Facturação Hongayetu</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Emitir FT Manual</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Emissão manual controlada de Factura Comercial externa, sem Contrato, Avença, Processo, pagamentos ou Conta Corrente.
            </p>
        </div>
        <a href="{{ route('integracoes.facturacao-hongayetu.facturas') }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
            Ver facturas
        </a>
    </header>

    <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900/70 dark:bg-red-950/40 dark:text-red-100">
        Esta acção emitirá uma Factura Comercial real no sistema Hongayetu. Confirme apenas se os dados estiverem correctos.
    </div>

    @if($successMessage)
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900/70 dark:bg-green-950/40 dark:text-green-100">
            {{ $successMessage }}
            @if($externalInvoiceNumber)
                <span class="font-semibold">Número externo: {{ $externalInvoiceNumber }}</span>
            @endif
        </div>
    @endif

    @error('emitir')
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900/70 dark:bg-red-950/40 dark:text-red-100">{{ $message }}</div>
    @enderror

    <form wire:submit.prevent="emitir" class="space-y-5">
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-5">
                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Cliente local</h2>
                        <a wire:click="abrirModalCliente" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-blue-300 px-3 text-sm font-semibold text-blue-700 hover:bg-blue-50 dark:border-blue-900/70 dark:text-blue-300 dark:hover:bg-blue-950/40">
                            + Cliente
                        </a>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tipo de Documento</label>
                            <div class="mt-2 grid gap-2">
                                @foreach($TipoDocumento as $tipo)
                                    <label class="flex cursor-pointer items-center justify-between rounded-lg border px-3 py-2 text-sm {{ (string) $form['tipo_documento'] === (string) $tipo->value ? 'border-blue-500 bg-blue-50 text-blue-800 dark:border-blue-400 dark:bg-blue-950/40 dark:text-blue-100' : 'border-slate-200 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800' }}">
                                        <span>{{ $tipo->label() }}</span>
                                        <input type="radio" wire:model.live="form.tipo_documento" value="{{ $tipo->value }}" class="text-blue-600">
                                    </label>
                                @endforeach
                            </div>
                            @error('form.tipo_documento') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cliente</label>
                            <select wire:model.live="form.customer_id" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                <option value="">Seleccione um cliente local</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente['id'] }}">
                                        {{ $cliente['id'] }} - {{ $cliente['name'] }} @if($cliente['nif']) - NIF {{ $cliente['nif'] }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('form.customer_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                            @if($customerMappingStatus)
                                <div class="mt-3 rounded-lg border {{ $form['external_customer_id'] ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-900/70 dark:bg-green-950/40 dark:text-green-100' : 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-100' }} p-3 text-sm">
                                    {{ $customerMappingStatus }}
                                    @if($form['external_customer_id'])
                                        <span class="font-semibold">ID externo: {{ $form['external_customer_id'] }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nome</label>
                                    <input wire:model.defer="form.cliente_nome" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                    @error('form.cliente_nome') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">NIF</label>
                                    <input wire:model.defer="form.cliente_nif" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                    @error('form.cliente_nif') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
                                    <input wire:model.defer="form.cliente_email" type="email" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                    @error('form.cliente_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Telefone</label>
                                    <input wire:model.defer="form.cliente_telefone" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                    @error('form.cliente_telefone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Linhas da factura</h2>
                        <button type="button" wire:click="addLine" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-blue-300 px-3 text-sm font-semibold text-blue-700 hover:bg-blue-50 dark:border-blue-900/70 dark:text-blue-300 dark:hover:bg-blue-950/40">
                            Adicionar Serviço/Produto
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                        <table class="min-w-[980px] w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                <tr>
                                    <th class="px-3 py-3 text-left font-semibold">Código</th>
                                    <th class="px-3 py-3 text-left font-semibold">Descrição</th>
                                    <th class="px-3 py-3 text-right font-semibold">Desconto</th>
                                    <th class="px-3 py-3 text-right font-semibold">Taxa</th>
                                    <th class="px-3 py-3 text-right font-semibold">Preço Unit.</th>
                                    <th class="px-3 py-3 text-right font-semibold">Qtd</th>
                                    <th class="px-3 py-3 text-right font-semibold">Subtotal</th>
                                    <th class="px-3 py-3 text-right font-semibold">Acções</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach($form['linhas'] as $index => $line)
                                    <tr class="align-top">
                                        <td class="px-3 py-3">
                                            <button type="button" wire:click="openProdutoModal({{ $index }})" class="mb-2 inline-flex min-h-8 w-full items-center justify-center rounded-md border border-slate-300 px-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                                Seleccionar
                                            </button>
                                            <input wire:model.defer="form.linhas.{{ $index }}.external_artigo_id" type="number" min="1" placeholder="ID artigo" class="w-28 rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                            @error("form.linhas.$index.external_artigo_id") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-3">
                                            <input wire:model.live.debounce.300ms="form.linhas.{{ $index }}.descricao" type="text" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                            @if(($line['produto_servico'] ?? '') !== '' && empty($line['external_artigo_id']))
                                                <p class="mt-2 rounded-md bg-amber-50 p-2 text-xs text-amber-800 dark:bg-amber-950/40 dark:text-amber-100">
                                                    Este item ainda não tem artigo externo mapeado.
                                                </p>
                                            @endif
                                            @error("form.linhas.$index.descricao") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            <input wire:model.live.debounce.300ms="form.linhas.{{ $index }}.desconto" type="number" step="0.01" min="0" class="w-28 rounded-md border-gray-300 text-right text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                            @error("form.linhas.$index.desconto") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-3 text-right text-slate-500">0%</td>
                                        <td class="px-3 py-3 text-right">
                                            <input wire:model.live.debounce.300ms="form.linhas.{{ $index }}.preco" type="number" step="0.01" min="0" class="w-32 rounded-md border-gray-300 text-right text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                            @error("form.linhas.$index.preco") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            <input wire:model.live.debounce.300ms="form.linhas.{{ $index }}.quantidade" type="number" step="0.0001" min="0.0001" class="w-24 rounded-md border-gray-300 text-right text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                            @error("form.linhas.$index.quantidade") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-3 text-right font-semibold text-slate-900 dark:text-white">
                                            {{ number_format($this->lineSubtotal($line), 2, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            <button type="button" wire:click="removeLine({{ $index }})" class="inline-flex min-h-8 items-center rounded-md border border-red-300 px-2 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-red-900/70 dark:text-red-300 dark:hover:bg-red-950/40">
                                                Remover
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <aside class="space-y-5">
                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Definições do documento</h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Data de emissão</label>
                            <input wire:model.defer="form.data_emissao" type="date" readonly class="mt-1 w-full rounded-md border-gray-300 bg-slate-50 text-slate-600 dark:border-gray-700 dark:bg-gray-950 dark:text-slate-300">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Vencimento</label>
                            <select wire:model.live="form.vencimento_opcao" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                <option value="0">Hoje</option>
                                <option value="15">15 dias</option>
                                <option value="30">30 dias</option>
                                <option value="45">45 dias</option>
                                <option value="60">60 dias</option>
                                <option value="90">90 dias</option>
                                <option value="especifica">Data específica</option>
                            </select>
                        </div>

                        @if(($form['vencimento_opcao'] ?? '') === 'especifica')
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Data específica</label>
                                <input wire:model.live="form.data_vencimento_especifica" type="date" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                @error('form.data_vencimento_especifica') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Data de expiração</label>
                            <input wire:model.defer="form.data_expiracao" type="date" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @error('form.data_expiracao') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estabelecimento ID</label>
                            <input wire:model.defer="form.estabelecimento_id" type="number" min="1" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @error('form.estabelecimento_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Metodo de pagamento</label>
                            <select wire:model.live="form.metodo_pagamento.tipo" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                <option value="">Seleccione um método de pagamento</option>
                                @foreach($MetodosPagamento as $metodo)
                                    <option value="{{ $metodo->value }}">{{ $metodo->label() }}</option>
                                @endforeach
                            </select>
                            @error('form.metodo_pagamento.tipo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        @if($showMetodoPagamentoSection)
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Banco ID</label>
                                    <select wire:model.defer="form.metodo_pagamento.banco_id" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                        <option value="">Seleccione um banco</option>
                                        @foreach($bancos as $banco)
                                            <option value="{{ $banco['id'] }}">{{ $banco['id'] }} - {{ $banco['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.metodo_pagamento.banco_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Referência</label>
                                    <input wire:model.defer="form.metodo_pagamento.referencia" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                    @error('form.metodo_pagamento.referencia') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Referências e observações</h2>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nossa referência</label>
                            <input wire:model.defer="form.nossa_referencia" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @error('form.nossa_referencia') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Vossa referência</label>
                            <input wire:model.defer="form.vossa_referencia" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @error('form.vossa_referencia') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Observações</label>
                            <textarea wire:model.defer="form.obs" rows="4" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
                            @error('form.obs') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Totais</h2>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Desconto (%)</label>
                            <input wire:model.live.debounce.300ms="form.desconto_percentual" type="number" step="0.01" min="0" max="100" class="mt-1 w-full rounded-md border-gray-300 text-right dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @error('form.desconto_percentual') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Desconto (Valor)</label>
                            <input wire:model.live.debounce.300ms="form.desconto_valor" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border-gray-300 text-right dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @error('form.desconto_valor') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Subtotal</dt>
                            <dd class="font-semibold text-slate-900 dark:text-white">{{ number_format($subtotal, 2, ',', '.') }} Kz</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500 dark:text-slate-400">Desconto global</dt>
                            <dd class="font-semibold text-red-700 dark:text-red-300">{{ number_format($descontoGlobal, 2, ',', '.') }} Kz</dd>
                        </div>
                        <div class="border-t border-slate-200 pt-3 dark:border-slate-700">
                            <div class="flex items-center justify-between">
                                <dt class="text-base font-semibold text-slate-900 dark:text-white">Total Geral</dt>
                                <dd class="text-xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($totalGeral, 2, ',', '.') }} Kz</dd>
                            </div>
                        </div>
                    </dl>

                    <label class="mt-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900/70 dark:bg-red-950/40 dark:text-red-100">
                        <input wire:model.live="form.confirmarEmissao" type="checkbox" class="mt-1 rounded border-red-300 text-red-700 focus:ring-red-700">
                        <span>Confirmo que os dados estão correctos para emissão na Hongayetu.</span>
                    </label>
                    @error('form.confirmarEmissao') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                    <button type="submit" wire:loading.attr="disabled" wire:target="emitir" class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg bg-red-700 px-4 text-sm font-semibold text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-70">
                        <i class="fas fa-spinner fa-pulse" wire:loading wire:target="emitir"></i>
                        <span wire:loading.remove wire:target="emitir">Emitir FT Hongayetu</span>
                        <span wire:loading wire:target="emitir">A emitir...</span>
                    </button>
                </section>
            </aside>
        </div>
    </form>

    @if($showProdutoModal)
        <div class="fixed inset-0 z-50 flex justify-end bg-slate-950/40">
            <button type="button" wire:click="closeProdutoModal" class="absolute inset-0 cursor-default" aria-label="Fechar modal"></button>

            <aside class="relative h-full w-full max-w-2xl overflow-y-auto bg-white p-5 shadow-2xl dark:bg-slate-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950 dark:text-white">Seleccionar Serviço/Produto</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Linha {{ ($produtoModalIndex ?? 0) + 1 }}</p>
                    </div>
                    <button type="button" wire:click="closeProdutoModal" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        Fechar
                    </button>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-[1fr_auto]">
                    <input wire:model.live.debounce.300ms="produtoSearch" type="search" placeholder="Pesquisar por nome, código ou ID externo" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    <div class="inline-flex rounded-lg border border-slate-200 p-1 dark:border-slate-700">
                        <button type="button" wire:click="setProdutoFilter('todos')" class="rounded-md px-3 py-2 text-sm font-semibold {{ $produtoFilter === 'todos' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800' }}">Todos</button>
                        <button type="button" wire:click="setProdutoFilter('service')" class="rounded-md px-3 py-2 text-sm font-semibold {{ $produtoFilter === 'service' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800' }}">Serviços</button>
                        <button type="button" wire:click="setProdutoFilter('product')" class="rounded-md px-3 py-2 text-sm font-semibold {{ $produtoFilter === 'product' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800' }}">Produtos</button>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($produtosFiltrados as $item)
                        <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $item['category'] }}</span>
                                        @if($item['code'])
                                            <span class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $item['code'] }}</span>
                                        @endif
                                        @if($item['external_artigo_id'])
                                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700 dark:bg-green-950/40 dark:text-green-100">ID {{ $item['external_artigo_id'] }}</span>
                                        @endif
                                    </div>
                                    <h3 class="mt-2 font-semibold text-slate-900 dark:text-white">{{ $item['name'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ number_format((float) $item['price'], 2, ',', '.') }} Kz</p>
                                </div>
                                <button type="button" wire:click="selectProdutoFromModal('{{ $item['value'] }}')" class="inline-flex min-h-9 items-center justify-center rounded-lg bg-blue-600 px-3 text-sm font-semibold text-white hover:bg-blue-700">
                                    Seleccionar
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-slate-200 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            Nenhum produto ou serviço encontrado.
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    @endif

    <!-- Modais de Cliente e Exportador -->
    <livewire:forms.cliente-quick-form />
</div>
