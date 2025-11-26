<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Serviços/Produtos', 'url' => route('produtos.index')],
        ['name' => 'Atualizar Preço', 'url' => '']
    ]" separator="/" />

    <div class="max-w-4xl mx-auto px-4 py-8">
        <x-validation-errors class="mb-4" />

        <!-- Container -->
        <div class="bg-white shadow-md rounded-lg border border-gray-100 p-6">

            <header class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Atualizar Preço</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Produto: <span class="font-medium">{{ $produto->ProductDescription }}</span>
                        • Código: <span class="font-medium">{{ $produto->ProductCode }}</span>
                    </p>
                </div>

                @if($produto->imagem_path)
                    <img src="{{ asset('storage/' . $produto->imagem_path) }}" alt="Imagem" class="h-16 w-16 object-cover rounded-md border" />
                @endif
            </header>

            <hr class="my-6">

            <div 
                x-data="priceUpdater({
                    currentPrice: {{ $produto->price->venda ?? ($produto->price->last()->price ?? 0) }},
                    hasFiscalUsage: {{ $temUsoFiscal ? 'true' : 'false' }}
                })"
                x-init="init()"
                class="space-y-6"
            >

                <!-- Current / New Price -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <div class="col-span-1 md:col-span-1">
                        <label class="text-sm text-gray-600">Preço Atual</label>
                        <div class="mt-1 text-lg font-semibold text-gray-800">
                            <span x-text="formatCurrency(currentPrice)"></span>
                            <input type="hidden" name="old_price" id="old_price" value="{{ $produto->price->venda }}" />
                        </div>
                    </div>

                    <div class="col-span-1 md:col-span-1">
                        <label for="new_price" class="text-sm text-gray-600">Novo Preço</label>
                        <input id="new_price" name="new_price" type="text"
                               x-model="newPriceInput"
                               @input="onNewPriceInput"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ex: 15000" />
                        <p class="text-xs text-gray-400 mt-1">Use apenas números — separador decimal: ponto (.) ou vírgula.</p>
                    </div>

                    <div class="col-span-1 md:col-span-1">
                        <label class="text-sm text-gray-600">Motivo</label>
                        <input type="text" x-model="motivo" placeholder="Ex: Ajuste por aumento de custo"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                </div>

                <!-- Preview (impact, classification, schedule) -->
                <div class="bg-gray-50 border border-gray-100 p-4 rounded-md">
                    <div class="flex items-start justify-between space-x-6">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500">Variação</p>
                            <p class="mt-1 text-lg font-semibold text-gray-800" x-text="(variationText())"></p>
                        </div>

                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500">Classificação (IA - preview)</p>
                            <p class="mt-1 text-lg font-semibold text-gray-800" x-text="classification"></p>
                        </div>

                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500">Reavaliação programada</p>
                            <p class="mt-1 text-lg font-semibold text-gray-800" x-text="nextReevaluation"></p>
                        </div>
                    </div>

                    <p class="mt-3 text-sm text-gray-500" x-show="!hasFiscalUsage">
                        <strong>Atenção:</strong> este produto <span class="font-medium">não tem</span> uso fiscal registado.  
                        Atualizações serão aplicadas simples — <span class="font-medium">sem histórico</span>, sem logs e sem IA.
                    </p>
                </div>

                <!-- Options -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <div class="col-span-1">
                        <label class="text-sm text-gray-600">Notificar Gestor</label>
                        <div class="mt-2 flex items-center space-x-3">
                            <label class="inline-flex items-center">
                                <input type="checkbox" x-model="notificar" :disabled="!hasFiscalUsage"
                                       class="rounded text-blue-600 border-gray-300 focus:ring-blue-500" />
                                <span class="ml-2 text-gray-700">Enviar notificação</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Disponível apenas para produtos com uso fiscal.</p>
                    </div>

                    <div class="col-span-2 md:col-span-2">
                        <label class="text-sm text-gray-600">Observações (opcional)</label>
                        <input type="text" x-model="observacoes"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Notas internas..." />
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('produtos.show', $produto->id) }}" class="px-4 py-2 rounded-md border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </a>

                    <button type="button"
                        @click="submit()"
                        :disabled="submitting || !isValidNewPrice"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 disabled:opacity-50"
                    >
                        <svg x-show="!submitting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <svg x-show="submitting" class="animate-spin w-4 h-4 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        Atualizar Preço
                    </button>
                </div>

                <!-- Hidden form that will be submitted to the server -->
                <form id="updatePriceForm" method="POST" action="{{ route('produtos.updatePrice', $produto->id) }}" class="hidden">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="new_price" x-bind:value="newPrice">
                    <input type="hidden" name="motivo" x-bind:value="motivo">
                    <input type="hidden" name="notificar" x-bind:value="notificar">
                    <input type="hidden" name="observacoes" x-bind:value="observacoes">
                </form>

            </div> <!-- end alpine scope -->

        </div> <!-- end container -->
    </div>

    <script>
        function priceUpdater({ currentPrice = 0, hasFiscalUsage = false }) {
            return {
                currentPrice: parseFloat(currentPrice) || 0,
                newPriceInput: '',
                newPrice: null,
                motivo: '',
                observacoes: '',
                notificar: false,
                hasFiscalUsage: hasFiscalUsage,
                classification: '-',
                nextReevaluation: '-', // date
                submitting: false,
                isValidNewPrice: false,

                init() {
                    // compute next re-evaluation date
                    const d = new Date();
                    d.setDate(d.getDate() + 30);
                    this.nextReevaluation = d.toLocaleDateString();
                },

                formatCurrency(value) {
                    value = parseFloat(value) || 0;
                    return new Intl.NumberFormat('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' AOA';
                },

                onNewPriceInput() {
                    // normalize input: replace comma with dot, strip non-numeric (except dot)
                    const raw = this.newPriceInput.toString().replace(',', '.').replace(/[^0-9.]/g, '');
                    this.newPrice = raw ? parseFloat(raw) : null;
                    this.isValidNewPrice = this.newPrice > 0;
                    this.computeImpactPreview();
                },

                variationPercent() {
                    if (!this.newPrice || !this.currentPrice || this.currentPrice == 0) return 0;
                    return ((this.newPrice - this.currentPrice) / this.currentPrice) * 100;
                },

                classificationByVariation(v) {
                    if (!this.hasFiscalUsage) return 'Sem avaliação (produto sem uso fiscal)';
                    if (v > 10) return 'Aumento Significativo';
                    if (v > 0) return 'Aumento Moderado';
                    if (v === 0) return 'Sem mudança';
                    if (v >= -10) return 'Redução Moderada';
                    return 'Redução Significativa';
                },

                computeImpactPreview() {
                    const v = parseFloat(this.variationPercent());
                    this.classification = this.classificationByVariation(v);
                },

                variationText() {
                    if (!this.newPrice) return 'Aguardando novo preço';
                    const v = this.variationPercent();
                    const sign = v > 0 ? '+' : '';
                    return `${sign}${v.toFixed(2)}%`;
                },

                async submit() {
                    if (!this.isValidNewPrice) {
                        alert('Por favor indique um novo preço válido.');
                        return;
                    }

                    const confirmMsg = this.hasFiscalUsage
                        ? 'Este produto tem uso fiscal — será criado histórico, log e IA será acionada. Deseja continuar?'
                        : 'Produto sem uso fiscal — a alteração será aplicada sem histórico. Deseja continuar?';

                    if (!confirm(confirmMsg)) return;

                    this.submitting = true;

                    // set notificar false if not allowed
                    if (!this.hasFiscalUsage) this.notificar = false;

                    // Populate hidden form & submit
                    document.querySelector('#updatePriceForm input[name="new_price"]').value = this.newPrice;
                    document.querySelector('#updatePriceForm input[name="motivo"]').value = this.motivo;
                    document.querySelector('#updatePriceForm input[name="notificar"]').value = this.notificar ? 1 : 0;
                    document.querySelector('#updatePriceForm input[name="observacoes"]').value = this.observacoes;

                    // submit the form (standard POST request)
                    document.getElementById('updatePriceForm').submit();
                }
            }
        }
    </script>
</x-app-layout>
@component('mail::message')