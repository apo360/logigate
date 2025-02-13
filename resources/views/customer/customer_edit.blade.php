<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => $customer->CompanyName, 'url' => route('customers.show', $customer->id)],
        ['name' => 'Editar Cliente', 'url' => route('customers.edit', $customer->id)]
    ]" separator="/" />

    <div class="card" style="border-color: black solid 1px;">
        <div class="card-header d-flex justify-content-between">
            <div class="btn-group float-right">
                <a class="btn btn-outline-secondary" href="{{ route('customers.index') }}">
                    <i class="fas fa-search"></i> {{ __('Pesquisar') }}
                </a>
                <a class="btn btn-outline-primary" href=" {{ route('customers.create') }} " class="btn btn-outline-secondary">
                    <i class="fas fa-plus-o"></i> {{ __('Novo Cliente') }}
                </a>
                <div class="btn-group" role="group">
                    <button id="btnGroupDrop1" type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-filter"></i> {{ __('Opções') }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                        <li>
                            <a href=" {{ route('customers.ficha_imprimir', ['id' => $customer->id]) }}" class="dropdown-item">
                                <i class="fas fa-file-pdf"></i> {{ __('Imprimir') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customers.show', $customer->id) }}" class="button dropdown-item">
                                <i class="fas fa-eye"></i> {{ __('Visualizar') }}
                            </a>
                        </li>
                        <li>
                            <a href=" {{ route('customers.create') }} " class="button dropdown-item">
                                <i class="fas fa-file"></i> {{ __('Abrir Processo') }}
                            </a>
                        </li>
                        <li>
                            <a href=" {{ route('customers.create') }} " class="button dropdown-item">
                                <i class="fas fa-plus-o"></i> {{ __('Iniciar Licenciamento') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                
                        <div class="">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active button" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="true"> <i class="fas fa-info"></i> Info</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="contabilidade-tab" data-bs-toggle="tab" data-bs-target="#contabilidade" type="button" role="tab" aria-controls="contabilidade" aria-selected="false">Contabilidade</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="documento-tab" data-bs-toggle="tab" data-bs-target="#documento" type="button" role="tab" aria-controls="documento" aria-selected="false">Documentos</button>
                                </li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                                    <form method="POST" action="{{ route('customers.update', $customer->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="mt-4">
                                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active button" id="endereco-tab" data-bs-toggle="tab" data-bs-target="#endereco" type="button" role="tab" aria-controls="endereco" aria-selected="true">Endereço</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="aduaneiro-tab" data-bs-toggle="tab" data-bs-target="#aduaneiro" type="button" role="tab" aria-controls="aduaneiro" aria-selected="true">Info Aduaneira</button>
                                                </li>
                                            </ul>
                                            <div class="tab-content" id="myTabContent">
                                                <div class="tab-pane fade show active" id="endereco" role="tabpanel" aria-labelledby="endereco-tab">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="mt-2">
                                                                <x-label for="CustomerTaxID" value="{{ __('NIF Cliente') }}" />
                                                                <x-input id="CustomerTaxID" class="block mt-1 w-full" type="text" name="CustomerTaxID" value="{{ old('CustomerTaxID', $customer->CustomerTaxID) }}" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mt-2">
                                                                <x-label for="CompanyName" value="{{ __('Cliente') }}" />
                                                                <x-input id="CompanyName" class="block mt-1 w-full" type="text" name="CompanyName" value="{{ old('CompanyName', $customer->CompanyName) }}" readonly />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mt-2">
                                                            <x-label for="Email" value="{{ __('Email') }}" />
                                                            <x-input id="Email" class="block mt-1 w-full" type="email" name="Email" autocomplete="email" value="{{ old('Email', $customer->Email ?? '') }}" />
                                                        </div>
                                                    </div> 

                                                    <div class="row">
                                                        <div class="col-md-4 mt-3">
                                                            <x-label for="Telephone" value="{{ __('Telefone') }}" />
                                                            <x-input id="Telephone" class="block mt-1 w-full" type="text" name="Telephone" required autocomplete="Telephone" value="{{ old('Telephone', $customer->Telephone ?? '') }}" />
                                                        </div>

                                                        <div class="col-md-4 mt-3">
                                                            <x-label for="PostalCode" value="{{ __('Código Postal') }}" />
                                                            <x-input id="PostalCode" class="form-control" type="text" name="PostalCode" value="{{ old('PostalCode', $customer->endereco->PostalCode ?? '00000') }}" />
                                                        </div>
                                                        <div class="col-md-4 mt-3">
                                                            <x-label for="SelfBillingIndicator" value="{{ __('Indicador de Autofaturação') }}" />
                                                            <select id="SelfBillingIndicator" class="form-control" name="SelfBillingIndicator">
                                                                <option value="0" {{ 0 == $customer->SelfBillingIndicator ? 'selected' : '' }}>Não</option>
                                                                <option value="1" {{ 1 == $customer->SelfBillingIndicator ? 'selected' : '' }}>Sim</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="col-md-3 mt-3">
                                                            <x-label for="country" value="{{ __('País') }}" />
                                                            <select name="nacionality" id="nacionality" class="form-control">
                                                                @foreach($paises as $pais)
                                                                    <option value="{{ $pais->id }}" {{ $pais->id == $customer->nacionality ? 'selected' : '' }}> {{ $pais->pais}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mt-3">
                                                            <label for="doc_type">Tipo de Documento</label>
                                                            <select name="doc_type" id="doc_type" class="form-control">
                                                                <option value="">Selecionar</option>
                                                                <option value="BI" {{ $customer->doc_type == 'BI' ? 'selected' : '' }}>Bilhete de Identidade</option>
                                                                <option value="PASS" {{ $customer->doc_type == 'PASS' ? 'selected' : '' }}>Passaporte</option>
                                                                <option value="CC" {{ $customer->doc_type == 'CC' ? 'selected' : '' }}>Carta de Condução</option>
                                                                <option value="CR" {{ $customer->doc_type == 'CR' ? 'selected' : '' }}>Cartão de Residência</option>
                                                                <option value="">Outro</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mt-3">
                                                            <label for="doc_num">Nº do Documento</label>
                                                            <x-input type="text" name="doc_num" id="doc_num" class="form-control" value="{{ old('doc_num', $customer->doc_num ?? '') }}" />
                                                        </div>
                                                        <div class="col-md-3 mt-3">
                                                            <label for="validade_date_doc">Data de Validade</label>
                                                            <x-input type="date" name="validade_date_doc" id="validade_date_doc" class="form-control" value="{{ old('validade_date_doc', $customer->validade_date_doc ?? '') }}" />
                                                        </div>
                                                    </div>

                                                    <!-- Campos de endereço -->
                                                    <hr class="mt-2">
                                                    <div class="form-group">
                                                        <x-label for="AddressDetail" value="{{ __('Morada Completa') }}" />
                                                        <x-input id="AddressDetail" class="form-input" type="text" name="AddressDetail" :value="old('AddressDetail', $customer->endereco->AddressDetail ?? 'Desconhecido')" />
                                                    </div>
                                                    <div class="row mt-4">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <x-label for="Province" value="{{ __('Província') }}" />
                                                                <select name="Province" id="Province" class="form-control">
                                                                    <option value="" disabled {{ empty($customer->endereco->Province ?? null) ? 'selected' : '' }}>
                                                                        {{ __('Selecione uma Província') }}
                                                                    </option>
                                                                    @foreach ($provincias as $provincia)
                                                                        <option 
                                                                            value="{{ $provincia->Nome }}" 
                                                                            @selected($provincia->Nome === ($customer->endereco->Province ?? ''))>
                                                                            {{ __($provincia->Nome) }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <x-label for="municipality" value="{{ __('Município') }}" />
                                                                <x-input id="municipality" type="text" class="form-input" list="municipalityList" name="municipality" value="{{ old('municipality', $customer->endereco->municipality ?? '') }}" />
                                                                <datalist id="municipalityList">
                                                                    
                                                                </datalist>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <x-label for="City" value="{{ __('Distrito') }}" />
                                                                <x-input id="City" class="form-input" type="text" name="City" value="{{ old('City', $customer->endereco->City ?? '') }}" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <x-label for="BuildingNumber" value="{{ __('Rua, Andar, Apartamento') }}" />
                                                                <x-input id="BuildingNumber" class="form-input" type="text" name="BuildingNumber" value="{{ old('BuildingNumber',$customer->endereco->BuildingNumber ?? '') }}" required />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Fim dos campos de endereço -->
                                                </div>
                                                <div class="tab-pane fade show" id="aduaneiro" role="tabpanel" aria-labelledby="aduaneiro-tab">
                                                    <!-- Informações Aduaneiras -->
                                                    <div class="mt-4">
                                                        <div class="row">
                                                            
                                                            <!-- Tipo de Cliente -->
                                                            <div class="col-md-6 form-group">
                                                                <label for="tipo_cliente" class="block font-medium text-gray-700">Tipo de Cliente *</label>
                                                                <select id="tipo_cliente" name="tipo_cliente" class="form-control">
                                                                    <option value="" disabled {{ old('tipo_cliente') === null ? 'selected' : '' }}>Selecione</option>
                                                                    <option value="importador" {{ old('tipo_cliente', $customer->tipo_cliente) == 'importador' ? 'selected' : '' }}>Importador</option>
                                                                    <option value="exportador" {{ old('tipo_cliente', $customer->tipo_cliente) == 'exportador' ? 'selected' : '' }}>Exportador</option>
                                                                    <option value="ambos" {{ old('tipo_cliente', $customer->tipo_cliente) == 'ambos' ? 'selected' : '' }}>Ambos</option>
                                                                </select>
                                                                <p id="tipo_cliente_desc" class="text-sm text-gray-500">Escolha o tipo de cliente.</p>
                                                            </div>

                                                            <!-- Tipo de Mercadoria -->
                                                            <div class="col-md-6 form-group">
                                                                <label for="tipo_mercadoria" class="block font-medium text-gray-700">Tipo de Mercadoria *</label>
                                                                <select id="tipo_mercadoria" name="tipo_mercadoria" aria-describedby="mercadoria_desc"
                                                                    class="form-control">
                                                                    <option value="" disabled {{ old('tipo_mercadoria') === null ? 'selected' : '' }}>Selecione</option>
                                                                    <option value="diversas" {{ old('tipo_mercadoria', $customer->tipo_mercadoria) === 'diversas' ? 'selected' : '' }}>Mercadorias Diversas</option>
                                                                    <option value="petroleo" {{ old('tipo_mercadoria', $customer->tipo_mercadoria) === 'petroleo' ? 'selected' : '' }}>Petroleo</option> <!-- Substituí "CRUD" por algo mais provável -->
                                                                    <option value="escritório" {{ old('tipo_mercadoria', $customer->tipo_mercadoria) === 'escritório' ? 'selected' : '' }}>Material de Escritórios</option>
                                                                    <option value="informáticos" {{ old('tipo_mercadoria', $customer->tipo_mercadoria) === 'informáticos' ? 'selected' : '' }}>Material Informático</option>
                                                                    <option value="electronicos" {{ old('tipo_mercadoria', $customer->tipo_mercadoria) === 'electronicos' ? 'selected' : '' }}>Electrónicos</option>
                                                                    <option value="outros" {{ old('tipo_mercadoria', $customer->tipo_mercadoria) === 'outros' ? 'selected' : '' }}>Outros</option>
                                                                </select>
                                                                <p id="mercadoria_desc" class="text-sm text-gray-500">Selecione o tipo de mercadoria.</p>
                                                            </div>

                                                            <!-- Frequência de Operações -->
                                                            <div class="col-md-3 form-group">
                                                                <label for="frequencia" class="block font-medium text-gray-700">Frequência de Operações</label>
                                                                <select id="frequencia" name="frequencia" class="form-control">
                                                                    <option value="" disabled {{ old('frequencia', $customer->frequencia) === null ? 'selected' : '' }}>Selecione</option>
                                                                    <option value="ocasional" {{ old('frequencia', $customer->frequencia) == 'ocasional' ? 'selected' : '' }}>Ocasional</option>
                                                                    <option value="mensal" {{ old('frequencia', $customer->frequencia) == 'mensal' ? 'selected' : '' }}>Mensal</option>
                                                                    <option value="anual" {{ old('frequencia', $customer->frequencia) == 'anual' ? 'selected' : '' }}>Anual</option>
                                                                </select>
                                                                <p id="frequencia_desc" class="text-sm text-gray-500">Escolha a frequência de operações aduaneiras.</p>
                                                            </div>

                                                            <div class="col-md-3 form-group">
                                                                <label for="moeda_operacao">Moeda de trasanção (Preferencial)</label>
                                                                <select id="moeda_operacao" name="moeda_operacao" class="form-control">sss
                                                                    <option value="" disabled {{ old('moeda_operacao') === null ? 'selected' : '' }}>Selecione</option>
                                                                    <option value="USD" {{ old('moeda_operacao', $customer->moeda_operacao) == 'USD' ? 'selected' : '' }}>USD</option>
                                                                    <option value="EUR" {{ old('moeda_operacao', $customer->moeda_operacao) == 'EUR' ? 'selected' : '' }}>EUR</option>
                                                                    <option value="AOA" {{ old('moeda_operacao', $customer->moeda_operacao) == 'AOA' ? 'selected' : '' }}>AOA</option>
                                                                </select>
                                                            </div>
                                                            <br>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label for="num_licenca">Nº de Licença</label>
                                                                    <input type="text" class="form-control" name="num_licenca" id="" value="{{ old('num_licenca',$customer->num_licenca ?? '')}}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="validade_licenca">Validade da Licença</label>
                                                                    <input type="date" class="form-control" name="validade_licenca" id="validade_licenca" value="{{ old('validade_licenca', $customer->validade_licenca ?? '')}}">
                                                                </div>
                                                            </div>
                                                            <!-- Observações -->
                                                            <div>
                                                                <label for="observacoes" class="block font-medium text-gray-700">Observações</label>
                                                                <textarea id="observacoes" name="observacoes" rows="4" aria-describedby="observacoes_desc"
                                                                    class="form-control"></textarea>
                                                                <p id="observacoes_desc" class="text-sm text-gray-500">Adicione informações adicionais, se necessário.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-info">Actualizar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade show" id="contabilidade" role="tabpanel" aria-labelledby="contabilidade-tab">
                                    Contabilidade <br>
                                    Avença
                                </div>
                                <div class="tab-pane fade show" id="documento" role="tabpanel" aria-labelledby="documento-tab">
                                    <fieldset class="mb-4">
                                        
                                        <div class="row g-3 mt-2">

                                            <!-- Tipo de Documento -->
                                            <div class="col-md-4">
                                                <label for="tipo_documento" class="form-label fw-medium">Tipo de Documento *</label>
                                                <select id="tipo_documento" name="tipo_documento" class="form-select" required>
                                                    <option value="" disabled {{ old('tipo_documento') === null ? 'selected' : '' }}>Selecione</option>
                                                    <option value="bi" {{ old('tipo_documento') == 'bi' ? 'selected' : '' }}>Bilhete de Identidade</option>
                                                    <option value="passaporte" {{ old('tipo_documento') == 'passaporte' ? 'selected' : '' }}>Passaporte</option>
                                                    <option value="nif" {{ old('tipo_documento') == 'nif' ? 'selected' : '' }}>NIF</option>
                                                    <option value="alvara" {{ old('tipo_documento') == 'alvara' ? 'selected' : '' }}>Alvará Comercial</option>
                                                    <option value="licenca" {{ old('tipo_documento') == 'licenca' ? 'selected' : '' }}>Licença de Importação/Exportação</option>
                                                </select>
                                            </div>

                                            <!-- Número do Documento -->
                                            <div class="col-md-4">
                                                <label for="numero_documento" class="form-label fw-medium">Número do Documento *</label>
                                                <input type="text" id="numero_documento" name="numero_documento" value="{{ old('numero_documento') }}" class="form-control">
                                            </div>

                                            <!-- Validade do Documento -->
                                            <div class="col-md-4">
                                                <label for="validade_documento" class="form-label fw-medium">Data de Validade</label>
                                                <input type="date" id="validade_documento" name="validade_documento" value="{{ old('validade_documento') }}" class="form-control">
                                            </div>

                                            <!-- Upload do Documento -->
                                            <div class="col-md-6">
                                                <label for="upload_documento" class="form-label fw-medium">Anexar Documento (PDF ou Imagem) *</label>
                                                <input type="file" id="upload_documento" name="upload_documento" accept=".pdf, .jpg, .jpeg, .png" required 
                                                    class="form-control">
                                            </div>

                                            <!-- Observações -->
                                            <div class="col-6">
                                                <label for="observacoes_documento" class="form-label fw-medium">Observações</label>
                                                <textarea id="observacoes_documento" name="observacoes_documento" rows="3" class="form-control">{{ old('observacoes_documento') }}</textarea>
                                            </div>

                                        </div>
                                    </fieldset>
                                    <div class="card">
                                        <div class="card-header">
                                            <span class="card-title">Lista de Documentos</span>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <thead>
                                                    <th>Documento</th>
                                                    <th>Tipo</th>
                                                    <th>Validade</th>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                                <tfoot>

                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>