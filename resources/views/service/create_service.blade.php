<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Serviços/Produtos', 'url' => route('produtos.index')],
        ['name' => 'Novo Serviço/Produto' , 'url' => '']
    ]" separator="/" />
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Novo Serviço/Produto') }}
        </h2>
    </x-slot>

    <div class="container">
        <x-validation-errors class="mb-4" />
        <!-- Formulário -->
        <form method="POST" action="{{ route('produtos.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="card">
                <div class="card-header">
                    <!-- Botões de Ação -->
                    <div class="float-left">
                        <a class="ml-4 btn btn-sm btn-secondary" href="{{ route('produtos.index') }}">
                            {{ __('Pesquisar') }}
                        </a>
                    </div>
                    <div class="float-right">
                        <x-button type="submit" class="ml-4 btn btn-primary">
                            {{ __('Inserir') }}
                        </x-button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <!-- Código do Produto -->
                    <div class="row">
                        <div class="col-md-4">
                            <x-label for="ProductCode" value="{{ __('Código') }}" />
                            <x-input id="ProductCode" class="block mt-1 w-full" type="text" name="ProductCode" placeholder="Código do Produto" required />
                        </div>
                    </div>

                    <div class="row">
                        <!-- Tipo de Produto -->
                        <div class="col-md-4">
                            <x-label for="ProductType" value="{{ __('Tipo') }}" />
                            <select name="ProductType" id="ProductType" class="form-control">
                                @foreach($productTypes as $type)
                                    <option value="{{ $type->code }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Categoria -->
                        <div class="col-md-4">
                            <x-label for="ProductGroup" value="{{ __('Categoria') }}" />
                            <select name="ProductGroup" id="ProductGroup" class="form-control">
                                <option value="null">Sem Categoria</option>
                                <option value="add">+ Categoria</option>
                                <!-- Exibir as opções de categorias -->
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->descricao }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <!-- Nome e Descrição -->
                        <div class="col-md-8">
                            <x-label for="ProductDescription" value="{{ __('Nome do Serviço / Produto') }}" />
                            <x-input id="ProductDescription" class="block mt-1 w-full" type="text" name="ProductDescription" placeholder="Nome do Produto/Serviço" required />
                        </div>

                        <!-- Código de Barras -->
                        <div class="col-md-4" id="ProductNumberCode">
                            <x-label for="ProductNumberCode" value="{{ __('Código de Barras') }}" />
                            <x-input id="ProductNumberCode" class="block mt-1 w-full" type="text" name="ProductNumberCode" placeholder="Código de Barras" />
                        </div>
                    </div>

                    <div class="row">
                        <!-- Imagem -->
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="imagem" value="{{ __('Imagem') }}" />
                                <x-input id="imagem" class="block mt-1 w-full" type="file" name="imagem" />
                            </div>
                        </div>

                        <!-- Unidade -->
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="unidade" value="{{ __('Unidade') }}" />
                                <select name="unidade" id="unidade" class="form-control">
                                    <option value="uni">Unidade</option>
                                    <option value="kg">Kilograma (Kg)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Imposto -->
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="taxa_iva" value="{{ __('Imposto') }}" />
                                <select name="taxa_iva" id="taxa_iva" class="form-control">
                                    @foreach($taxas as $taxa)
                                        <option value="{{ $taxa->id }}" data-type = "{{ $taxa->TaxType }}" data-percetagem = "{{$taxa->TaxPercentage}}">
                                            {{ $taxa->TaxType }} - 
                                            {{ $taxa->TaxPercentage != 0 ? intval($taxa->TaxPercentage) . '%' : '' }} 
                                            ({{ $taxa->Description }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Motivo de Isenção -->
                        <div class="col-md-4 mt-2" id="motivo_isencao_container" style="display:none;">
                            <x-label for="motivo_isencao" value="{{ __('Motivo de Isenção') }}" />
                            <select name="reasonID" id="motivo_isencao" class="form-control">
                                @foreach($productExemptionReasons as $reason)
                                    <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dedutível de IVA -->
                        <div class="col-md-4 mt-2">
                            <div class="form-group">
                                <x-label for="dedutivel_iva" value="{{ __('IVA Dedutível') }}" />
                                <input type="text" class="form-control" id="dedutivel_iva" name="dedutivel_iva" value="{{ old('dedutivel_iva') }}">
                                
                                <!-- Texto explicativo -->
                                <small class="form-text text-muted">
                                    Indique a porcentagem do IVA que pode ser deduzida. Use valores entre 0 e 100. 
                                    Deixe em branco se o IVA não for dedutível.
                                </small>
                                
                                <!-- Exibição de erros de validação -->
                                @if ($errors->has('dedutivel_iva'))
                                    <span class="text-danger">{{ $errors->first('dedutivel_iva') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Preços e Margens -->
                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="preco_custo" value="{{ __('Preço de Custo') }}" />
                                <x-input type="text" name="custo" id="preco_custo" class="block mt-1 w-full" placeholder="Preço de Custo" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="preco_venda" value="{{ __('Preço de Venda') }}" />
                                <x-input type="text" name="venda" id="preco_venda" class="block mt-1 w-full" placeholder="Preço de Venda" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="margem_lucro" value="{{ __('Margem de Lucro') }}" />
                                <x-input type="text" name="lucro" id="margem_lucro" class="block mt-1 w-full" placeholder="Margem de Lucro" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="preco_sem_iva" value="{{ __('Preço sem IVA') }}" />
                                <x-input type="text" name="venda_sem_iva" id="preco_sem_iva" class="block mt-1 w-full" placeholder="Preço sem IVA" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ------------------------------------- Modais -------------------------------------- -->
    <!-- Modal para criar nova categoria -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1" role="dialog" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryModalLabel">Criar Nova Categoria</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="createCategoryForm" method="POST" action="{{ route('insert.grupo.produto') }}">
                        @csrf
                        <div class="form-group">
                            <label for="newCategoryName">Nome da Categoria</label>
                            <input type="text" class="form-control" id="newCategoryName" name="newCategoryName" placeholder="Digite o nome da nova categoria" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Selecione o formulário
        const formE = document.getElementById('createCategoryForm');

        // Adicione um event listener para o envio do formulário
        formE.addEventListener('submit', async (event) => {
            // Impedir o envio padrão do formulário
            event.preventDefault();

            // Enviar o formulário via AJAX
            const formData = new FormData(formE);
            const url = formE.action;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                // Verificar se a resposta é bem-sucedida
                if (response.ok) {
                    // Converter a resposta para JSON
                    const data = await response.json();

                    // Exibir a mensagem de retorno usando Toastr
                    toastr.success(data.message); // Exibir mensagem de sucesso
                    $("#createCategoryForm")[0].reset();  // Reset form
                    $('#createCategoryModal').modal('hide');  // Hide modal
                    $('#ProductGroup').append('<option value="' + data.categoria_id + '">' + data.categoria_desc + '</option>');
                } else {
                    // Se a resposta não for bem-sucedida, exibir uma mensagem de erro genérica
                    toastr.error('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
                }
            } catch (error) {
                console.error('Erro ao enviar formulário:', error);
                // Em caso de erro, exibir uma mensagem de erro genérica
                toastr.error('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
            }
        });
    </script>
    <!-- -------------------------------------------------------------------- -->

    <!-- -----------------------Scripts-------------------------------------- -->

    <!-- Ensure you have included jQuery and Bootstrap JS at the end of the body tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Função para verificar o tipo de produto e mostrar/esconder os campos
            function toggleFieldsByProductType() {
                var productType = $('#ProductType').val();
                
                if (productType === 'S') {
                    // Esconder campos que não são necessários para serviços
                    $('#unidade').closest('.row').hide();
                    $('#imagem').closest('.row').hide();
                    $('#ProductNumberCode').hide();
                } else {
                    // Mostrar campos para produtos que não sejam serviços
                    $('#unidade').closest('.row').show();
                    $('#imagem').closest('.row').show();
                    $('#ProductNumberCode').show();
                }
            }

            // Função para verificar o valor selecionado no select
            function toggleMotivoIsencao() {
                // Pega a opção selecionada
                var selectedOption = $('#taxa_iva option:selected');
                
                // Pega o atributo data-type da opção selecionada
                var taxType = selectedOption.data('type'); 

                // Se o tipo for "NS", mostra o campo "Motivo de Isenção"
                if (taxType === 'NS') {
                    $('#motivo_isencao_container').show(); // Mostra o campo
                } else {
                    $('#motivo_isencao_container').hide(); // Oculta o campo
                }
            }

            // Chama a função ao carregar a página
            toggleMotivoIsencao();

            // Monitora a mudança no select de taxa_iva
            $('#taxa_iva').change(function() {
                toggleMotivoIsencao(); // Chama a função novamente quando o valor mudar
            });

            // Chamar a função ao carregar a página e ao mudar o tipo de produto
            toggleFieldsByProductType();
            $('#ProductType').on('change', function() {
                toggleFieldsByProductType();
            });

            // Quando a opção "+ Categoria" for selecionada
            $('#ProductGroup').on('change', function() {
                if ($(this).val() === 'add') {
                    // Abrir o modal para criar nova categoria
                    $('#createCategoryModal').modal('show');
                }
            });

        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const precoCustoInput = document.getElementById('preco_custo');
            const margemLucroInput = document.getElementById('margem_lucro');
            const precoVendaInput = document.getElementById('preco_venda');
            const precoSemIvaInput = document.getElementById('preco_sem_iva');
            const ivaDedutivelInput = document.getElementById('dedutivel_iva');
            const taxaIvaSelect = document.getElementById('taxa_iva');

            // Função para calcular o preço de venda com base no custo e na margem de lucro
            function calcularPrecoVenda() {
                const precoCusto = parseFloat(precoCustoInput.value) || 0;
                const margemLucro = parseFloat(margemLucroInput.value) || 0;
                const precoVenda = precoCusto + (precoCusto * (margemLucro / 100));
                precoVendaInput.value = precoVenda.toFixed(2);
                calcularPrecoSemIva();
            }

            // Função para calcular o preço sem IVA com base no preço de venda e a taxa de IVA selecionada
            function calcularPrecoSemIva() {
                const precoVenda = parseFloat(precoVendaInput.value) || 0;
                const ivaDedutivel = parseFloat(ivaDedutivelInput.value) || 0;

                // Obter a taxa de IVA selecionada
                const selectedTaxOption = taxaIvaSelect.options[taxaIvaSelect.selectedIndex];
                const taxaIva = parseFloat(selectedTaxOption.getAttribute('data-percetagem')) || 0;

                const precoSemIva = precoVenda / (1 + (taxaIva / 100));
                precoSemIvaInput.value = precoSemIva.toFixed(2);
            }

            // Atualizar o preço de venda ao alterar o custo ou a margem de lucro
            precoCustoInput.addEventListener('input', calcularPrecoVenda);
            margemLucroInput.addEventListener('input', calcularPrecoVenda);

            // Atualizar o preço sem IVA ao alterar o preço de venda, a taxa de IVA ou o IVA dedutível
            precoVendaInput.addEventListener('input', calcularPrecoSemIva);
            ivaDedutivelInput.addEventListener('input', calcularPrecoSemIva);
            taxaIvaSelect.addEventListener('change', calcularPrecoSemIva);
        });
    </script>

</x-app-layout>
