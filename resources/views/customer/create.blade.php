<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => 'Novo Cliente' , 'url' => '']
    ]" separator="/" />
        <div class="container">
            <div class="col-12">
                <form method="POST" action="{{ route('customers.store') }}">
                    @csrf
                    <div class="card card-navy">
                        <div class="card-header">
                            <div class="float-left">
                                <a href="{{ route('customers.index') }}" class="btn btn-default">
                                    <i class="fas fa-search" style="color: #0170cf;"></i> {{ __('Pesquisar Cliente') }}
                                </a>
                            </div>
                            <div class="float-right">
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-5">
                                    <x-label for="CustomerTaxID" value="{{ __('NIF') }}" />
                                    <x-input-button namebutton="Validar NIF" idButton="CustomerTaxID" type="text" name="CustomerTaxID" value="000000" />
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="container">
                                <div class="col-md-6 mt-3">
                                    <x-label for="CustomerType" value="{{ __('Tipo de Cliente') }}" />
                                    <select name="CustomerType" id="CustomerType" class="form-control" required>
                                        <option value="">Selecionar</option>
                                        <option value="Individual">{{__('Individual')}}</option>
                                        <option value="Empresa">{{__('Empresa')}}</option>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mt-3">
                                        <x-label for="CompanyName" value="{{ __('Empresa') }}" />
                                        <x-input id="CompanyName" class="block mt-1 w-full" type="text" name="CompanyName" required autofocus autocomplete="CompanyName" />
                                    </div>

                                    <div class="col-md-6 mt-3">
                                        <x-label for="Email" value="{{ __('Email') }}" />
                                        <x-input id="Email" class="block mt-1 w-full" type="email" name="Email" autocomplete="Email" />
                                    </div>

                                    <div class="col-md-4 mt-3">
                                        <x-label for="Telephone" value="{{ __('Telefone') }}" />
                                        <x-input id="Telephone" class="block mt-1 w-full" type="text" name="Telephone" required autocomplete="Telephone" />
                                    </div>

                                    <div class="col-md-4 mt-3">
                                        <x-label for="PostalCode" value="{{ __('Código Postal') }}" />
                                        <x-input id="PostalCode" class="form-control" type="text" name="PostalCode" value="0000-000" />
                                    </div>

                                    <div class="col-md-4 mt-3">
                                        <x-label for="Province" value="{{ __('Província') }}" />
                                        <select name="Province" id="Province" class="form-control" required>
                                            <option value="">Selecionar</option>
                                            @foreach($provincias as $provincia)
                                                <option value="{{$provincia->Nome}}">{{__($provincia->Nome)}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Se o tipo de cliente for individual activa a parte de documentos -->
                                    <div class="nacional mt-3" id="document-section" style="display:none;">
                                        <div class="col-md-6">
                                            <label for="nacionality">Nacionalidade</label>
                                            <select name="nacionality" id="nacionality" class="form-control">
                                                @foreach($paises as $pais)
                                                    <option value="{{ $pais->id }}" {{ $pais->pais == 'Angola' ? 'selected' : '' }}> {{ $pais->pais}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4 mt-3">
                                                <label for="doc_type">Tipo de Documento</label>
                                                <select name="doc_type" id="doc_type" class="form-control">
                                                    <option value="BI">Bilhete de Identidade</option>
                                                    <option value="PASS">Passaporte</option>
                                                    <option value="CC">Carta de Condução</option>
                                                    <option value="CR">Cartão de Residência</option>
                                                    <option value="">Outro</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="doc_num">Nº do Documento</label>
                                                <input type="text" name="doc_num" id="doc_num" class="form-control">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="validade_date_doc">Data de Validade</label>
                                                <input type="date" name="validade_date_doc" id="validade_date_doc" class="form-control">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-4 mt-3">
                                            <x-label for="Fax" value="{{ __('Fax') }}" />
                                            <x-input id="Fax" class="form-control" type="text" name="Fax" />
                                        </div>

                                        <div class="col-md-4 mt-3">
                                            <x-label for="Website" value="{{ __('Website') }}" />
                                            <x-input id="Website" class="form-control" type="text" name="Website" />
                                        </div>

                                        <div class="col-md-4 mt-3">
                                            <x-label for="SelfBillingIndicator" value="{{ __('Indicador de Autofaturação') }}" />
                                            <select id="SelfBillingIndicator" class="form-control" name="SelfBillingIndicator">
                                                <option value="0">Não</option>
                                                <option value="1">Sim</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <x-label for="metodo_pagamento" value="{{ __('Metodo de Pagamento') }}" />
                                        <select name="metodo_pagamento" id="metodo_pagamento" class="form-control">
                                            <option value="">Selecionar</option>
                                            <option value="00">Pronto Pagamento</option>
                                            <option value="15">Pagamento 15 dias</option>
                                            <option value="30">Pagamento 30 dias</option>
                                            <option value="45">Pagamento 45 dias</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <x-button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus btn-icon" style="color: #0170cf;"></i> {{ __('Salvar Cliente') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#CustomerType').change(function() {
                if ($(this).val() === 'Individual') {
                    $('#document-section').show();
                } else {
                    $('#document-section').hide();
                }
            });

            // Validação do número do documento
            $('#doc_num').on('input', function() {
                var docType = $('#doc_type').val();
                var docNum = $(this).val();

                if (docType === 'BI' && !/^\d{9}[A-Z]{2}\d{3}$/.test(docNum)) {
                    alert('Formato do BI inválido. Deve ser 9 números, 2 letras maiúsculas, seguidos por 3 números.');
                }
            });
        });
    </script>
</x-app-layout>
