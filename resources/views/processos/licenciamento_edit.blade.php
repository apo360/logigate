<x-app-layout>
    <!-- resources/views/processos/licenciamento_edit.blade.php -->
    <head>
        <style>
                .body-doc {
                    font-family: Arial, sans-serif;
                    background-color: #f0f0f0;
                    margin: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }

                .upload-container {
                    text-align: center;
                    background-color: #ffffff;
                    border: 2px dashed #ccc;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                }

                .progress-bar {
                    width: 0;
                    height: 10px;
                    background-color: #3498db;
                    border-radius: 5px;
                    transition: width 0.3s ease-in-out;
                }

                #drop-area.active {
                    background-color: #e0e0e0;
                }

                #file-list ul {
                    list-style: none;
                    padding: 0;
                }

                #file-list ul li {
                    margin: 5px 0;
                    padding: 5px;
                    background-color: #f5f5f5;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    cursor: move;
                }

                .button-arquivo {
                    background-color: #3498db;
                    color: #fff;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                }

                input[type="file"] {
                    display: none;
                }
        </style>
    </head>
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
        <!-- Início das Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="licenciamento-tab" data-bs-toggle="tab" href="#licenciamento" role="tab" aria-controls="licenciamento" aria-selected="false"> <i class="fas fa-info"></i> Info</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="documentos-tab" data-bs-toggle="tab" href="#documentos" role="tab" aria-controls="documentos" aria-selected="false"> <i class="fas fa-file"></i> Documentos</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <!-- Aba Licenciamento -->
            <div class="tab-pane fade show active" id="licenciamento" role="tabpanel" aria-labelledby="licenciamento-tab">
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
                                        <div class="text-danger d-flex align-items-center">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span>{{ $message }}</span>
                                        </div>
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
                                <input type="text" id="factura_proforma" name="factura_proforma" value="{{ old('factura_proforma', $licenciamento->factura_proforma) }}" class="form-control">
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
                                    <x-input type="text" name="porto_origem" class="form-control rounded-md shadow-sm" list="porto" value="{{ $licenciamento->porto_origem }}" required />
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

                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label for="seguro">FOB</label>
                                <input type="text" id="fob_total" name="fob_total" class="form-control" value="{{ $licenciamento->fob_total }}">
                                @error('fob_total')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="seguro">CIF</label>
                                <input type="text" id="cif" name="cif" class="form-control" value="{{ $licenciamento->cif }}">
                                @error('cif')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        .

                    </div>
                    <div class="card-footer">
                        <!-- Botões -->
                        <div class="row">
                            <div class="form-group text-right mt-4">
                                <button type="submit" class="btn btn-primary">Atualizar Licenciamento</button>
                                <a href="{{ route('licenciamentos.index') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Aba de Documentos -->
            <div class="tab-pane fade" id="documentos" role="tabpanel" aria-labelledby="documentos-tab">
                <div class="card mt-3">
                    <h5 class="card-header">Documentos Anexados</h5>
                    <div class="card-body">
                        <form action="{{ route('arquivos.store')}}" method="post" enctype="multipart/form-data"></form>
                            <!-- Início do bloco para carregar arquivos -->
                             <div class="row">
                                <input type="hidden" name="licenciamento_id" value="$licenciamento->id">
                                <div class="form-group col-md-6">
                                    <label for="TipoDocumento" class="col-md-4 col-form-label text-md-right">Tipo de Documento</label>
                                    <select name="TipoDocumento" id="TipoDocumento" class="form-control">
                                        <option value="">Selecionar</option>
                                        <option value="Licenciamento">Licenciamento</option>
                                        <option value="Comprovativo Pag">Comprovativo de Pagamento</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="documento_licenciamento" class="col-md-4 col-form-label text-md-right">Data de Emissão</label>
                                    <input type="date" name="DataEmissao" id="DataEmissao" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12 mt-4">
                                <div id="drop-area" style="width: 100%; height: 200px; border: 2px dashed #ccc; text-align: center; padding: 20px;">
                                    <h2>Arraste e solte documento aqui!</h2>
                                    <p>ou</p>
                                    <label for="file-input" style="cursor: pointer;" class="button-arquivo">Selecione um arquivo</label>
                                </div>
                                <input type="file" id="file-input" name="Caminho" multiple style="display: none;">
                                
                                <div id="file-list" class="mt-4">
                                    <!-- Se o licenciamento já tiver documentos carregados -->
                                    @if($licenciamento->documentos)
                                        <div class="form-group row">
                                            <label class="col-md-4 col-form-label text-md-right">Arquivos Anexados</label>
                                            <div class="col-md-6">
                                                <ul>
                                                    @foreach($licenciamento->documentos as $documento)
                                                        <li>
                                                            <a href="{{ route('arquivos.download', ['NrDocumento' => $documento->NrDocumento]) }}" target="_blank">{{ $documento->TipoDocumento }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <br>
                            </div>
                        <!-- Fim do bloco de upload -->
                    </div>
                </div>
            </div>
        </div>
       
        <!-- Fim das Tabs -->
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

    <!-- Scrip para inserção de documentos -->
    <script>
        const dropArea = document.getElementById('drop-area');
        const fileList = document.querySelector('#file-list ul');

        // Prevenir comportamento padrão de arrastar e soltar
        dropArea.addEventListener('dragenter', (e) => {
            e.preventDefault();
            dropArea.style.border = '2px dashed #aaa';
        });

        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.style.border = '2px dashed #ccc';
        });

        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.style.border = '2px dashed #ccc';

            const files = e.dataTransfer.files;
            updateFileList(files);
        });

        // Validar o tipo de arquivo
        function validateFileType(file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.ms-excel'];

            return allowedTypes.includes(file.type);
        }

        // Atualizar a lista de arquivos selecionados
        function updateFileList(files) {
            fileList.innerHTML = '';
            for (const file of files) {
                if (validateFileType(file) && validateFileSize(file)) {
                    const li = createFileListItem(file);
                    fileList.appendChild(li);
                }
            }
        }

        // Validar o tamanho do arquivo
        function validateFileSize(file) {
            const maxSizeMB = 5;
            const maxSizeBytes = maxSizeMB * 1024 * 1024;
            if (file.size <= maxSizeBytes) {
                return true;
            } else {
                alert('Tamanho do arquivo excede o limite de ' + maxSizeMB + 'MB.');
                return false;
            }
        }

        // Criar item da lista de arquivos
        function createFileListItem(file) {
            const li = document.createElement('li');
            li.textContent = file.name;

            const removeButton = document.createElement('button');
            removeButton.textContent = 'Remover';
            removeButton.addEventListener('click', () => {
                li.remove();
            });

            li.appendChild(removeButton);

            return li;
        }

        // Lidar com o evento de arrastar sobre a área de soltar
        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.classList.add('active');
        });

        // Lidar com o evento de sair da área de soltar
        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('active');
        });

        // Lidar com o evento de soltar na área de soltar
        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.classList.remove('active');

            const files = e.dataTransfer.files;
            updateFileList(files);
        });


        // Lidar com a seleção de arquivo usando o input de arquivo
        const fileInput = document.getElementById('file-input');
        fileInput.addEventListener('change', (e) => {
            const files = e.target.files;
            updateFileList(files);
        });

        // Permitir reordenar arquivos usando arrastar e soltar
        new Sortable(fileList, {
            animation: 150,
            ghostClass: 'sortable-ghost'
        });
    </script>

</x-app-layout>