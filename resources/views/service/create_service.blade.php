<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Novo Serviço/Produto') }}
        </h2>
    </x-slot>

    <div class="container">
        <div class="">
            <div class="">
                <x-validation-errors class="mb-4" />
                
                <!-- Formulário -->
                <form method="POST" action="{{ route('produtos.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="card">
                        <div class="d-flex justify-content-between">
                            <!-- Botões de Ação -->
                            <div class="flex items-center justify-end">
                                <a class="ml-4 btn btn-sm btn-secondary" href="{{ route('produtos.index') }}">
                                    {{ __('Pesquisar') }}
                                </a>

                                <x-button type="submit" class="ml-4 btn btn-primary">
                                    {{ __('Inserir') }}
                                </x-button>
                            </div>
                        </div>
                    </div>

                    <!-- Código do Produto -->
                    <div class="col-md-4">
                        <div class="mt-2">
                            <x-label for="ProductCode" value="{{ __('Código') }}" />
                            <x-input id="ProductCode" class="block mt-1 w-full" type="text" name="ProductCode" placeholder="Código do Produto" required />
                        </div>
                    </div>

                    <div class="row">
                        <!-- Tipo de Produto -->
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="ProductType" value="{{ __('Tipo') }}" />
                                <select name="ProductType" id="ProductType" class="form-control">
                                    @foreach($productTypes as $type)
                                        <option value="{{ $type->code }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Categoria -->
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="ProductGroup" value="{{ __('Categoria') }}" />
                                <select name="ProductGroup" id="ProductGroup" class="form-control">
                                    <option value="null">Sem Categoria</option>
                                    <option value="add">+ Categoria</option>
                                    <!-- Exibir as opções de categorias -->
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->Descriptions }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Nome e Descrição -->
                        <div class="col-md-8">
                            <div class="mt-2">
                                <x-label for="ProductDescription" value="{{ __('Nome do Serviço / Produto') }}" />
                                <x-input id="ProductDescription" class="block mt-1 w-full" type="text" name="ProductDescription" placeholder="Nome do Produto/Serviço" required />
                            </div>
                        </div>

                        <!-- Código de Barras -->
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="ProductNumberCode" value="{{ __('Código de Barras') }}" />
                                <x-input id="ProductNumberCode" class="block mt-1 w-full" type="text" name="ProductNumberCode" placeholder="Código de Barras" />
                            </div>
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

                        <!-- Fatura -->
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="factura" value="{{ __('Incluir na Fatura') }}" />
                                <select name="factura" id="factura" class="form-control">
                                    <option value="nao">Não</option>
                                    <option value="sim">Sim</option>
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
                                        <option value="{{ $taxa->TaxType }}">
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
                            <select name="motivo_isencao" id="motivo_isencao" class="form-control">
                                @foreach($productExemptionReasons as $reason)
                                    <option value="{{ $reason->code }}">{{ $reason->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dedutível de IVA -->
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="dedutivel_iva" value="{{ __('Dedutível de IVA') }}" />
                                <x-input id="dedutivel_iva" class="block mt-1 w-full" type="text" name="dedutivel_iva" placeholder="100%" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Preços e Margens -->
                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="preco_custo" value="{{ __('Preço de Custo') }}" />
                                <x-input type="text" name="preco_custo" id="preco_custo" class="block mt-1 w-full" placeholder="Preço de Custo" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="preco_venda" value="{{ __('Preço de Venda') }}" />
                                <x-input type="text" name="preco_venda" id="preco_venda" class="block mt-1 w-full" placeholder="Preço de Venda" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="margem_lucro" value="{{ __('Margem de Lucro') }}" />
                                <x-input type="text" name="margem_lucro" id="margem_lucro" class="block mt-1 w-full" placeholder="Margem de Lucro" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="preco_sem_iva" value="{{ __('Preço sem IVA') }}" />
                                <x-input type="text" name="preco_sem_iva" id="preco_sem_iva" class="block mt-1 w-full" placeholder="Preço sem IVA" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
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
                    <form id="createCategoryForm" method="POST" class="" action="{{ route('insert.grupo.produto')}}">
                        @csrf
                        <div class="form-group">
                            <label for="newCategoryName">Nome da Categoria</label>
                            <input type="text" class="form-control" id="newCategoryName" placeholder="Digite o nome da nova categoria" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                    $('#ProductNumberCode').closest('.row').hide();
                } else {
                    // Mostrar campos para produtos que não sejam serviços
                    $('#unidade').closest('.row').show();
                    $('#imagem').closest('.row').show();
                    $('#ProductNumberCode').closest('.row').show();
                }
            }

            // Função para verificar o valor selecionado no select
            function toggleMotivoIsencao() {
                var selectedTax = $('#taxa_iva').val(); // Pega o valor selecionado
                
                // Se o valor selecionado for "NS", mostra o campo "Motivo de Isenção"
                if (selectedTax === 'NS') {
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

            // Função para criar nova categoria ao submeter o formulário no modal
            $('#createCategoryForm').on('submit', function(event) {
                event.preventDefault(); // Evitar o reload da página
                
                var newCategoryName = $('#newCategoryName').val(); // Obter o nome da nova categoria

                // Simulação de envio para o backend (você pode usar AJAX para salvar no backend)
                $.ajax({
                    url: '/categorias/criar',  // URL fictícia para criar a categoria
                    method: 'POST',
                    data: {
                        name: newCategoryName,
                        _token: $('input[name="_token"]').val()  // Token CSRF
                    },
                    success: function(response) {
                        // Supondo que o backend retorna o ID da nova categoria
                        var newCategoryId = response.id;

                        // Adicionar a nova categoria ao select
                        $('#ProductGroup').append(new Option(newCategoryName, newCategoryId));

                        // Selecionar automaticamente a nova categoria
                        $('#ProductGroup').val(newCategoryId);

                        // Fechar o modal
                        $('#createCategoryModal').modal('hide');
                    },
                    error: function() {
                        alert('Erro ao criar categoria!');
                    }
                });
            });


        });
    </script>
</x-app-layout>
