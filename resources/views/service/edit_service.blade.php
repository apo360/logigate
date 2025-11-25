<x-app-layout>

    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Serviços/Produtos', 'url' => route('produtos.index')],
        ['name' => 'Editar Produto', 'url' => '']
    ]" separator="/" />

    <div class="max-w-7xl mx-auto px-4 py-6 ">

        <x-validation-errors class="mb-4" />

        <form method="POST" enctype="multipart/form-data"
              action="{{ route('produtos.update', $produto->id) }}">
            @csrf
            @method('PUT')

            <!-- ====================== -->
            <!--   TABS PRINCIPAIS      -->
            <!-- ====================== -->

            <div x-data="{ tab: 'info' }" class="w-full">

                <!-- TAB HEADERS -->
                <div class="relative border-b border-gray-200">
                    <nav class="flex space-x-8">

                        <!-- TAB ITEM -->
                        <button type="button" @click="tab = 'info'"
                                class="py-3 px-1 text-sm font-medium"
                                :class="tab === 'info' ? 'text-blue-600' : 'text-gray-500'">
                            Info
                        </button>

                        <button type="button" @click="tab = 'precos'"
                                class="py-3 px-1 text-sm font-medium"
                                :class="tab === 'precos' ? 'text-blue-600' : 'text-gray-500'">
                            Preços
                        </button>

                        <button type="button" @click="tab = 'tabela'"
                                class="py-3 px-1 text-sm font-medium"
                                :class="tab === 'tabela' ? 'text-blue-600' : 'text-gray-500'">
                            Tabela de Preços
                        </button>

                        <button type="button" @click="tab = 'imagem'"
                                class="py-3 px-1 text-sm font-medium"
                                :class="tab === 'imagem' ? 'text-blue-600' : 'text-gray-500'">
                            Imagem
                        </button>

                        <button type="button" @click="tab = 'fiscal'"
                                class="py-3 px-1 text-sm font-medium"
                                :class="tab === 'fiscal' ? 'text-blue-600' : 'text-gray-500'">
                            Dados Fiscais
                        </button>

                    </nav>

                    <!-- ANIMAÇÃO HIGHLIGHT -->
                    <div aria-hidden="true" 
                        class="absolute bottom-0 h-0.5 bg-blue-600 transition-all duration-300"
                        :style="{
                            width:
                                tab === 'info' ? '60px' :
                                tab === 'precos' ? '70px' :
                                tab === 'tabela' ? '115px' :
                                tab === 'imagem' ? '70px' :
                                '110px',
                            transform:
                                tab === 'info' ? 'translateX(0px)' :
                                tab === 'precos' ? 'translateX(80px)' :
                                tab === 'tabela' ? 'translateX(165px)' :
                                tab === 'imagem' ? 'translateX(300px)' :
                                'translateX(380px)'
                        }"
                    ></div>
                </div>

                <!-- ====================== -->
                <!--       TAB: INFO        -->
                <!-- ====================== -->
                <div x-show="tab === 'info'" class="mt-6 space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <x-label for="ProductCode" value="Código do Produto" />
                            <x-input name="ProductCode" value="{{ $produto->ProductCode }}" />
                        </div>

                        <div>
                            <x-label for="ProductNumberCode" value="Código de Barras" />
                            <x-input name="ProductNumberCode" value="{{ $produto->ProductNumberCode }}" />
                        </div>

                    </div>

                    <div>
                        <x-label for="ProductDescription" value="Descrição" />
                        <x-input name="ProductDescription"
                                 value="{{ $produto->ProductDescription }}" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <x-label for="ProductType" value="Tipo" />
                            <select name="ProductType" class="w-full rounded border-gray-300">
                                @foreach ($productTypes as $type)
                                    <option value="{{ $type->code }}"
                                            @if($produto->ProductType === $type->code) selected @endif>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-label for="ProductGroup" value="Categoria" />
                            <select name="ProductGroup" class="w-full rounded border-gray-300">
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    @if($cat->id == $produto->ProductGroup) selected @endif>
                                    {{ $cat->descricao }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                </div>

                <!-- ====================== -->
                <!--       TAB: PREÇOS      -->
                <!-- ====================== -->
                <div x-show="tab === 'precos'" class="mt-6 space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                        <div>
                            <x-label value="Preço de Custo" />
                            <x-input type="text" name="preco_custo"
                                     value="{{ $produto->price->custo ?? '' }}" />
                        </div>

                        <div>
                            <x-label value="Preço de Venda" />
                            <x-input type="text" name="preco_venda"
                                     value="{{ $produto->price->venda ?? '' }}" />
                        </div>

                        <div>
                            <x-label value="Margem de Lucro (%)" />
                            <x-input type="text" name="margem_lucro"
                                     value="{{ $produto->price->lucro ?? '' }}" />
                        </div>

                        <div>
                            <x-label value="Preço sem IVA" />
                            <x-input type="text" name="preco_sem_iva"
                                     value="{{ $produto->price->venda_sem_iva ?? '' }}" />
                        </div>

                    </div>

                </div>

                <!-- ====================== -->
                <!--   TAB: TABELA PREÇO    -->
                <!-- ====================== -->
                <div x-show="tab === 'tabela'" class="mt-6">

                    <x-label value="Histórico de Preços" />

                    <div class="mt-4 bg-white shadow rounded-lg overflow-hidden">

                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="p-3 text-left">Preço</th>
                                    <th class="p-3 text-left">Imposto</th>
                                    <th class="p-3 text-left">Lucro</th>
                                    <th class="p-3 text-left">Data</th>
                                    <th class="p-3 text-left">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="p-3">{{ number_format($produto->price->venda, 2) }} AOA</td>
                                    <td class="p-3">{{ $produto->price->imposto }}</td>
                                    <td class="p-3">{{ number_format($produto->price->lucro, 2) }}%</td>
                                    <td class="p-3">{{ $produto->created_at }}</td>
                                    <td class="p-3"> <span class="bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs">Ativo</span> </td>
                                </tr>
                                @if($produto->price->history)
                                    @foreach($produto->price->priceHistory as $history)
                                        <tr class="border-b">
                                            <td class="p-3">{{ number_format($history->old_price, 2) }} AOA</td>
                                            <td class="p-3">{{ $history->old_tax }}</td>
                                            <td class="p-3">{{ number_format($history->lucro ?? 0, 2) }}%</td>
                                            <td class="p-3">{{ $history->created_at }}</td>
                                            <td class="p-3"> <span class="bg-red-200 text-red-600 py-1 px-3 rounded-full text-xs">Desativado</span> </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="p-3 text-center text-gray-500">
                                            Nenhum histórico de preços disponível ou existente.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                    </div>

                </div>

                <!-- ====================== -->
                <!--      TAB: IMAGEM       -->
                <!-- ====================== -->
                <div x-show="tab === 'imagem'" class="mt-6 space-y-6">

                    @if($produto->imagem_path)
                        <img src="{{ asset('storage/' . $produto->imagem_path) }}"
                             class="h-32 rounded shadow">
                    @endif

                    <x-label for="imagem" value="Alterar Imagem" />
                    <x-input type="file" name="imagem" />

                </div>

                <!-- ====================== -->
                <!--     TAB: FISCAL        -->
                <!-- ====================== -->
                <div x-show="tab === 'fiscal'" class="mt-6 space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <x-label value="Imposto (IVA)" />
                            <select name="taxa_iva" class="w-full rounded border-gray-300">
                                @foreach($taxas as $iva)
                                    <option value="{{ $iva->id }}">
                                        {{ $iva->TaxType }} - {{ $iva->TaxPercentage }}%
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-label value="Motivo de Isenção" />
                            <select name="motivo_isencao" class="w-full rounded border-gray-300">
                                @foreach($productExemptionReasons as $reason)
                                    <option value="{{ $reason->id }}">
                                        {{ $reason->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                </div>

            </div>

            <!-- BOTÃO DE SALVAR -->
            <div class="mt-6">
                <x-button class="bg-blue-600 text-white hover:bg-blue-700">
                    Atualizar Produto
                </x-button>
            </div>

        </form>
    </div>

</x-app-layout>
