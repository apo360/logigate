<div class="min-h-screen bg-slate-100 py-8 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="mb-8 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Finalizar pagamento</h1>
                <p class="text-sm text-slate-600">Logigate Aduaneiro</p>
            </div>

            <img src="{{ asset('dist/img/LOGIGATE.png') }}" alt="Logigate" class="h-12 w-auto">
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            <section class="rounded-lg bg-white p-6 shadow-sm">
                @include('checkout.error')

                @if(! $this->hasPayment)
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Metodo de pagamento</h2>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <button type="button"
                                wire:click="$set('method', 'GPO')"
                                class="rounded-lg border p-4 text-left transition {{ $method === 'GPO' ? 'border-blue-600 bg-blue-50' : 'border-slate-200 hover:border-blue-300' }}">
                                <div class="font-semibold text-slate-900">Multicaixa GPO</div>
                                <div class="mt-1 text-sm text-slate-600">Autorizar no MCX App pelo telefone.</div>
                            </button>

                            <button type="button"
                                wire:click="$set('method', 'REF')"
                                class="rounded-lg border p-4 text-left transition {{ $method === 'REF' ? 'border-blue-600 bg-blue-50' : 'border-slate-200 hover:border-blue-300' }}">
                                <div class="font-semibold text-slate-900">Referencia Multicaixa</div>
                                <div class="mt-1 text-sm text-slate-600">Gerar entidade e referencia.</div>
                            </button>
                        </div>
                    </div>

                    @if($method === 'GPO')
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-slate-700">Telefone GPO</label>
                            <div class="mt-2 flex">
                                <span class="inline-flex items-center rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-600">+244</span>
                                <input type="tel"
                                    wire:model.blur="phone"
                                    maxlength="9"
                                    class="w-full rounded-r-lg border-slate-300 focus:border-blue-600 focus:ring-blue-600"
                                    placeholder="9XXXXXXXX">
                            </div>
                            @error('phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <button type="button"
                        wire:click="submit"
                        wire:loading.attr="disabled"
                        wire:target="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-blue-700 px-5 py-3 font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60">
                        <span wire:loading.remove wire:target="submit">
                            {{ $method === 'GPO' ? 'Enviar pedido GPO' : 'Gerar referencia' }}
                        </span>
                        <span wire:loading wire:target="submit">Processando...</span>
                    </button>
                @else
                    @if($paymentStatus === 'paid')
                        @include('checkout.success')
                    @elseif(($paymentView['method'] ?? null) === 'REF')
                        @include('checkout.ref')
                    @else
                        @include('checkout.gpo')
                    @endif
                @endif
            </section>

            <aside class="rounded-lg bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Resumo</h2>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-600">Plano</dt>
                        <dd class="font-medium text-slate-900">{{ $paymentView['plan_name'] ?? 'Plano' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-600">Modalidade</dt>
                        <dd class="font-medium text-slate-900">{{ $paymentView['cycle_label'] ?? 'Mensal' }}</dd>
                    </div>
                    <div class="border-t border-slate-200 pt-3">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-600">Valor base</dt>
                            <dd class="font-medium text-slate-900">{{ number_format($this->baseAmount, 0, ',', '.') }} AOA</dd>
                        </div>
                        <div class="mt-2 flex justify-between gap-4">
                            <dt class="text-slate-600">IVA 14%</dt>
                            <dd class="font-medium text-slate-900">{{ number_format($this->vatAmount, 0, ',', '.') }} AOA</dd>
                        </div>
                    </div>
                    <div class="border-t border-slate-200 pt-3">
                        <div class="flex justify-between gap-4">
                            <dt class="font-semibold text-slate-900">Total</dt>
                            <dd class="text-xl font-bold text-blue-700">{{ number_format((float) ($paymentView['amount'] ?? 0), 0, ',', '.') }} AOA</dd>
                        </div>
                    </div>
                </dl>
            </aside>
        </div>
    </div>

    @if($this->shouldPoll)
        <div wire:poll.5s="refreshPaymentStatus" class="hidden"></div>
    @endif
</div>
