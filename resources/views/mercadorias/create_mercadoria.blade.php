<x-app-layout>
    @php
        $fob = 0;
        $seguro = 0;
        $frete = 0;
        $CIF = 0;
    @endphp

    @if(request()->has('licenciamento_id'))
        <x-breadcrumb :items="[
            ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')],
            ['name' => 'Visualizar Licenciamento', 'url' => route('licenciamentos.show', request()->get('licenciamento_id'))],
            ['name' => 'Nova Mercadoria', 'url' => '']
        ]" separator="/" />

        @php
            $licenciamento = App\Models\Licenciamento::find(request()->get('licenciamento_id'));
            $fob = $licenciamento->fob_total;
            $seguro = $licenciamento->seguro;
            $frete = $licenciamento->frete;
            $CIF = $fob + $seguro + $frete;
        @endphp
    @endif

    <form action="{{ route('mercadorias.store') }}" method="POST" id="formNovaMercadoria">
        @csrf
        @if(request()->has('licenciamento_id'))
            <input type="hidden" name="licenciamento_id" value="{{ request()->get('licenciamento_id') }}">
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Coluna principal: formulário (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">📦 Adicionar Mercadoria</h3>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                            Salvar Mercadoria
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Linha 1: Tipo e Código Aduaneiro -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Mercadoria</label>
                                <select id="subcategoria_id" name="subcategoria_id" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($sub_categorias as $subcategoria)
                                        <option value="{{ $subcategoria->id }}" data-code="{{ $subcategoria->cod_pauta }}">
                                            {{ $subcategoria->cod_pauta }} - {{ $subcategoria->descricao }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código Aduaneiro</label>
                                <div class="relative">
                                    <input type="text" id="codigo_aduaneiro" name="codigo_aduaneiro"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        list="pauta_list" required>
                                    <span class="erro_pauta text-xs text-red-600 absolute -bottom-5 left-0"></span>
                                </div>
                                <datalist id="pauta_list"></datalist>
                            </div>
                        </div>

                        <!-- Linha 2: Quantidade, Peso, Valor Unitário, Total -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade *</label>
                                <input type="number" id="Quantidade" name="Quantidade" step="0.01"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ex.: 10.01" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Peso (Kg)</label>
                                <input type="number" id="Peso" name="Peso" step="0.01" value="0"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ex.: 500.50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Unitário (Moeda)</label>
                                <input type="number" id="preco_unitario" name="preco_unitario" step="0.01"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Ex.: 1000.00" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total (FOB)</label>
                                <input type="number" id="preco_total" name="preco_total" step="0.01"
                                    class="w-full rounded-md border-gray-300 shadow-sm bg-gray-100"
                                    readonly>
                            </div>
                        </div>

                        <!-- Descrição -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição da Mercadoria</label>
                            <input type="text" name="Descricao"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        <!-- Seções adicionais (Veículos / Máquinas) -->
                        <div id="info_veiculos" class="hidden space-y-4 border-t pt-4">
                            <h4 class="text-md font-semibold text-gray-800">Detalhes do Veículo</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                                    <input type="text" name="marca" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                                    <input type="text" name="modelo" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nº do Chassis</label>
                                    <input type="text" name="chassis" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ano de Fabricação</label>
                                    <input type="number" name="ano_fabricacao" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">H.S Code</label>
                                    <input type="text" name="hs_code" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>
                        </div>

                        <div id="info_maquina" class="hidden space-y-4 border-t pt-4">
                            <h4 class="text-md font-semibold text-gray-800">Detalhes da Máquina</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                                    <input type="text" name="marca_maquina" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Potência (kW)</label>
                                    <input type="number" step="0.01" name="potencia" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna lateral: Detalhes e progresso (1/3) -->
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-6">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200" id="card-header">
                        <div class="flex justify-between items-center">
                            <h4 class="font-semibold text-blue-800"><i class="fas fa-file-invoice-dollar"></i> Detalhes do Licenciamento</h4>
                            <span class="px-2 py-1 text-xs rounded-full" id="status-badge">Analisando...</span>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm text-gray-500">FOB Total</label>
                            <p class="text-xl font-bold text-blue-600">Kz {{ number_format($fob, 2) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-500">Somatório das Mercadorias</label>
                            <p class="text-xl font-bold text-green-600">Kz {{ number_format($somaPrecoTotal, 2) }}</p>
                        </div>
                        <hr>
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Progresso</span>
                                <span id="progress-percent">{{ number_format($porcentagem, 2) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div id="progress-bar" class="h-2.5 rounded-full transition-all duration-300" style="width: {{ $porcentagem }}%;"></div>
                            </div>
                            <p id="progress-message" class="text-xs text-gray-500 mt-2"></p>
                        </div>
                        @if($porcentagem > 100)
                            <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded">
                                <p class="text-red-700 text-sm"><i class="fas fa-exclamation-triangle"></i> Atenção! O total das mercadorias excede o valor FOB.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Secção de Adições Agrupadas (tabela expandível) -->
    <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">📋 Adições Agrupadas</h3>
            <button id="pauta_mercadoria" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md text-sm hover:bg-indigo-200 transition" data-toggle="modal" data-target="#PautaModal">
                <i class="fas fa-book"></i> Pauta Aduaneira
            </button>
        </div>
        <div class="p-0 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código Aduaneiro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantidade Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peso (Kg)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qtd Itens</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($mercadoriasAgrupadas as $agrupamento)
                        <tr class="hover:bg-gray-50 cursor-pointer" data-widget="expandable-table" aria-expanded="false">
                            <td class="px-6 py-4 text-sm">{{ $agrupamento->codigo_aduaneiro }}</td>
                            <td class="px-6 py-4 text-sm">{{ $agrupamento->quantidade_total }}</td>
                            <td class="px-6 py-4 text-sm">{{ $agrupamento->peso_total }}</td>
                            <td class="px-6 py-4 text-sm font-semibold">Kz {{ number_format($agrupamento->preco_total, 2) }}</td>
                            <td class="px-6 py-4 text-sm">{{ count($agrupamento->mercadorias) }}</td>
                        </tr>
                        <tr class="expandable-body hidden bg-gray-50">
                            <td colspan="5" class="px-6 py-4">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left">Descrição</th>
                                            <th class="px-4 py-2 text-left">Quantidade</th>
                                            <th class="px-4 py-2 text-left">Peso</th>
                                            <th class="px-4 py-2 text-left">Preço Total</th>
                                            <th class="px-4 py-2 text-center">Acções</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($agrupamento->mercadorias as $mercadoria)
                                            <tr id="mercadoria-{{ $mercadoria->id }}" class="border-t">
                                                <td class="px-4 py-2">{{ $mercadoria->Descricao }}</td>
                                                <td class="px-4 py-2">{{ $mercadoria->Quantidade }}</td>
                                                <td class="px-4 py-2">{{ $mercadoria->Peso }}</td>
                                                <td class="px-4 py-2">Kz {{ number_format($mercadoria->preco_total, 2) }}</td>
                                                <td class="px-4 py-2 text-center space-x-2">
                                                    <a href="#" class="btn-edit text-blue-600" data-id="{{ $mercadoria->id }}"><i class="fas fa-edit"></i></a>
                                                    <a href="#" class="btn-delete text-red-600" data-id="{{ $mercadoria->id }}"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Pauta Aduaneira -->
<div class="modal fade" id="PautaModal" tabindex="-1" aria-labelledby="PautaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PautaModalLabel">
                    <i class="fas fa-book"></i> Pauta Aduaneira - Códigos da Categoria
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="searchPauta" class="form-control mb-3" placeholder="Filtrar código ou descrição...">
                <div class="table-responsive" style="max-height: 60vh;">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr><th>Código</th><th>Descrição</th><th class="text-center">Selecionar</th></tr>
                        </thead>
                        <tbody id="pautaModalBody">
                            <tr><td colspan="3" class="text-center">Selecione uma categoria no campo "Tipo de Mercadoria".</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

    <script>
        // Carregar códigos da pauta ao abrir o modal, baseado na subcategoria selecionada
$('#PautaModal').on('show.bs.modal', function () {
    var subcategoriaId = $('#subcategoria_id').val();
    if (!subcategoriaId) {
        $('#pautaModalBody').html('<tr><td colspan="3" class="text-center text-warning">⚠️ Selecione uma categoria no campo "Tipo de Mercadoria".</td></tr>');
        return;
    }
    var cod_pauta = $('#subcategoria_id option:selected').data('code');
    if (!cod_pauta) {
        $('#pautaModalBody').html('<tr><td colspan="3" class="text-center text-warning">⚠️ Código da categoria não encontrado.</td></tr>');
        return;
    }

    // Chama o mesmo endpoint que já usas (ex: /get-codigo-aduaneiro/87)
    $.ajax({
        url: `${window.location.origin}/get-codigo-aduaneiro/${cod_pauta}`,
        method: 'GET',
        success: function(data) {
            if (!data || data.length === 0) {
                $('#pautaModalBody').html('<tr><td colspan="3" class="text-center">Nenhum código aduaneiro encontrado para esta categoria.</td></tr>');
                return;
            }
            var html = '';
            $.each(data, function(index, pauta) {
                html += `<tr>
                            <td>${pauta.codigo}</td>
                            <td>${pauta.descricao}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary selecionar-pauta" data-codigo="${pauta.codigo}">Selecionar</button>
                            </td>
                         </tr>`;
            });
            $('#pautaModalBody').html(html);
        },
        error: function() {
            $('#pautaModalBody').html('<tr><td colspan="3" class="text-center text-danger">Erro ao carregar os códigos.</td></tr>');
        }
    });
});

// Filtrar a tabela do modal
$('#searchPauta').on('keyup', function() {
    var valor = $(this).val().toLowerCase();
    $('#pautaModalBody tr').each(function() {
        var texto = $(this).text().toLowerCase();
        $(this).toggle(texto.indexOf(valor) > -1);
    });
});

// Selecionar código e preencher o campo principal, fechando o modal
$(document).on('click', '.selecionar-pauta', function() {
    var codigo = $(this).data('codigo');
    $('#codigo_aduaneiro').val(codigo);
    $('#codigo_aduaneiro').trigger('input'); // dispara validação (se existir)
    $('#PautaModal').modal('hide');
});
    </script>

    <!-- Scripts (mantidos os originais) -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Mantém toda a lógica original, apenas actualiza a estilização da barra de progresso via classes Tailwind
        $(document).ready(function() {
            $('#subcategoria_id').focus().css('border', '2px solid #007bff');

            $('#subcategoria_id').on('change', function() {
                var cod_pauta = $(this).find(':selected').data('code');
                $('#pauta_list').empty();
                if (cod_pauta) {
                    $.ajax({
                        url: `${window.location.origin}/get-codigo-aduaneiro/${cod_pauta}`,
                        method: 'GET',
                        success: function(data) {
                            $('#pauta_list').empty();
                            $.each(data, function(index, pauta) {
                                var codigoFormatado = pauta.codigo;
                                $('#pauta_list').append('<option value="' + codigoFormatado + '">' + codigoFormatado + ' - ' + pauta.descricao + '</option>');
                                if (cod_pauta === 87 || cod_pauta === 88) {
                                    if (codigoFormatado.startsWith('8701') || codigoFormatado.startsWith('8702') ||
                                        codigoFormatado.startsWith('8703') || codigoFormatado.startsWith('8704') ||
                                        codigoFormatado.startsWith('8705') || codigoFormatado.startsWith('8706') ||
                                        codigoFormatado.startsWith('8707') || codigoFormatado.startsWith('8709') ||
                                        codigoFormatado.startsWith('8711') || codigoFormatado.startsWith('8712') ||
                                        codigoFormatado.startsWith('8713')) {
                                        $('#info_veiculos').removeClass('hidden');
                                    }
                                } else if (cod_pauta === 84) {
                                    $('#info_maquina').removeClass('hidden');
                                } else {
                                    $('#info_veiculos, #info_maquina').addClass('hidden');
                                }
                            });
                        },
                        error: function() { alert('Erro ao carregar os códigos aduaneiros.'); }
                    });
                }
                $('#codigo_aduaneiro').focus().css('border', '2px solid #007bff');
            });
        });

        // Validação do código aduaneiro
        $(document).ready(function() {
            $('#codigo_aduaneiro').on('input', function() {
                var inputValue = $(this).val().trim();
                var isValid = false;
                var isIncomplete = false;
                $('#pauta_list option').each(function() {
                    var optionValue = $(this).val();
                    if (optionValue === inputValue) { isValid = true; }
                    if (optionValue.startsWith(inputValue) && optionValue.length > inputValue.length) { isIncomplete = true; }
                });
                if (!isValid || isIncomplete) {
                    $('.erro_pauta').text(isIncomplete ? 'Código incompleto! Digite o código completo.' : 'Código inválido! Selecione um da lista.');
                    $(this).addClass('is-invalid');
                } else {
                    $('.erro_pauta').text('');
                    $(this).removeClass('is-invalid');
                }
            });

            $('#formNovaMercadoria').on('submit', function(event) {
                var inputValue = $('#codigo_aduaneiro').val().trim();
                var isValid = false;
                var isIncomplete = false;
                $('#pauta_list option').each(function() {
                    var optionValue = $(this).val();
                    if (optionValue === inputValue) { isValid = true; }
                    if (optionValue.startsWith(inputValue) && optionValue.length > inputValue.length) { isIncomplete = true; }
                });
                if (!isValid || isIncomplete) {
                    event.preventDefault();
                    alert(isIncomplete ? 'Erro: Código incompleto! Digite o código completo.' : 'Erro: Código inválido. Escolha um da lista.');
                }
            });
        });

        // Cálculo automático do valor total
        document.getElementById('Quantidade').addEventListener('input', calcularValorTotal);
        document.getElementById('preco_unitario').addEventListener('input', calcularValorTotal);
        function calcularValorTotal() {
            var quantidade = parseFloat(document.getElementById('Quantidade').value) || 0;
            var precoUnitario = parseFloat(document.getElementById('preco_unitario').value) || 0;
            document.getElementById('preco_total').value = (quantidade * precoUnitario).toFixed(2);
        }

        // Barra de progresso (estilização dinâmica)
        document.addEventListener("DOMContentLoaded", function () {
            var porcentagem = {{ $porcentagem }};
            var progressBar = document.getElementById('progress-bar');
            var cardHeader = document.getElementById('card-header');
            var statusBadge = document.getElementById('status-badge');
            var progressMessage = document.getElementById('progress-message');

            if (porcentagem > 100) {
                progressBar.classList.add('bg-red-600');
                cardHeader.classList.add('bg-red-600', 'text-white');
                statusBadge.textContent = "Excedente!";
                statusBadge.classList.add('bg-red-600', 'text-white');
                progressMessage.innerHTML = "O somatório das mercadorias <strong>excedeu</strong> o limite permitido.";
            } else if (porcentagem >= 80) {
                progressBar.classList.add('bg-yellow-500');
                cardHeader.classList.add('bg-yellow-500');
                statusBadge.textContent = "Quase no limite";
                statusBadge.classList.add('bg-yellow-500', 'text-dark');
                progressMessage.innerHTML = "O valor das mercadorias está próximo ao limite do FOB.";
            } else {
                progressBar.classList.add('bg-green-600');
                cardHeader.classList.add('bg-green-600', 'text-white');
                statusBadge.textContent = "Dentro do Limite";
                statusBadge.classList.add('bg-green-600', 'text-white');
                progressMessage.innerHTML = "O somatório das mercadorias está dentro do permitido.";
            }
        });
    </script>

    <!-- Estilo adicional para a tabela expandível -->
    <style>
        .expandable-body td { padding: 0 !important; }
        .expandable-body table { width: 100%; margin: 0; }
        tr[data-widget="expandable-table"] { cursor: pointer; }
        .is-invalid { border-color: #dc2626 !important; }
        .erro_pauta { font-size: 0.75rem; margin-top: 0.25rem; display: block; }
    </style>
</x-app-layout>