<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => {{__('Clientes')}}, 'url' => route('customers.index')],
        ['name' => {{__('Novo Cliente')}} , 'url' => '']
    ]" separator="/" />
    <br>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <form method="POST" action="{{ route('customers.store') }}">
                    @csrf
                    <div class="card card-navy">
                        <div class="card-header">
                            <div class="float-left">
                                <a type="button" href="{{ route('customers.index') }}" class="btn btn-default" style="color: black;">
                                    <i class="fas fa-search" style="color: black;"></i> {{ __('Pesquisar Cliente') }}
                                </a>
                            </div>
                            <div class="float-right">
                                <div class="btn-group">
                                    <x-button class="btn btn-default ">
                                        <i class="fas fa-user-plus btn-icon" style="color: #0170cf;"></i> {{ __('Inserir Cliente') }}
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mt-2">
                                        <x-label for="name" value="{{ __('Cliente ID') }}" />
                                        <x-input id="name" class="block mt-1 w-full" type="text" name="CustomerID" value="{{ $newCustomerCode }}" readonly />
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="mt-2">
                                        <x-label for="CustomerTaxID" value="{{ __('NIF') }}" />
                                        <x-input-button namebutton="Validar NIF" idButton="CustomerTaxID" type="text" name="CustomerTaxID" value="000000"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mt-2">
                                            <x-label for="CompanyName" value="{{ __('Empresa') }}" />
                                            <x-input id="CompanyName" class="block mt-1 w-full" type="text" name="CompanyName" required autofocus autocomplete="CompanyName" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mt-4">
                                            <x-label for="Telephone" value="{{ __('Telefone') }}" />
                                            <x-input id="Telephone" class="block mt-1 w-full" type="text" name="Telephone" required autofocus autocomplete="Telephone" />
                                        </div>
                                    </div>
                                
                                    <div class="col-md-6">
                                        <div class="mt-4">
                                            <x-label for="Email" value="{{ __('Email') }}" />
                                            <x-input id="Email" class="block mt-1 w-full" type="email" name="Email" autocomplete="Email" />
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <x-label for="PostalCode" :value="__('Código Postal')" />
                                        <x-input id="PostalCode" class="form-control" type="text" name="PostalCode" :value="old('PostalCode')" required />
                                    </div>

                                    <div class="mb-3">
                                        <x-label for="Province" :value="__('Província')" />
                                        <x-input id="Province" class="form-control" type="text" name="Province" :value="old('Province')" required />
                                    </div>

                                    <div class="mb-3">
                                        <x-label for="Country" :value="__('País')" />
                                        <x-input id="Country" class="form-control" type="text" name="Country" :value="old('Country')" value="AOA" required />
                                    </div>

                                    <div class="mb-3">
                                        <x-label for="Telephone" :value="__('Telefone')" />
                                        <x-input id="Telephone" class="form-control" type="text" name="Telephone" :value="old('Telephone')" required />
                                    </div>

                                    <div class="mb-3">
                                        <x-label for="Fax" :value="__('Fax')" />
                                        <x-input id="Fax" class="form-control" type="text" name="Fax" :value="old('Fax')" required />
                                    </div>

                                    <div class="mb-3">
                                        <x-label for="Email" :value="__('Email')" />
                                        <x-input id="Email" class="form-control" type="email" name="Email" :value="old('Email')" required />
                                    </div>

                                    <div class="mb-3">
                                        <x-label for="Website" :value="__('Website')" />
                                        <x-input id="Website" class="form-control" type="text" name="Website" :value="old('Website')" required />
                                    </div>

                                    <div class="mb-3">
                                        <x-label for="SelfBillingIndicator" :value="__('Indicador de Autofaturação')" />
                                        <x-input id="SelfBillingIndicator" class="form-control" type="text" name="SelfBillingIndicator" :value="old('SelfBillingIndicator')" required />
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>