<!-- resources/view/mercadorias/create_mercadoria.blade.php -->
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
            ['name' => 'Visualizar Licenciamento', 'url' => route('licenciamentos.show', request()->get('licenciamento_id') )],
            ['name' => 'Nova Mercadorias', 'url' => '']
        ]" separator="/" />
        @php
            $mercadoriasAgrupadas = App\Models\MercadoriaAgrupada::with('mercadorias')->where('licenciamento_id',request()->get('licenciamento_id'))->get();
            $licenciamento = App\Models\Licenciamento::find(request()->get('licenciamento_id'));
            $fob = $licenciamento->fob_total;
            $seguro = $licenciamento->seguro;
            $frete = $licenciamento->frete;
            $CIF = $fob + $seguro + $frete;
        @endphp
    @endif

    @if(request()->has('processo_id'))
        <x-breadcrumb :items="[
            ['name' => 'Processos', 'url' => route('processos.index')],
            ['name' => 'Editar Processo', 'url' => route('processos.show', request()->get('processo_id') )],
            ['name' => 'Nova Mercadorias', 'url' => '']
        ]" separator="/" />
        @php
            $mercadoriasAgrupadas = App\Models\MercadoriaAgrupada::with('mercadorias')->where('Fk_Importacao',request()->get('processo_id'))->get();
            $processo = App\Models\Processo::find(request()->get('processo_id'));
            $fob = $processo->importacao->FOB;
            $seguro = $processo->importacao->Insurance;
            $frete = $processo->importacao->Freight;
            $CIF = $fob + $seguro + $frete;
        @endphp
    @endif

    <form action="{{ route('mercadorias.store') }}" method="POST">
        @csrf
        <!-- Enviar os IDs escondidos no formulário -->
        @if(request()->has('licenciamento_id'))
            <input type="hidden" name="licenciamento_id" value="{{ request()->get('licenciamento_id') }}">
        @endif

        @if(request()->has('processo_id'))
            <input type="hidden" name="Fk_Importacao" value="{{ request()->get('processo_id') }}">
        @endif

        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header justify-between h-16">
                        <div class="card-title flex"> <span>Mercadorias</span> </div>
                        <div class="flex float-right">
                            <!-- Botão de submissão -->
                            <button type="submit" class="btn btn-primary">Salvar Mercadoria</button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="">Tipo de Mercadoria</label>
                                <select class="form-control" id="subcategoria_id" name="subcategoria_id" required>
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($sub_categorias as $subcategoria)
                                        <option value="{{$subcategoria->id}}" data-code="{{$subcategoria->cod_pauta}}">
                                            {{$subcategoria->cod_pauta}} - {{$subcategoria->descricao}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="codigo_aduaneiro">Código Aduaneiro</label> <span class="erro_pauta"></span>
                                <input type="text" id="codigo_aduaneiro" name="codigo_aduaneiro" class="form-control" required list="pauta_list">
                                <datalist id="pauta_list">

                                </datalist>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="NCM_HS">Marca</label>
                                <input type="text" name="NCM_HS" id="NCM_HS" placeholder="Marcas do Contentor" class="form-control">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="NCM_HS_Numero">Números</label>
                                <input type="text" name="NCM_HS_Numero" id="NCM_HS_Numero" placeholder="Números do Contentor" class="form-control">
                            </div>
                        </div>

                        
                        <!-- Detalhes da Mercadoria -->
                        <div class="form-group">
                            <label for="descricao">Descrição da Mercadoria:</label>
                            <input type="text" name="Descricao" class="form-control" required>
                        </div>

                        <!-- Seção de Detalhes Específicos -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="Quantidade" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="Quantidade" name="Quantidade" min="1" placeholder="Ex.: 10" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="Unidade" class="form-label">Unidade de Medida</label>
                                <select class="form-select" id="Unidade" name="Unidade" required>
                                    <option value="">Selecione a unidade</option>
                                    <option value="kg">Kg</option>
                                    <option value="litros">Litros</option>
                                    <option value="unidades">Unidades</option>
                                    <option value="metros">Metros</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label for="unidade">Qualificação</label>
                                    <select name="Qualificacao" id="Qualificacao" class="form-control">
                                        <option value="">Selecionar</option>
                                        <option value="cont">Contentor</option>
                                        <option value="auto">Automóvel</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Detalhes Adicionais: Peso, Volume, etc. -->
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="Peso" class="form-label">Peso (Kg)</label>
                                <input type="number" class="form-control" id="Peso" name="Peso" step="0.01" placeholder="Ex.: 500.50">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="volume" class="form-label">Volume (m³)</label>
                                <input type="number" class="form-control" id="volume" name="volume" step="0.01" placeholder="Ex.: 20.5">
                            </div>
                       
                            <!-- Valor Unitário e Total -->
                        
                            <div class="col-md-3 mb-3">
                                <label for="preco_unitario" class="form-label">Valor Unitário (Moeda)</label>
                                <input type="number" class="form-control" id="preco_unitario" name="preco_unitario" step="0.01" placeholder="Ex.: 1000.00" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="preco_total" class="form-label">Valor Total (FOB)</label>
                                <input type="number" class="form-control" id="preco_total" name="preco_total" step="0.01" placeholder="Ex.: 10000.00" required>
                            </div>
                        </div>

                        <!-- Informações Adicionais por Categoria -->
                        <div class="info_veiculos" id="info_veiculos" style="display: none;">
                            <div class = "row">
                                <div class="col-md-4 mb-3">
                                    <label for="marca" class="form-label">Marca do Veículo</label>
                                    <input type="text" class="form-control" id="marca" name="marca" placeholder="Ex.: Toyota">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="modelo" class="form-label">Modelo do Veículo</label>
                                    <input type="text" class="form-control" id="modelo" name="modelo" placeholder="Ex.: Corolla">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="chassis" class="form-label">Nº do Chassis</label>
                                    <input type="text" class="form-control" id="chassis" name="chassis" placeholder="Ex.: MA3JFB...">
                                </div>
                            </div>
                            <div class = "row">
                                <div class="col-md-4 mb-3">
                                    <label for="ano_fabricacao" class="form-label">Ano de Fabricação</label>
                                    <input type="number" class="form-control" id="ano_fabricacao" name="ano_fabricacao" placeholder="Ex.: 2020">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="modelo" class="form-label">H.S Code</label>
                                <input type="text" class="form-control" id="modelo" name="modelo" placeholder="Ex.: Corolla">
                            </div>
                        </div>

                        <div class="info_maquina" id="info_maquina" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="marca" class="form-label">Marca da Máquina</label>
                                    <input type="text" class="form-control" id="marca" name="marca" placeholder="Ex.: Dell">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="potencia" class="form-label">Potência (kW)</label>
                                    <input type="number" class="form-control" id="potencia" name="potencia" step="0.01" placeholder="Ex.: 500.00">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <label for="frete">Frete</label>
                                <input type="text" id="frete" name="frete" class="form-control" value="{{$frete}}">
                                @error('frete')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="seguro">Seguro</label>
                                <input type="text" id="seguro" name="seguro" class="form-control" value="{{$seguro}}">
                                @error('seguro')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="cif">CIF</label>
                                <input type="text" id="cif" name="cif" class="form-control" value="{{$CIF}}">
                                @error('cif')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header"></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="fob_total">FOB Total</label>
                                <input type="text" id="fob_total" name="fob_total" class="form-control" readonly value="{{$fob}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        <!-- Seleção de Categoria -->
    </form>
    <hr>
    <div class="card card-navy">
        <div class="card-header justify-between h-16">
            <div class="card-title flex"> <span>Adições Agrupadas</span> </div>
            <div class="flex float-right">
                <a href="#" id="pauta_mercadoria" class="event button" data-toggle="modal" data-target="#PautaModal">
                    <span class="icon-edit icon"></span>Pauta Aduaneira
                </a>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-flex table-flex--autocomplete">
                <thead>
                    <tr>
                        <th>Codigo Aduaneiro</th>
                        <th>Quantidade Total</th>
                        <th>Peso (Kg)</th>
                        <th>Preço (Moeda)</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mercadoriasAgrupadas as $agrupamento)
                    <tr data-widget="expandable-table" aria-expanded="false">
                        <td>{{ $agrupamento->codigo_aduaneiro }}</td>
                        <td>{{ $agrupamento->quantidade_total }}</td>
                        <td>{{ $agrupamento->peso_total }}</td>
                        <td>{{ $agrupamento->preco_total }}</td>
                    </tr>

                    <!-- Linhas Detalhadas (Mercadorias Associadas) -->
                    <tr class="expandable-body">
                        <td colspan="5">
                            <table class="table table-sm mb-0">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Quantidade</th>
                                        <th>Peso</th>
                                        <th>Preço Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($agrupamento->mercadorias as $mercadoria)
                                    <tr>
                                        <td>{{ $mercadoria->Descricao }}</td>
                                        <td>{{ $mercadoria->Quantidade }}</td>
                                        <td>{{ $mercadoria->Peso }}</td>
                                        <td>{{ $mercadoria->preco_total }}</td>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#subcategoria_id').on('change', function() {
                // Captura o código da subcategoria selecionada
                var cod_pauta = $(this).find(':selected').data('code');

                // Limpa a lista existente
                $('#pauta_list').empty();

                if (cod_pauta) {
                    // Faz a requisição AJAX para buscar os códigos aduaneiros com base no código da subcategoria
                    $.ajax({
                        url: "{{ route('pauta.get', ['cod_pauta' => ':cod_pauta']) }}".replace(':cod_pauta', cod_pauta),
                        method: 'GET',
                        success: function(data) {
                            // Limpa o datalist existente
                            $('#pauta_list').empty();

                            // Preenche o datalist com os códigos retornados
                            $.each(data, function(index, pauta) {
                                // Remove os pontos do pauta.codigo
                                var codigoFormatado = pauta.codigo.replace(/\./g, '');
                                $('#pauta_list').append('<option value="' + codigoFormatado + '">'+ codigoFormatado +' - '+ pauta.descricao + '</option>');
                            });
                        },
                        error: function() {
                            alert('Erro ao carregar os códigos aduaneiros.');
                        }
                    });
                }
            });
        });

        document.getElementById('Quantidade').addEventListener('input', calcularValorTotal);
        document.getElementById('preco_unitario').addEventListener('input', calcularValorTotal);

        function calcularValorTotal() {
            var quantidade = parseFloat(document.getElementById('Quantidade').value) || 0;
            var precoUnitario = parseFloat(document.getElementById('preco_unitario').value) || 0;
            var valorTotal = quantidade * precoUnitario;
            document.getElementById('preco_total').value = valorTotal.toFixed(2);
        }

    </script>

</x-app-layout>