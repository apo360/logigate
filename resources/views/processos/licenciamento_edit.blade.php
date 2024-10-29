<x-app-layout>
    <!-- resources/views/processos/licenciamento_edit.blade.php -->

    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')],
        ['name' => $licenciamento->codigo_licenciamento, 'url' => route('licenciamentos.show', $licenciamento->id)],
        ['name' => 'Editar Licenciamento', 'url' => route('licenciamentos.edit', $licenciamento->id)]
    ]" separator="/" />

    <div class="card">
        <div class="card-header"></div>
        <span style="padding: 10px 0px 0px 10px;">Cliente :  <a href="{{route('customers.show', $licenciamento->cliente->id)}}">{{$licenciamento->cliente->CompanyName}}</a>  </span>
        <span style="padding-left: 10px;">Email : {{$licenciamento->cliente->Email}} </span>
        <span style="padding-left: 10px;">Telefone : {{$licenciamento->cliente->Telephone}} </span>
    </div>

    <div class="card">
        <form id="licenciamento-form" action="{{ route('licenciamentos.update', $licenciamento->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="form-group mt-2 col-md-4">
                        <label for="tipo_declaracao">Tipo de Declaração (Região)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-check"></i></span>
                            </div>
                            <select id="tipo_declaracao" name="tipo_declaracao" required class="form-control">
                                <option value="">Selecionar</option>
                                <option value="11" {{ $licenciamento->tipo_declaracao == 11 ? 'selected' : '' }} >Importação Definitiva</option>
                                <option value="21" {{ $licenciamento->tipo_declaracao == 12 ? 'selected' : '' }}>Exportação Definitiva</option>
                            </select>
                            @error('tipo_declaracao')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group mt-2 col-md-4">
                        <label for="ContaDespacho">Região Aduaneira (Estância)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-check"></i></span>
                            </div>
                            <select name="estancia_id" id="estancia_id" class="form-control" value="{{ old('estancia_id') }}">
                                <option value="">Selecionar</option>
                                @foreach($estancias as $estancia)
                                    <option value="{{ $estancia->id }}" {{ $estancia->id == $licenciamento->estancia_id ? 'selected' : '' }} >{{ $estancia->desc_estancia }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group mt-2 col-md-4">
                        <!-- Campo Código do Banco (Select2) -->
                        <div class="form-group">
                            <label for="codigo_banco">Banco</label>
                            <select class="form-control" id="codigo_banco" name="codigo_banco" required>
                                <option value="">Selecione um banco</option>
                                @foreach($bancos as $banco)
                                    <option value="{{$banco['code']}}" {{ $banco['code'] == $licenciamento->codigo_banco ? 'selected' : '' }}>
                                        {{$banco['code']}} - {{$banco['fname']}} ({{$banco['sname']}})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group mt-2 col-md-3">
                        <label for="factura_proforma">Factura Proforma do cliente</label>
                        <input type="text" id="factura_proforma" name="factura_proforma" required class="form-control" value="{{ $licenciamento->factura_proforma }}">
                        @error('factura_proforma')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-2 col-md-3">
                        <label for="referencia_cliente">Referência do Cliente</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-check"></i></span>
                            </div>
                            <x-input type="text" name="referencia_cliente" value="{{ $licenciamento->referencia_cliente }}" required class="form-control" />
                            @error('referencia_cliente')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mt-2 col-md-6">
                        <label for="descricao">Descrição:</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-edit"></i></span>
                            </div>
                            <input type="text" name="descricao" value="{{ $licenciamento->descricao }}" class="form-control rounded-md shadow-sm" required>
                            @error('descricao')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group mt-2 col-md-3">
                        <label for="tipo_transporte">Tipo de Transporte</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-country"></i></span>
                            </div>
                            <select name="tipo_transporte" class="form-control rounded-md shadow-sm" id="tipo_transporte" value="{{ old('tipo_transporte') }}" required>
                                <option value="">Selecionar</option>
                                <option value="1" {{ $licenciamento->tipo_transporte == 1 ? 'selected' : '' }}>Maritimo</option>
                                <option value="2" {{ $licenciamento->tipo_transporte == 2 ? 'selected' : '' }}>Ferroviário</option>
                                <option value="3" {{ $licenciamento->tipo_transporte == 3 ? 'selected' : '' }}>Rodoviário</option>
                                <option value="4" {{ $licenciamento->tipo_transporte == 4 ? 'selected' : '' }}>Aéreo</option>
                                <option value="5" {{ $licenciamento->tipo_transporte == 5 ? 'selected' : '' }}>Correio</option>
                                <option value="6" {{ $licenciamento->tipo_transporte == 6 ? 'selected' : '' }}>Multimodal</option>
                                <option value="7" {{ $licenciamento->tipo_transporte == 7 ? 'selected' : '' }}>Instalação Transporte Fixo (Pipe, P)</option>
                                <option value="8" {{ $licenciamento->tipo_transporte == 8 ? 'selected' : '' }}>Fluvial</option>
                            </select>
                            @error('tipo_transporte')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mt-2 col-md-3">
                        <label for="registo_transporte">Registo do Transporte:</label>
                        <input type="text" id="registo_transporte" name="registo_transporte" class="form-control" value="{{ $licenciamento->registo_transporte }}">
                    </div>

                    <div class="form-group mt-2 col-md-3">
                        <label for="nacionalidade_transporte">Nacionalidade</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-plane"></i></span>
                            </div>
                            <select name="nacionalidade_transporte" class="form-control" id="nacionalidade_transporte" >
                                @foreach($paises as $pais)
                                    <option value="{{$pais->id}}" {{ $licenciamento->nacionalidade_transporte == $pais->id ? 'selected' : '' }}>{{$pais->pais}} ({{$pais->codigo}})</option>
                                @endforeach
                            </select>
                            @error('nacionalidade_transporte')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mt-2 col-md-3">
                        <label for="manifesto">Manifesto</label>
                        <input type="text" id="manifesto" name="manifesto" class="form-control" value="{{ $licenciamento->manifesto }}">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group mt-4 col-md-3">
                        <label for="moeda">Moeda</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-check"></i></span>
                            </div>
                            <select id="moeda" name="moeda" required class="form-control">
                                <option value="">Selecionar</option>
                                @foreach($paises->filter(fn($pais) => $pais->cambio > 0) as $pais)
                                    <option value="{{ $pais->moeda }}" {{ $licenciamento->moeda == $pais->moeda ? 'selected' : '' }} >{{ $pais->moeda }}</option>
                                @endforeach
                            </select>
                            @error('moeda')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group mt-4 col-md-3">
                        <label for="data_entrada">Data de Chegada</label>
                        <input type="date" id="data_entrada" name="data_entrada" class="form-control" value="{{ $licenciamento->data_entrada }}">
                    </div>

                    <div class="form-group mt-4 col-md-3">
                        <label for="porto_entrada">(Aero)Porto de Entrada</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-plane"></i></span>
                            </div>
                            <x-input type="text" name="porto_entrada" class="form-control rounded-md shadow-sm" value="{{ $licenciamento->porto_entrada }}" list="porto" required />
                            <datalist id="porto">
                                @foreach($portos as $porto)
                                    <option value="{{$porto->porto}}" {{ $licenciamento->porto_entrada == $porto->porto ? 'selected' : '' }}> {{$porto->porto}} ({{$porto->sigla}})</option>
                                @endforeach
                            </datalist>
                            @error('porto_entrada')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label for="metodo_avaliacao">Método de Avaliação:</label>
                        <select id="metodo_avaliacao" name="metodo_avaliacao" required class="form-control">
                            <option value="GATT">GATT</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="forma_pagamento">Forma de Pagamento:</label>
                        <select id="forma_pagamento" name="forma_pagamento" required class="form-control" >
                            <option value="RD">RD</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                </div>

                <div class = "row">
                    <div class="form-group col-md-3">
                        <label for="porto_origem">Porto de Origem</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-plane"></i></span>
                            </div>
                            <x-input type="text" name="porto_origem" class="form-control rounded-md shadow-sm" list="porto" required />
                            <datalist id="porto">
                                @foreach($portos as $porto)
                                    <option value="{{$porto->porto}}" {{ $licenciamento->porto_origem == $porto->porto ? 'selected' : '' }}> {{$porto->porto}} - ({{$porto->sigla}})</option>
                                @endforeach
                            </datalist>
                            @error('porto_origem')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="codigo_volume">Código do Volume:</label>
                        <select id="codigo_volume" name="codigo_volume" required class="form-control">
                            <option value="B" {{ $licenciamento->codigo_volume == 'B' ? 'selected' : '' }}>B - Carga Granel</option>
                            <option value="F" {{ $licenciamento->codigo_volume == 'F' ? 'selected' : '' }}>F - Contentor Carregado</option>
                            <option value="G" {{ $licenciamento->codigo_volume == 'G' ? 'selected' : '' }}>G - Carga Geral</option>
                            <option value="L" {{ $licenciamento->codigo_volume == 'L' ? 'selected' : '' }}>L - Contentor Carregado não cheio</option>
                            <option value="N" {{ $licenciamento->codigo_volume == 'N' ? 'selected' : '' }}>N - Numero por unidade</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-md-3">
                        <label for="qntd_volume">Quantidade de Volumes</label>
                        <input type="number" id="qntd_volume" name="qntd_volume" required class="form-control" value="{{ $licenciamento->qntd_volume }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label for="frete">Frete</label>
                        <input type="text" id="frete" name="frete" class="form-control" value="{{ $licenciamento->frete }}">
                        @error('frete')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="seguro">Seguro</label>
                        <input type="text" id="seguro" name="seguro" class="form-control" value="{{ $licenciamento->seguro }}">
                        @error('seguro')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="seguro">Peso Bruto</label>
                        <input type="text" id="peso_bruto" name="peso_bruto" class="form-control" value="{{ $licenciamento->peso_bruto }}">
                        @error('peso_bruto')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <!-- Botões -->
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="{{ route('licenciamentos.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('#codigo_banco').select2({
                placeholder: 'Selecione um banco',
                allowClear: true
            });

            // Rascunho automático a cada 60 segundos
            setInterval(function() {
                var formData = $('#licenciamento-form').serialize();

                $.ajax({
                    url: "{{ route('licenciamento.rascunho.store') }}",
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        console.log('Rascunho salvo automaticamente.');
                    },
                    error: function(xhr, status, error) {
                        console.log('Erro ao salvar rascunho automaticamente.');
                    }
                });
            }, 60000); // Salva automaticamente a cada 60 segundos
        });
    </script>

</x-app-layout>