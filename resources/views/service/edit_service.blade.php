<x-app-layout>
    <x-slot name="header">
        <x-breadcrumb title="Editar Produto" breadcrumb="Editar Produto" />
    </x-slot>
    <br>

    <div class="col-md-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <x-validation-errors class="mb-4" />
                <form method="POST" action="{{ route('produtos.update', $produto->Id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <a href="">Actualizar</a>
                            </div>
                        </div>
                        <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active button" id="endereco-tab" data-bs-toggle="tab" data-bs-target="#endereco" type="button" role="tab" aria-controls="endereco" aria-selected="true">Endereço</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contrato-tab button" data-bs-toggle="tab" data-bs-target="#contrato" type="button" role="tab" aria-controls="contrato" aria-selected="false">Contrato</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="fiscal-tab button" data-bs-toggle="tab" data-bs-target="#fiscal" type="button" role="tab" aria-controls="fiscal" aria-selected="false">Dados Fiscais</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contabilidade-tab" data-bs-toggle="tab" data-bs-target="#contabilidade" type="button" role="tab" aria-controls="contabilidade" aria-selected="false">Contabilidade</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="endereco" role="tabpanel" aria-labelledby="endereco-tab">teste</div>
                        <div class="tab-pane fade show" id="contrato" role="tabpanel" aria-labelledby="contrato-tab">tesete desdf </div>
                        <div class="tab-pane fade show" id="fiscal" role="tabpanel" aria-labelledby="fiscal-tab">masteri</div>
                            <div class="container-tabs">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active button" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="true">Info</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="precos-tab button" data-bs-toggle="tab" data-bs-target="#precos" type="button" role="tab" aria-controls="precos" aria-selected="false">Preços</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tabela_p-tab button" data-bs-toggle="tab" data-bs-target="#tabela_p" type="button" role="tab" aria-controls="tabela_p" aria-selected="false">Tabela de Preços</button>
                                    </li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mt-2">
                                                    <x-label for="ProductCode" value="{{ __('Código') }}" />
                                                    <x-input id="ProductCode" class="block mt-1 w-full" type="text" name="ProductCode" placeholder="Código do Produto" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mt-2">
                                                    <x-label for="ProductNumberCode" value="{{ __('Código de Barras') }}" />
                                                    <x-input id="ProductNumberCode" class="block mt-1 w-full" type="text" name="ProductNumberCode" placeholder="Código de Barras" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mt-2">
                                                    <x-label for="ProductDescription" value="{{ __('Nome do Serviço / Produto') }}" />
                                                    <x-input id="ProductDescription" class="block mt-1 w-full" type="text" name="ProductDescription" placeholder="Nome do Produto/Serviço" required />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
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

                                            <div class="col-md-4">
                                                <div class="mt-2">
                                                    <x-label for="ProductGroup" value="{{ __('Categoria') }}" />
                                                    <select name="ProductGroup" id="ProductGroup" class="form-control">
                                                        <!-- Foreach -->
                                                        <!-- /.Foreach -->
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade show " id="precos" role="tabpanel" aria-labelledby="precos-tab">
                                        teste
                                    </div>
                                    <div class="tab-pane fade show " id="tabela_p" role="tabpanel" aria-labelledby="tabela_p-tab">
                                        outro
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="imagem" value="{{ __('Imagem') }}" />
                                <x-input id="imagem" class="block mt-1 w-full" type="file" name="imagem" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="factura" value="{{ __('Incluir na Fatura') }}" />
                                <select name="factura" id="factura">
                                    <option value="nao">Não</option>
                                    <option value="sim">Sim</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="unidade" value="{{ __('Unidade') }}" />
                                <select name="unidade" id="unidade">
                                    <option value="uni">Unidade</option>
                                    <option value="kg">Kilograma (Kg)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="taxa_iva" value="{{ __('Imposto') }}" />
                                <div class="input-group input-group-sm">
                                    <x-input type="text" id="taxa_iva" name="taxa_iva" placeholder="Taxa de Imposto" />
                                    <span class="input-group-append">
                                        <button type="button" class="btn btn-info btn-flat" id="btt_categoria" data-toggle="modal" data-target="#modal-primary"> 
                                            <i class="fa fa-search-plus"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mt-2">
                                <x-label for="motivo_isencao" value="{{ __('Motivo de Isenção') }}" />
                                <select name="motivo_isencao" id="motivo_isencao">
                                    @foreach($productExemptionReasons as $reason)
                                        <option value="{{ $reason->code }}">{{ $reason->name }}</option>
                                    @endforeach
                                </select>
                                <x-input type="text" name="dedutivel_iva" id="dedutivel_iva" value="100%" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="preco_custo" value="{{ __('Preço de Custo') }}" />
                                <x-input type="text" name="preco_custo" id="preco_custo" placeholder="Preço de Custo" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="preco_venda" value="{{ __('Preço de Venda') }}" />
                                <x-input type="text" name="preco_venda" id="preco_venda" placeholder="Preço de Venda" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="margem_lucro" value="{{ __('Margem de Lucro') }}" />
                                <x-input type="text" name="margem_lucro" id="margem_lucro" placeholder="Margem de Lucro" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mt-2">
                                <x-label for="preco_sem_iva" value="{{ __('Preço sem IVA') }}" />
                                <x-input type="text" name="preco_sem_iva" id="preco_sem_iva" placeholder="Preço sem IVA" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
