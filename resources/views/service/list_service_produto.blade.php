
<x-app-layout>
    <div class="py-12">

        <div class="card">
            <div class="card-header">
                <div class="float-left"></div>
                <div class="float-right">
                    <!-- Botão Cadastro de Processos -->
                    <div class="btn-group">
                        <a href="{{ route('produtos.create') }}" class="btn btn-sm btn-primary">Novo Serviço</a>
                        <a href="" class="btn btn-sm btn-default">Categorias</a>
                        <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter"></i> Opções
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                <li><a href="" class="dropdown-item"><i class="fas fa-download"></i> Importar (xlsx, csv)</a></li>
                                <li><a href="" class="dropdown-item"><i class="fas fa-upload"></i> Exportar (xlsx, csv)</a></li>
                                <li><a href="" class="dropdown-item"><i class="fas fa-file"></i> Tabela de Preços</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <x-input type="text" id="search" placeholder="Pesquisar Serviço por: Referência, Descrição, Preço" class="form-control" />
                    </div>
                    <div class="col-md-2">
                        <select name="taxa_iva" id="taxa_iva" class="form-control">
                            <option value="" selected>Todas as Taxas</option>
                            @foreach($taxas as $taxa)
                                <option value="{{ $taxa->TaxType }}">
                                    {{ $taxa->Description }}
                                    {{ $taxa->TaxPercentage != 0 ? ' - '.intval($taxa->TaxPercentage) . '%' : '' }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="ProductType" id="ProductType" class="form-control">
                            <option value="" selected>Todos os Tipos</option>
                            @foreach($productTypes as $type)
                                <option value="{{ $type->code }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="sort_by" class="form-control">
                            <option value="">Ordenar por...</option>
                            <option value="preco_asc">Preço Ascendente</option>
                            <option value="preco_desc">Preço Descendente</option>
                            <option value="maior">Maior Facturação</option>
                            <option value="menor">Menor Facturação</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-body">
                    <table class="table table-sm table-stripped">
                        <thead>
                            <th></th>
                            <th>Tipo|Ref</th>
                            <th>Descrição</th>
                            <th>Preço S/Taxa</th>
                            <th>Taxa</th>
                            <th>Preço Venda</th>
                            <th>...</th>
                        </thead>
                        <tbody>
                            <!-- products -->
                            @foreach($products as $product)
                            <tr id="productRow_{{ $product->Id }}" >
                                <td>
                                    <a href="{{ route('produtos.edit', $product->Id) }}" style='margin:5px;' title="Editar Produto">
                                            <i class="fas fa-edit"></i>
                                    </a>
                                    <a onclick="deleteProduct({{ $product->Id }})" data-id="{{ $product->Id }}" type="button" data-toggle="modal" data-target="#exampleModalCentered" title="Excluir Produto">
                                        <i class="fas fa-trash" style="color: salmon;"></i>
                                    </a>
                                    <a href="">
                                        <i class="fas fa-eye" style="color: lightseagreen;"></i>
                                    </a>
                                </td>
                                <td>{{ $product->ProductType }} | {{ $product->ProductCode }}</td>
                                <td>{{ $product->ProductDescription }}</td>
                                <td>{{ number_format(floatval($product->venda_sem_iva), 2, ',','.') }} Kz</td>
                                <td>{{ number_format(floatval($product->imposto), 2, ',','.') }} %</td>
                                <td>{{ number_format(floatval($product->venda), 2, ',','.') }} Kz</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="card-footer">
                    
                </div>

            </div>
        </div>
    </div>

    <!-- Segundo Modal para Nova Categoria -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="addCategoryModalLabel">Adicionar Nova Categoria</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <!-- Formulário para adicionar nova categoria -->
            <form method="POST" class="" action="{{ route('insert.grupo.produto')}}">
                @csrf
            <!-- Campos do formulário -->
            <div class="row">
                <div class="col-md-12">
                    <x-input id="descricao" name="descricao" class="block mt-1 w-full" type="text" placeholder="Nova Categoria" />
                </div>
            </div>
            <!-- Botão para salvar e fechar o segundo modal -->
                <x-button type="submit" class="button ml-4">
                    {{ __('Salvar e Fechar') }}
                </x-button>
            </form>
        </div>
        </div>
    </div>
    </div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Script JavaScript para manipular a lógica do modal -->
<script>
  $(document).ready(function() {
    // Flag para evitar recursão infinita
    var isAddingCategory = false;

    // Evento ao alterar o valor do seletor ProductGroup
    // Delegação de evento para o seletor ProductGroup
    $('body').on('change', '#ProductGroup', function() {
        if ($(this).val() === 'add' && !isAddingCategory) {
            // Definir a flag para true
            isAddingCategory = true;
            
            // Fechar o primeiro modal
            //$('#largeModal').modal('hide');
            // Abrir o segundo modal para adicionar nova categoria
            $('#addCategoryModal').modal('show');
        }
    });

    // Evento ao fechar o segundo modal
    $('#addCategoryModal').on('hidden.bs.modal', function () {
        // Limpar o valor do input de nova categoria
        $('#newCategory').val('');
        // Reabrir o primeiro modal
        $('#largeModal').modal('show');

        // Resetar a flag para false
        isAddingCategory = false;
    });
  });

  // Função para salvar e fechar o segundo modal
  function saveAndCloseAddCategoryModal() {
    // Simulação: obtendo o valor da nova categoria do input
    var newCategoryValue = $('#newCategory').val();

    // Lógica para salvar a nova categoria (usando Ajax, por exemplo)
    // Aqui você deve implementar a lógica real para salvar a nova categoria

    // Atualizar o seletor ProductGroup no primeiro modal com a nova categoria (simulação)
    $('#ProductGroup').append('<option value="' + newCategoryValue + '">' + newCategoryValue + '</option>');

    // Fechar o segundo modal
    $('#addCategoryModal').modal('hide');
  }
</script>

<script>
    function deleteProduct(productId) {
        // Exibir um modal de confirmação, se desejar
        if (confirm("Tem certeza de que deseja excluir este produto?")) {
            // Enviar uma solicitação Ajax para excluir o produto
            $.ajax({
                url: route('produtos.destroy', productId), // Substitua pelo seu URL de rota de exclusão
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    // Remover a linha da tabela após a exclusão bem-sucedida
                    $('#productRow_' + productId).remove();
                },
                error: function (error) {
                    console.error('Erro ao excluir o produto:', error);
                }
            });
        }
    }
</script>

</x-app-layout>