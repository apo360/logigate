<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => $customer->CompanyName, 'url' => route('customers.show', $customer->id)],
        ['name' => 'Editar Cliente', 'url' => route('customers.edit', $customer->id)]
    ]" separator="/" />

    <div class="bg-white border border-gray-300 rounded-lg shadow-sm p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex space-x-2">
                <a href="{{ route('customers.index') }}" class="btn flex items-center space-x-2 px-3 py-1.5 border rounded-md hover:bg-gray-100">
                    <i class="fas fa-search text-gray-500"></i>
                    <span>Pesquisar</span>
                </a>
                <a href="{{ route('customers.create') }}" class="btn flex items-center space-x-2 px-3 py-1.5 border border-blue-500 rounded-md hover:bg-blue-50 text-blue-600">
                    <i class="fas fa-plus"></i>
                    <span>Novo Cliente</span>
                </a>

                <!-- Dropdown -->
                <div class="relative">
                    <button type="button" class="flex items-center px-3 py-1.5 border rounded-md hover:bg-gray-100" id="dropdownButton">
                        <i class="fas fa-filter mr-2"></i> Opções
                        <i class="fas fa-chevron-down ml-2 text-sm"></i>
                    </button>
                    <div class="absolute hidden bg-white border border-gray-200 rounded-md shadow-lg mt-1 w-48 z-10" id="dropdownMenu">
                        <a href="{{ route('customers.ficha_imprimir', ['id' => $customer->id]) }}" class="block px-4 py-2 hover:bg-gray-100">
                            <i class="fas fa-file-pdf mr-2 text-red-500"></i> Imprimir
                        </a>
                        <a href="{{ route('customers.show', $customer->id) }}" class="block px-4 py-2 hover:bg-gray-100">
                            <i class="fas fa-eye mr-2 text-blue-500"></i> Visualizar
                        </a>
                        <a href="{{ route('customers.create') }}" class="block px-4 py-2 hover:bg-gray-100">
                            <i class="fas fa-folder-open mr-2 text-gray-500"></i> Abrir Processo
                        </a>
                        <a href="{{ route('customers.create') }}" class="block px-4 py-2 hover:bg-gray-100">
                            <i class="fas fa-cogs mr-2 text-green-500"></i> Iniciar Licenciamento
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div>
            <ul class="flex border-b text-sm font-medium" id="mainTabs">
                <li><button class="px-4 py-2 border-b-2 border-blue-500 text-blue-600" data-tab="info">Info</button></li>
                <li><button class="px-4 py-2 hover:border-b-2 hover:border-blue-400" data-tab="contabilidade">Contabilidade</button></li>
                <li><button class="px-4 py-2 hover:border-b-2 hover:border-blue-400" data-tab="documentos">Documentos</button></li>
            </ul>

            <div id="tabContent" class="mt-4">
                <!-- TAB: INFO -->
                <div id="info" class="tab-pane block">
                    <form method="POST" action="{{ route('customers.update', $customer->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Sub-tabs -->
                        <ul class="flex border-b text-sm font-medium mt-2" id="infoSubTabs">
                            <li><button class="px-4 py-2 border-b-2 border-blue-500 text-blue-600" data-subtab="endereco">Endereço</button></li>
                            <li><button class="px-4 py-2 hover:border-b-2 hover:border-blue-400" data-subtab="aduaneiro">Info Aduaneira</button></li>
                        </ul>

                        <div id="subTabContent" class="mt-4">
                            <!-- Subtab: Endereço -->
                            <div id="endereco" class="subtab-pane block space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <x-label for="CustomerTaxID" value="NIF Cliente" />
                                        <x-input id="CustomerTaxID" name="CustomerTaxID" type="text" value="{{ old('CustomerTaxID', $customer->CustomerTaxID) }}" />
                                    </div>
                                    <div>
                                        <x-label for="CompanyName" value="Cliente" />
                                        <x-input id="CompanyName" name="CompanyName" type="text" value="{{ old('CompanyName', $customer->CompanyName) }}" readonly />
                                    </div>
                                    <div>
                                        <x-label for="Email" value="Email" />
                                        <x-input id="Email" name="Email" type="email" value="{{ old('Email', $customer->Email ?? '') }}" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <x-label for="Telephone" value="Telefone" />
                                        <x-input id="Telephone" name="Telephone" type="text" value="{{ old('Telephone', $customer->Telephone ?? '') }}" required />
                                    </div>
                                    <div>
                                        <x-label for="PostalCode" value="Código Postal" />
                                        <x-input id="PostalCode" name="PostalCode" type="text" value="{{ old('PostalCode', $customer->endereco->PostalCode ?? '00000') }}" />
                                    </div>
                                    <div>
                                        <x-label for="SelfBillingIndicator" value="Autofaturação" />
                                        <select id="SelfBillingIndicator" name="SelfBillingIndicator" class="form-select w-full border-gray-300 rounded-md">
                                            <option value="0" @selected($customer->SelfBillingIndicator == 0)>Não</option>
                                            <option value="1" @selected($customer->SelfBillingIndicator == 1)>Sim</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <x-label for="nacionality" value="País" />
                                        <select name="nacionality" id="nacionality" class="form-select w-full border-gray-300 rounded-md">
                                            @foreach($paises as $pais)
                                                <option value="{{ $pais->id }}" @selected($pais->id == $customer->nacionality)>{{ $pais->pais }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-label for="doc_type" value="Tipo de Documento" />
                                        <select name="doc_type" id="doc_type" class="form-select w-full border-gray-300 rounded-md">
                                            <option value="">Selecionar</option>
                                            <option value="BI" @selected($customer->doc_type == 'BI')>Bilhete de Identidade</option>
                                            <option value="PASS" @selected($customer->doc_type == 'PASS')>Passaporte</option>
                                            <option value="CC" @selected($customer->doc_type == 'CC')>Carta de Condução</option>
                                            <option value="CR" @selected($customer->doc_type == 'CR')>Cartão de Residência</option>
                                        </select>
                                    </div>
                                    <div>
                                        <x-label for="doc_num" value="Nº Documento" />
                                        <x-input id="doc_num" name="doc_num" type="text" value="{{ old('doc_num', $customer->doc_num ?? '') }}" />
                                    </div>
                                    <div>
                                        <x-label for="validade_date_doc" value="Validade" />
                                        <x-input id="validade_date_doc" name="validade_date_doc" type="date" value="{{ old('validade_date_doc', $customer->validade_date_doc ?? '') }}" />
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <x-label for="AddressDetail" value="Morada Completa" />
                                    <x-input id="AddressDetail" name="AddressDetail" type="text" value="{{ old('AddressDetail', $customer->endereco->AddressDetail ?? '') }}" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                                    <div>
                                        <x-label for="Province" value="Província" />
                                        <select id="Province" name="Province" class="form-select w-full border-gray-300 rounded-md">
                                            @foreach ($provincias as $provincia)
                                                <option value="{{ $provincia->Nome }}" @selected($provincia->Nome === ($customer->endereco->Province ?? ''))>{{ $provincia->Nome }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-label for="municipality" value="Município" />
                                        <x-input id="municipality" name="municipality" type="text" value="{{ old('municipality', $customer->endereco->municipality ?? '') }}" />
                                    </div>
                                    <div>
                                        <x-label for="City" value="Distrito" />
                                        <x-input id="City" name="City" type="text" value="{{ old('City', $customer->endereco->City ?? '') }}" />
                                    </div>
                                    <div>
                                        <x-label for="BuildingNumber" value="Rua, Andar, Apartamento" />
                                        <x-input id="BuildingNumber" name="BuildingNumber" type="text" value="{{ old('BuildingNumber',$customer->endereco->BuildingNumber ?? '') }}" />
                                    </div>
                                </div>
                            </div>

                            <!-- Subtab: Info Aduaneira -->
                            <div id="aduaneiro" class="subtab-pane hidden space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-label for="tipo_cliente" value="Tipo de Cliente" />
                                        <select id="tipo_cliente" name="tipo_cliente" class="form-select w-full border-gray-300 rounded-md">
                                            <option value="importador" @selected($customer->tipo_cliente == 'importador')>Importador</option>
                                            <option value="exportador" @selected($customer->tipo_cliente == 'exportador')>Exportador</option>
                                            <option value="ambos" @selected($customer->tipo_cliente == 'ambos')>Ambos</option>
                                        </select>
                                    </div>
                                    <div>
                                        <x-label for="tipo_mercadoria" value="Tipo de Mercadoria" />
                                        <select id="tipo_mercadoria" name="tipo_mercadoria" class="form-select w-full border-gray-300 rounded-md">
                                            <option value="diversas" @selected($customer->tipo_mercadoria == 'diversas')>Mercadorias Diversas</option>
                                            <option value="petroleo" @selected($customer->tipo_mercadoria == 'petroleo')>Petróleo</option>
                                            <option value="informáticos" @selected($customer->tipo_mercadoria == 'informáticos')>Material Informático</option>
                                            <option value="electronicos" @selected($customer->tipo_mercadoria == 'electronicos')>Electrónicos</option>
                                            <option value="outros" @selected($customer->tipo_mercadoria == 'outros')>Outros</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <x-label for="frequencia" value="Frequência" />
                                        <select id="frequencia" name="frequencia" class="form-select w-full border-gray-300 rounded-md">
                                            <option value="ocasional" @selected($customer->frequencia == 'ocasional')>Ocasional</option>
                                            <option value="mensal" @selected($customer->frequencia == 'mensal')>Mensal</option>
                                            <option value="anual" @selected($customer->frequencia == 'anual')>Anual</option>
                                        </select>
                                    </div>
                                    <div>
                                        <x-label for="moeda_operacao" value="Moeda Preferencial" />
                                        <select id="moeda_operacao" name="moeda_operacao" class="form-select w-full border-gray-300 rounded-md">
                                            <option value="USD" @selected($customer->moeda_operacao == 'USD')>USD</option>
                                            <option value="EUR" @selected($customer->moeda_operacao == 'EUR')>EUR</option>
                                            <option value="AOA" @selected($customer->moeda_operacao == 'AOA')>AOA</option>
                                        </select>
                                    </div>
                                    <div>
                                        <x-label for="num_licenca" value="Nº de Licença" />
                                        <x-input id="num_licenca" name="num_licenca" type="text" value="{{ old('num_licenca',$customer->num_licenca ?? '') }}" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-label for="validade_licenca" value="Validade da Licença" />
                                        <x-input id="validade_licenca" name="validade_licenca" type="date" value="{{ old('validade_licenca',$customer->validade_licenca ?? '') }}" />
                                    </div>
                                    <div>
                                        <x-label for="observacoes" value="Observações" />
                                        <textarea id="observacoes" name="observacoes" rows="3" class="form-textarea w-full border-gray-300 rounded-md">{{ old('observacoes',$customer->observacoes ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Atualizar</button>
                        </div>
                    </form>
                </div>

                <!-- TAB: CONTABILIDADE -->
                <div id="contabilidade" class="tab-pane hidden">
                    <p class="text-gray-700 mt-2">Módulo de Contabilidade e Avença em desenvolvimento...</p>
                </div>

                <!-- TAB: DOCUMENTOS -->
                <div id="documentos" class="tab-pane hidden">
                    <form method="POST" action="{{ route('customers.documents.store', $customer->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label for="tipo_documento" value="Tipo de Documento" />
                                <select id="tipo_documento" name="tipo_documento" class="form-select w-full border-gray-300 rounded-md" required>
                                    <option value="bi">Bilhete de Identidade</option>
                                    <option value="passaporte">Passaporte</option>
                                    <option value="nif">NIF</option>
                                    <option value="alvara">Alvará Comercial</option>
                                    <option value="licenca">Licença de Importação/Exportação</option>
                                </select>
                            </div>
                            <div>
                                <x-label for="numero_documento" value="Número do Documento" />
                                <x-input id="numero_documento" name="numero_documento" type="text" required />
                            </div>
                            <div>
                                <x-label for="validade_documento" value="Validade" />
                                <x-input id="validade_documento" name="validade_documento" type="date" />
                            </div>
                            <div>
                                <x-label for="upload_documento" value="Anexar Documento (PDF ou Imagem)" />
                                <input id="upload_documento" name="upload_documento" type="file" accept=".pdf,.jpg,.jpeg,.png" class="w-full border-gray-300 rounded-md" required />
                            </div>
                            <div class="col-span-2">
                                <x-label for="observacoes_documento" value="Observações" />
                                <textarea id="observacoes_documento" name="observacoes_documento" rows="3" class="form-textarea w-full border-gray-300 rounded-md"></textarea>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Anexar Documento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple JS for Tabs -->
    <script>
        // Main Tabs
        document.querySelectorAll('#mainTabs button').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('hidden'));
                document.getElementById(btn.dataset.tab).classList.remove('hidden');
                document.querySelectorAll('#mainTabs button').forEach(b => b.classList.remove('border-b-2', 'border-blue-500', 'text-blue-600'));
                btn.classList.add('border-b-2', 'border-blue-500', 'text-blue-600');
            });
        });

        // Sub Tabs (Info)
        document.querySelectorAll('#infoSubTabs button').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.subtab-pane').forEach(p => p.classList.add('hidden'));
                document.getElementById(btn.dataset.subtab).classList.remove('hidden');
                document.querySelectorAll('#infoSubTabs button').forEach(b => b.classList.remove('border-b-2', 'border-blue-500', 'text-blue-600'));
                btn.classList.add('border-b-2', 'border-blue-500', 'text-blue-600');
            });
        });

        // Dropdown Menu
        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownMenu = document.getElementById('dropdownMenu');
        dropdownButton.addEventListener('click', () => dropdownMenu.classList.toggle('hidden'));
    </script>
</x-app-layout>
<!-- --- a/file:///www/wwwroot/aduaneiro.hongayetu.com/resources/views/customer/customer_edit.blade.php -->