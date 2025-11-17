<x-app-layout>
    <!-- resources/views/processos/licenciamento_edit.blade.php -->
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')],
        ['name' => $licenciamento->codigo_licenciamento, 'url' => route('licenciamentos.show', $licenciamento->id)],
        ['name' => 'Editar Licenciamento', 'url' => route('licenciamentos.edit', $licenciamento->id)]
    ]" separator="/" />

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="fas fa-building"></i> Dados do Cliente</h5>
            </div>
            <div>
                <a href="{{route('customers.show', $licenciamento->cliente->id)}}" class="btn btn-light btn-sm">
                    <i class="fas fa-eye"></i> Ver Perfil
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap">
                <div class="me-4 mb-2"><strong>Cliente:</strong> {{ $licenciamento->cliente->CompanyName }}</div>
                <div class="me-4 mb-2"><strong>Email:</strong> {{ $licenciamento->cliente->Email }}</div>
                <div class="mb-2"><strong>Telefone:</strong> {{ $licenciamento->cliente->Telephone }}</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <ul class="nav nav-tabs card-header-tabs" id="licenciamentoTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                        <i class="fas fa-info-circle"></i> Informações
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs" type="button" role="tab">
                        <i class="fas fa-file-alt"></i> Documentos
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body tab-content" id="licenciamentoTabsContent">

            <!-- TAB: INFORMAÇÕES -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">

                <form id="licenciamento-form" action="{{ route('licenciamentos.update', $licenciamento->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="border-bottom pb-2 mb-3 text-secondary"><i class="fas fa-globe"></i> Dados Gerais</h6>
                    
                    <!-- Divisão -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Tipo de Declaração</label>
                                    <select class="form-select" name="tipo_declaracao" required>
                                        <option value="">Selecionar</option>
                                        <option value="11" {{ $licenciamento->tipo_declaracao == 11 ? 'selected' : '' }}>Importação Definitiva</option>
                                        <option value="21" {{ $licenciamento->tipo_declaracao == 12 ? 'selected' : '' }}>Exportação Definitiva</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Região Aduaneira</label>
                                    <select name="estancia_id" id="estancia_id" class="form-select">
                                        <option value="">Selecionar</option>
                                        @foreach($estancias as $estancia)
                                            <option value="{{ $estancia->id }}" {{ $estancia->id == $licenciamento->estancia_id ? 'selected' : '' }}>
                                                {{ $estancia->desc_estancia }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="my-4"></div>
                            
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Factura Proforma</label>
                                    <input type="text" name="factura_proforma" class="form-control" value="{{ old('factura_proforma', $licenciamento->factura_proforma) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Ref. do Cliente</label>
                                    <input type="text" name="referencia_cliente" class="form-control" value="{{ old('referencia_cliente', $licenciamento->referencia_cliente) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Descrição</label>
                                    <input type="text" name="descricao" class="form-control" value="{{ old('descricao', $licenciamento->descricao) }}">
                                </div>
                            </div>
                            <!-- /.  Dados Gerais  -->

                            <hr class="my-4">
                            <h6 class="border-bottom pb-2 mb-3 text-secondary"><i class="fas fa-truck"></i> Dados de Transporte</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Tipo de Transporte</label>
                                    <select name="tipo_transporte" class="form-select" required>
                                        <option value="">Selecionar</option>
                                        <option value="1" {{ $licenciamento->tipo_transporte == 1 ? 'selected' : '' }}>Marítimo</option>
                                        <option value="2" {{ $licenciamento->tipo_transporte == 2 ? 'selected' : '' }}>Ferroviário</option>
                                        <option value="3" {{ $licenciamento->tipo_transporte == 3 ? 'selected' : '' }}>Rodoviário</option>
                                        <option value="4" {{ $licenciamento->tipo_transporte == 4 ? 'selected' : '' }}>Aéreo</option>
                                        <option value="5" {{ $licenciamento->tipo_transporte == 5 ? 'selected' : '' }}>Correio</option>
                                        <option value="6" {{ $licenciamento->tipo_transporte == 6 ? 'selected' : '' }}>Multimodal</option>
                                        <option value="8" {{ $licenciamento->tipo_transporte == 8 ? 'selected' : '' }}>Fluvial</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Registo do Transporte</label>
                                    <input type="text" name="registo_transporte" class="form-control" value="{{ $licenciamento->registo_transporte }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Nacionalidade</label>
                                    <select name="nacionalidade_transporte" class="form-select">
                                        @foreach($paises as $pais)
                                            <option value="{{$pais->id}}" {{ $licenciamento->nacionalidade_transporte == $pais->id ? 'selected' : '' }}>
                                                {{$pais->pais}} ({{$pais->codigo}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Manifesto</label>
                                    <input type="text" name="manifesto" class="form-control" value="{{ $licenciamento->manifesto }}">
                                </div>
                            </div>
                            <!-- /.  Dados de Transporte  -->

                            <hr class="my-4">
                            <h6 class="border-bottom pb-2 mb-3 text-secondary"><i class="fas fa-map-marker-alt"></i> Dados de Origem e Destino da Mercadoria</h6>
                            <div class="row g-3">
                                <!-- (Aero)Porto de Origem -->
                                <div class="col-md-3">
                                    <label class="form-label">(Aero)Porto de Origem</label>
                                    <select name="porto_origem" class="form-select">
                                        @foreach($portos as $porto)
                                            <option value="{{ $porto->sigla }}" {{ $licenciamento->porto_origem == $porto->sigla ? 'selected' : '' }}>
                                                {{$porto->porto}} ({{$porto->sigla}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- (Aero)Porto de Entrada -->
                                <div class="col-md-3">
                                    <label class="form-label">(Aero)Porto de Entrada</label>
                                    <select name="porto_entrada" class="form-select">
                                        @foreach($portos as $porto)
                                            <option value="{{ $porto->sigla }}" {{ $licenciamento->porto_entrada == $porto->sigla ? 'selected' : '' }}>
                                                {{$porto->porto}} ({{$porto->sigla}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Data de Chegada -->
                                <div class="col-md-3">
                                    <label class="form-label">Data de Chegada</label>
                                    <input type="date" name="data_entrada" class="form-control" value="{{ old('data_entrada', $licenciamento->data_entrada ? $licenciamento->data_entrada->format('Y-m-d') : '') }}">
                                </div>

                                <!-- Código do Volume -->
                                <div class="col-md-3">
                                    <label class="form-label">Código do Volume</label>
                                    <select name="codigo_volume" id="codigo_volume" required class="form-select">
                                        <option value="">Selecionar</option>
                                        <option value="B" {{ $licenciamento->codigo_volume == 'B' ? 'selected' : '' }}>B - Carga Granel</option>
                                        <option value="F" {{ $licenciamento->codigo_volume == 'F' ? 'selected' : '' }}>F - Contentor Carregado</option>
                                        <option value="G" {{ $licenciamento->codigo_volume == 'G' ? 'selected' : '' }}>G - Carga Geral</option>
                                        <option value="L" {{ $licenciamento->codigo_volume == 'L' ? 'selected' : '' }}>L - Contentor Carregado não cheio</option>
                                        <option value="N" {{ $licenciamento->codigo_volume == 'N' ? 'selected' : '' }}>N - Numero por unidade</option>
                                    </select>
                                </div>

                                <!-- Quantidade de Volume -->
                                <div class="col-md-6">
                                    <label class="form-label">Quantidade de Volume</label>
                                    <input type="number" name="qntd_volume" class="form-control" value="{{ old('qntd_volume', $licenciamento->qntd_volume) }}">
                                </div>

                                <!-- Peso Bruto -->
                                <div class="col-md-6">
                                    <label class="form-label">Peso Bruto (Kg)</label>
                                    <input type="number" step="0.01" name="peso_bruto" class="form-control" value="{{ old('peso_bruto', $licenciamento->peso_bruto) }}">
                                </div>
                            </div>
                            <!-- /.  Dados de Origem e Destino da Mercadoria  -->
                        </div>

                        <div class="col-md-4">
                            <hr class="my-4 border-red-500 d-md-none">
                            <h6 class="border-bottom pb-2 mb-3 text-danger"><i class="fas fa-file-invoice-dollar"></i> Dados Financeiros</h6>
                            
                            <div class="row g-3">
                                <div class="">
                                    <label class="form-label">Banco</label>
                                    <select class="form-select" id="codigo_banco" name="codigo_banco" required>
                                        <option value="">Selecione um banco</option>
                                        @foreach($bancos as $banco)
                                            <option value="{{$banco['code']}}" {{ $banco['code'] == $licenciamento->codigo_banco ? 'selected' : '' }}>
                                                {{$banco['code']}} - {{$banco['fname']}} ({{$banco['sname']}})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="">
                                    <label class="form-label">Forma de Pagamento</label>
                                    <select id="forma_pagamento" name="forma_pagamento" class="form-select" required>
                                        <option value="" disabled selected>Selecione</option>
                                        <option value="Tr" {{ $licenciamento->forma_pagamento == 'Tr' ? 'selected' : ''}}> Transferência Bancária</option>
                                        <option value="CK" {{ $licenciamento->forma_pagamento == 'CK' ? 'selected' : ''}}> Caixa Única Tesouro Base Kwanda</option>
                                        <option value="RD" {{ $licenciamento->forma_pagamento == 'RD' ? 'selected' : ''}}>Pronto Pagamento</option>
                                        <option value="Ou" {{ $licenciamento->forma_pagamento == 'Ou' ? 'selected' : ''}}>Outro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Método de Avaliação</label>
                                    <select name="metodo_avaliacao" class="form-select" required>
                                        <option value="">Selecionar</option>
                                        <option value="GATT" {{ $licenciamento->metodo_avaliacao == 'GATT' ? 'selected' : '' }}>GATT</option>
                                        <option value="Outro" {{ $licenciamento->metodo_avaliacao == 'Outro' ? 'selected' : '' }}>Outro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="my-4"></div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Moeda</label>
                                    <select name="moeda" class="form-select" required>
                                        @foreach($paises->filter(fn($pais) => $pais->cambio > 0) as $pais)
                                            <option value="{{ $pais->moeda }}" {{ $licenciamento->moeda == $pais->moeda ? 'selected' : '' }} >
                                                {{ $pais->moeda }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="">
                                    <label class="form-label">Valor Declarado (FOB)</label>
                                    <input type="number" step="0.01" name="fob_total" class="form-control" value="{{ old('fob_total', $licenciamento->fob_total) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Frete</label>
                                    <input type="number" step="0.01" name="frete" class="form-control" value="{{ old('frete', $licenciamento->frete) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Seguro</label>
                                    <input type="number" step="0.01" name="seguro" class="form-control" value="{{ old('seguro', $licenciamento->seguro) }}">
                                </div>
                                <div class="">
                                    <label class="form-label">CIF Total</label>
                                    <input type="number" step="0.01" name="cif" class="form-control" value="{{ old('cif', $licenciamento->cif) }}">
                                </div>
                            </div>
                            <!-- /. Dados Financeiros -->
                        </div>
                    </div>
                    <!-- /. Divisão -->
                    <hr class="my-4">

                    <div class="text-end">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fas fa-save"></i> Atualizar Licenciamento
                        </button>
                        <a href="{{ route('licenciamentos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>

            <!-- TAB: DOCUMENTOS -->
            <div class="tab-pane fade" id="docs" role="tabpanel">
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-paperclip"></i> Documentos Anexados</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('arquivos.store')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="licenciamento_id" value="{{ $licenciamento->id }}">

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Documento</label>
                                    <select name="TipoDocumento" class="form-select">
                                        <option value="">Selecionar</option>
                                        <option value="Licenciamento">Licenciamento</option>
                                        <option value="Comprovativo Pag">Comprovativo de Pagamento</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Data de Emissão</label>
                                    <input type="date" name="DataEmissao" class="form-control">
                                </div>
                            </div>

                            <div id="drop-area" class="p-5 border border-dashed rounded text-center bg-light">
                                <h6 class="fw-bold mb-2"><i class="fas fa-cloud-upload-alt"></i> Arraste e solte o documento aqui</h6>
                                <p class="text-muted mb-3">ou clique abaixo para selecionar</p>
                                <label for="file-input" class="btn btn-primary">
                                    <i class="fas fa-file-upload"></i> Escolher Arquivo
                                </label>
                                <input type="file" id="file-input" name="Caminho" multiple hidden>
                            </div>

                            <div class="mt-4">
                                @if($licenciamento->documentos)
                                    <h6 class="text-muted"><i class="fas fa-folder-open"></i> Arquivos já anexados:</h6>
                                    <ul class="list-group list-group-flush">
                                        @foreach($licenciamento->documentos as $documento)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                {{ $documento->TipoDocumento }}
                                                <a href="{{ route('arquivos.download', ['NrDocumento' => $documento->NrDocumento]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download"></i> Baixar
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

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