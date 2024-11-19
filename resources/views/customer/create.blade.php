<x-app-layout>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <div class="py-12">
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => 'Novo Cliente' , 'url' => '']
    ]" separator="/" />
        <div class="row">
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
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-label for="CompanyName" value="{{ __('Empresa') }}" />
                                        <x-input id="CompanyName" class="block mt-1 w-full" type="text" name="CompanyName" required autofocus autocomplete="CompanyName" />
                                    </div>

                                    <div class="col-md-6">
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

                                    <div class="col-md-6 mt-3">
                                        <x-label for="Country" value="{{ __('Tipo de Cliente') }}" />
                                        <x-input id="Country" class="form-control" type="text" name="Country" value="Individual" />
                                    </div>

                                    <div class="col-md-6 mt-3">
                                        <x-label for="Fax" value="{{ __('Fax') }}" />
                                        <x-input id="Fax" class="form-control" type="text" name="Fax" />
                                    </div>

                                    <div class="col-md-6 mt-3">
                                        <x-label for="Website" value="{{ __('Website') }}" />
                                        <x-input id="Website" class="form-control" type="text" name="Website" />
                                    </div>

                                    <div class="col-md-6 mt-3">
                                        <x-label for="SelfBillingIndicator" value="{{ __('Indicador de Autofaturação') }}" />
                                        <select id="SelfBillingIndicator" class="form-control" name="SelfBillingIndicator">
                                            <option value="0">Não</option>
                                            <option value="1">Sim</option>
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
</x-app-layout>
