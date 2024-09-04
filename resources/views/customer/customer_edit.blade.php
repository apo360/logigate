<x-app-layout>
    <x-slot name="header">
        <x-breadcrumb title="Editar Cliente" breadcrumb="Editar Cliente" />
    </x-slot>
    <br>

    <div class="container">
        @if (Session::has('status'))
            <x-alert-message type="success" :message="Session::get('success')" />
            <x-alert-message type="error" :message="Session::get('error')" /> 
        @endif

        <form method="POST" action="">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header"></div>
            </div>

            <div class="card card-dark">
                <div class="card-header">
                    <div class="card-title text-red">{{ $costumers->CustomerID }}</div>
                </div>
                <div class="card-body"></div>
            </div>
        </form>

    </div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
               
                <x-validation-errors class="mb-4" />
                <form method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between mt-4">
                        <div class="items-center">
                            <x-button>
                                <a class="button" href="{{ route('customers.index') }}">
                                    {{ __('Pesquisar') }}
                                </a>
                            </x-button>
                        </div>

                        <div class="items-center">
                            <x-button type="submit" class="button">{{ __('Atualizar') }}</x-button>
                            <x-button>
                                <a class="button " href=" {{ route('customers.print', $costumers->Id) }}">
                                    {{ __('Imprimir') }}
                                </a>
                            </x-button>
                            <x-button>
                                <a class="button" href=" {{ route('customers.create') }} ">
                                    {{ __('Novo') }}
                                </a>
                            </x-button>
                        </div>
                    </div>
                    <hr style="border-color: 2px solid black; background-color:black;">
                    <hr>
                    <div class="container">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mt-2">
                                    <x-label for="name" value="{{ __('Cliente ID') }}" />
                                    <x-input id="name" class="block mt-1 w-full" type="text" name="CustomerID" value="{{ $costumers->CustomerID }}" readonly />
                                </div>
                            </div>
                        </div> 

                        <!-- # Create tabs for DAR, DU, -->

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

                        <!-- Tab panes -->
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="endereco" role="tabpanel" aria-labelledby="endereco-tab">
                            <!-- Restante dos campos do formulário -->

                            <!-- Campos de endereço -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <x-label for="country" value="{{ __('País') }}" />
                                        <x-select name="country" :options="$countries" :selected="$selectedCountry" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <x-label for="province" value="{{ __('Província') }}" />
                                        <x-select name="province" :options="$provinces" :selected="$selectedProvinces" />
                                        
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
                            </div>
                            
                            <div class="form-group">
                                <x-label for="district" value="{{ __('Distrito') }}" />
                                <x-input id="district" class="form-input" type="text" name="district" :value="old('district')" required />
                            </div>

                            <div class="form-group">
                                <x-label for="full_address" value="{{ __('Morada Completa') }}" />
                                <x-input id="full_address" class="form-input" type="text" name="full_address" :value="old('full_address')" required />
                            </div>

                            <div class="form-group">
                                <x-label for="street" value="{{ __('Rua, Andar, Apartamento') }}" />
                                <x-input id="street" class="form-input" type="text" name="street" :value="old('street')" required />
                            </div>

                            <div class="form-group">
                                <x-label for="phone" value="{{ __('Telefone') }}" />
                                <x-input id="phone" class="form-input" type="text" name="phone" :value="old('phone')" required />
                            </div>

                            <div class="form-group">
                                <x-label for="alternative_phone" value="{{ __('Telefone Alternativo') }}" />
                                <x-input id="alternative_phone" class="form-input" type="text" name="alternative_phone" :value="old('alternative_phone')" />
                            </div>

                            <div class="form-group">
                                <x-label for="fax" value="{{ __('Fax') }}" />
                                <x-input id="fax" class="form-input" type="text" name="fax" :value="old('fax')" />
                            </div>

                            <div class="form-group">
                                <x-label for="email" value="{{ __('Email') }}" />
                                <x-input id="email" class="form-input" type="email" name="email" :value="old('email')" required />
                            </div>
                            <!-- Fim dos campos de endereço -->
                        </div>
                        <div class="tab-pane fade show" id="contrato" role="tabpanel" aria-labelledby="contrato-tab">

                            <div class="form-group">
                                <x-label for="contract_start_date" value="{{ __('Tipo de Contrato') }}" />
                                <x-select name="typeContact" :options="$typeContacts" :value="old('typeContact')" required />
                            </div>
                        
                            <div class="form-group">
                                <x-label for="contract_start_date" value="{{ __('Data de Início do Contrato') }}" />
                                <x-input id="contract_start_date" class="block mt-1 w-full" type="date" name="contract_start_date" :value="old('contract_start_date')" required />
                            </div>

                            <div class="form-group">
                                <x-label for="contract_end_date" value="{{ __('Data de Término do Contrato') }}" />
                                <x-input id="contract_end_date" class="block mt-1 w-full" type="date" name="contract_end_date" :value="old('contract_end_date')" />
                            </div>

                            <div class="form-group">
                                <x-label for="contract_terms" value="{{ __('Termos do Contrato') }}" />
                                <textarea id="contract_terms" class="form-control" name="contract_terms" rows="3">{{ old('contract_terms') }}</textarea>
                            </div>

                        </div>
                        <div class="tab-pane fade show" id="fiscal" role="tabpanel" aria-labelledby="fiscal-tab">
                            <div class="container">
                                <div class="form-group">
                                    <x-label for="nif" value="{{ __('NIF') }}" />
                                    <x-input id="nif" class="block mt-1 w-full" type="text" name="nif" :value="old('nif')" required autofocus />
                                </div>
                                
                                <div class="form-group">
                                    <x-label for="payment_mode" value="{{ __('Modo de Pagamento') }}" />
                                    <x-select name="payment_mode" :options="$paymentModes" :selected="old('payment_mode')" />
                                </div>

                                <div class="form-group">
                                    <x-label for="iva_exercise" value="{{ __('Exercício do IVA') }}" />
                                    <x-select name="iva_exercise" :options="$ivaExercises" :selected="old('iva_exercise')" />
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
                                
                </form>
            </div>
        </div>
    </div>
</x-app-layout>