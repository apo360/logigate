<div class="mx-auto max-w-8xl space-y-6">

    {{-- Header --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-xl text-blue-700">
                        👤
                    </div>

                    <div>
                        <h1 class="text-xl font-bold text-slate-900">
                            Editar Cliente
                        </h1>
                        <p class="text-sm text-slate-500">
                            {{ $customer->CompanyName }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('customers.show', $customer->id) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    ← Voltar ao Cliente
                </a>

                <button type="submit"
                        form="customer-edit-form"
                        wire:loading.attr="disabled"
                        wire:target="update"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                    <span wire:loading.remove wire:target="update">
                        Guardar Alterações
                    </span>

                    <span wire:loading wire:target="update">
                        A guardar...
                    </span>
                </button>
            </div>
        </div>

        <div class="grid gap-4 px-6 py-4 text-sm md:grid-cols-4">
            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs font-medium uppercase text-slate-400">Código</p>
                <p class="mt-1 font-semibold text-slate-800">
                    {{ $customer->CustomerID ?? '—' }}
                </p>
            </div>

            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs font-medium uppercase text-slate-400">NIF</p>
                <p class="mt-1 font-semibold text-slate-800">
                    {{ $customer->CustomerTaxID ?? '—' }}
                </p>
            </div>

            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs font-medium uppercase text-slate-400">Tipo</p>
                <p class="mt-1 font-semibold text-slate-800">
                    {{ ucfirst($customer->tipo_cliente ?? '—') }}
                </p>
            </div>

            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs font-medium uppercase text-slate-400">Estado</p>
                <p class="mt-1">
                    @if($customer->is_active)
                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                            Activo
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                            Inactivo
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Form --}}
    <form id="customer-edit-form"
          wire:submit.prevent="update"
          class="space-y-6">

        {{-- Identificação --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">
                    Identificação
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Dados principais do cliente.
                </p>
            </div>

            <div class="grid gap-5 p-6 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">
                        AccountID
                    </label>
                    <input type="text"
                           wire:model.defer="form.AccountID"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.AccountID')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        NIF / Tax ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           wire:model.defer="form.CustomerTaxID"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.CustomerTaxID')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-slate-700">
                        Nome / Empresa <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           wire:model.defer="form.CompanyName"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.CompanyName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Contactos --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">
                    Contactos
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Dados de comunicação e presença digital.
                </p>
            </div>

            <div class="grid gap-5 p-6 md:grid-cols-3">
                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Telefone
                    </label>
                    <input type="text"
                           wire:model.defer="form.Telephone"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.Telephone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Email
                    </label>
                    <input type="email"
                           wire:model.defer="form.Email"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.Email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Website
                    </label>
                    <input type="text"
                           wire:model.defer="form.Website"
                           placeholder="https://exemplo.com"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.Website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Dados comerciais --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">
                    Dados Comerciais
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Informações usadas em processos, licenciamentos e operações.
                </p>
            </div>

            <div class="grid gap-5 p-6 md:grid-cols-3">
                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Tipo Cliente
                    </label>
                    <select wire:model.defer="form.tipo_cliente"
                            class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecionar</option>
                        <option value="importador">Importador</option>
                        <option value="exportador">Exportador</option>
                        <option value="ambos">Ambos</option>
                    </select>
                    @error('form.tipo_cliente')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Método Pagamento
                    </label>
                    <select wire:model.defer="form.metodo_pagamento"
                            class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecionar</option>
                        <option value="00">Pronto pagamento</option>
                        <option value="15">15 dias</option>
                        <option value="30">30 dias</option>
                        <option value="45">45 dias</option>
                    </select>
                    @error('form.metodo_pagamento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Moeda Operação
                    </label>
                    <select wire:model.defer="form.moeda_operacao"
                            class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecionar</option>
                        <option value="AOA">AOA - Kwanza</option>
                        <option value="USD">USD - Dólar</option>
                        <option value="EUR">EUR - Euro</option>
                    </select>
                    @error('form.moeda_operacao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Tipo Mercadoria
                    </label>
                    <input type="text"
                           wire:model.defer="form.tipo_mercadoria"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.tipo_mercadoria')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Frequência
                    </label>
                    <select wire:model.defer="form.frequencia"
                            class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecionar</option>
                        <option value="pontual">Pontual</option>
                        <option value="mensal">Mensal</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="semestral">Semestral</option>
                        <option value="anual">Anual</option>
                    </select>
                    @error('form.frequencia')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <input type="checkbox"
                           wire:model.defer="form.is_active"
                           id="is_active"
                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                    <label for="is_active" class="ml-3 text-sm font-medium text-slate-700">
                        Cliente activo
                    </label>
                </div>
            </div>
        </section>

        {{-- Documento pessoal --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">
                    Documento Pessoal
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Aplicável principalmente a clientes individuais.
                </p>
            </div>

            <div class="grid gap-5 p-6 md:grid-cols-4">
                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Nacionalidade
                    </label>
                    <input type="text"
                           wire:model.defer="form.nacionality"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.nacionality')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Tipo Documento
                    </label>
                    <select wire:model.defer="form.doc_type"
                            class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecionar</option>
                        <option value="BI">BI</option>
                        <option value="NIF">NIF</option>
                        <option value="PASSAPORTE">Passaporte</option>
                        <option value="CARTAO_RESIDENTE">Cartão Residente</option>
                        <option value="OUTRO">Outro</option>
                    </select>
                    @error('form.doc_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Número Documento
                    </label>
                    <input type="text"
                           wire:model.defer="form.doc_num"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.doc_num')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Validade Documento
                    </label>
                    <input type="date"
                           wire:model.defer="form.validade_date_doc"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.validade_date_doc')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Licença --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">
                    Licença
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Dados de licença comercial ou operacional do cliente.
                </p>
            </div>

            <div class="grid gap-5 p-6 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Nº Licença
                    </label>
                    <input type="text"
                           wire:model.defer="form.num_licenca"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.num_licenca')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Validade Licença
                    </label>
                    <input type="date"
                           wire:model.defer="form.validade_licenca"
                           class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('form.validade_licenca')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Observações --}}
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">
                    Observações
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Notas internas sobre o cliente.
                </p>
            </div>

            <div class="p-6">
                <textarea wire:model.defer="form.observacoes"
                          rows="5"
                          class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Escreva observações internas sobre o cliente..."></textarea>

                @error('form.observacoes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </section>

        {{-- Footer actions --}}
        <div class="sticky bottom-0 z-10 rounded-2xl border border-slate-200 bg-white/95 px-6 py-4 shadow-lg backdrop-blur">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-slate-500">
                    Confirme os dados antes de guardar as alterações.
                </p>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('customers.show', $customer->id) }}"
                       class="rounded-lg border border-slate-300 bg-white px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Cancelar
                    </a>

                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="update"
                            class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <span wire:loading.remove wire:target="update">
                            Actualizar Cliente
                        </span>

                        <span wire:loading wire:target="update">
                            A actualizar...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>