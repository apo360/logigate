<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => $customer->CompanyName, 'url' => route('customers.show', $customer->id)],
        ['name' => 'Editar Cliente', 'url' => route('customers.edit', $customer->id)]
    ]" separator="/" />

    <div class="card">
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
                                    <button class="nav-link active button" id="endereco-tab" data-bs-toggle="tab" data-bs-target="#endereco" type="button" role="tab" aria-controls="endereco" aria-selected="true">Endereço</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="fiscal-tab button" data-bs-toggle="tab" data-bs-target="#fiscal" type="button" role="tab" aria-controls="fiscal" aria-selected="false">Facturação</button>
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
                                <div class="tab-pane fade show active" id="endereco" role="tabpanel" aria-labelledby="endereco-tab">
                                    <form method="POST" action="{{ route('customers.update', $customer->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mt-2">
                                                    <x-label for="CustomerTaxID" value="{{ __('NIF Cliente') }}" />
                                                    <x-input id="CustomerTaxID" class="block mt-1 w-full" type="text" name="CustomerTaxID" value="{{ $customer->CustomerTaxID }}" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mt-2">
                                                    <x-label for="CompanyName" value="{{ __('Cliente') }}" />
                                                    <x-input id="CompanyName" class="block mt-1 w-full" type="text" name="CompanyName" value="{{ $customer->CompanyName }}" readonly />
                                                </div>
                                            </div>
                                            <div class="col-md-4 mt-2">
                                                <x-label for="Email" value="{{ __('Email') }}" />
                                                <x-input id="Email" class="block mt-1 w-full" type="email" name="Email" autocomplete="Email" value="{{ $customer->Email }}" />
                                            </div>
                                        </div> 

                                        <div class="row">
                                            <div class="col-md-4 mt-3">
                                                <x-label for="Telephone" value="{{ __('Telefone') }}" />
                                                <x-input id="Telephone" class="block mt-1 w-full" type="text" name="Telephone" required autocomplete="Telephone" value="{{ $customer->Telephone }}" />
                                            </div>

                                            <div class="col-md-4 mt-3">
                                                <x-label for="PostalCode" value="{{ __('Código Postal') }}" />
                                                <x-input id="PostalCode" class="form-control" type="text" name="PostalCode" value="{{ $customer->endereco->PostalCode }}" />
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
                                                <input type="text" name="doc_num" id="doc_num" class="form-control" value="{{ $customer->doc_num }}">
                                            </div>
                                            <div class="col-md-3 mt-3">
                                                <label for="validade_date_doc">Data de Validade</label>
                                                <input type="date" name="validade_date_doc" id="validade_date_doc" class="form-control" value="{{ $customer->validade_date_doc }}">
                                            </div>
                                        </div>

                                        <!-- Campos de endereço -->
                                         <hr class="mt-2">
                                        <div class="row mt-4">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <x-label for="Province" value="{{ __('Província') }}" />
                                                    <select name="Province" id="Province" class="form-control">
                                                        @foreach($provincias as $provincia)
                                                            <option value="{{$provincia->Nome}}" {{ $provincia->Nome == $customer->endereco->Province ? 'selected' : '' }}>{{__($provincia->Nome)}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <x-label for="municipality" value="{{ __('Município') }}" />
                                                    <x-input id="municipality" class="form-input" list="municipalityList" name="municipality" :value="old('municipality')" required />
                                                    <datalist id="municipalityList">
                                                        
                                                    </datalist>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <x-label for="City" value="{{ __('Distrito') }}" />
                                                    <x-input id="City" class="form-input" type="text" name="City" :value="old('City')" required />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <x-label for="AddressDetail" value="{{ __('Morada Completa') }}" />
                                            <x-input id="AddressDetail" class="form-input" type="text" name="AddressDetail" :value="old('AddressDetail')" required />
                                        </div>

                                        <div class="form-group">
                                            <x-label for="BuildingNumber" value="{{ __('Rua, Andar, Apartamento') }}" />
                                            <x-input id="BuildingNumber" class="form-input" type="text" name="BuildingNumber" :value="old('BuildingNumber')" required />
                                        </div>

                                    </form>
                                    <!-- Fim dos campos de endereço -->
                                </div>
                                <div class="tab-pane fade show" id="fiscal" role="tabpanel" aria-labelledby="fiscal-tab">
                                    <div class="container">
                                        <div class="form-group">
                                            <x-label for="nif" value="{{ __('NIF') }}" />
                                            <x-input id="nif" class="block mt-1 w-full" type="text" name="nif" :value="old('nif')" required autofocus />
                                        </div>
                                        
                                        <div class="form-group">
                                            <x-label for="payment_mode" value="{{ __('Modo de Pagamento') }}" />
                                        </div>

                                        <div class="form-group">
                                            <x-label for="iva_exercise" value="{{ __('Exercício do IVA') }}" />
                                        </div>

                                        @if (old('iva_exercise') === 'Isento')
                                            <div class="form-group">
                                                <x-label for="iva_exercise_reason" value="{{ __('Motivo (com base na lei)') }}" />
                                                <x-input id="iva_exercise_reason" class="block mt-1 w-full" type="text" name="iva_exercise_reason" :value="old('iva_exercise_reason')" />
                                            </div>
                                        @endif

                                        <br>
                                        <!-- Rest of the fields -->

                                    </div>

                                </div>
                                <div class="tab-pane fade show" id="contabilidade" role="tabpanel" aria-labelledby="contabilidade-tab">
                                    conta
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>