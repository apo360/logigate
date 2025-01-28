<x-app-layout>
    <x-breadcrumb :items="[ 
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Pesquisar Processos', 'url' => route('processos.index')],
        ['name' => 'Novo Processo (Exportação de Crud)', 'url' => route('create.crud')]
    ]" separator="/" />

    <div class="max-w-7xl mx-auto p-6 bg-white shadow-md rounded-lg">
        <form action="{{ route('processos.store') }}" method="POST">
            @csrf <!-- Token de segurança Laravel -->
            <div class="card">
                <div class="card-header">
                    <div class="float-left">
                        <a class="btn btn-default" style="color: black;" href="{{ route('processos.index') }}">
                            <i class="fas fa-search" style="color: black;"></i> {{ __('Pesquisar Processos') }}
                        </a>
                    </div>
                    <div class="float-right">
                        <div class="btn-group">
                            <button type="submit" name="action" value="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus btn-icon" style="color: #0170cf;"></i> {{ __('Salvar') }}
                            </button>
                            <button type="submit" name="action" value="draft" class="btn btn-secondary" title="Ao clicar o processo será um Rascunho!">
                                Salvar como Rascunho
                            </button>
                            <a href="#" id="add-new-exportdor" class="btn btn-default" data-toggle="modal" data-target="#newExportadorModal" title="Adicionar ficheiro de importação de um Processo">
                                Importar XML
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <!-- Nome do Navio -->
                    <input type="hidden" name="TipoTransporte" value="1">
                    <div class="row">
                        <!-- Regime Aduaneiro -->
                        <div class="col-md-4 mb-4">
                            <x-label for="regime_aduaneiro" value="Regime Aduaneiro" />
                            <x-input id="regime_aduaneiro" name="regime_aduaneiro" type="text" class="block w-full mt-1" placeholder="Digite o Regime Aduaneiro" />
                        </div>

                        <!-- Terminal -->
                        <div class="col-md-3 mb-4">
                            <x-label for="terminal" value="Terminal" />
                            <x-input id="terminal" name="terminal" type="text" class="block w-full mt-1" placeholder="Digite o terminal" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-4">
                            <x-label for="registo_transporte" value="Nome do Navio" />
                            <x-input id="registo_transporte" name="registo_transporte" type="text" class="block w-full mt-1" placeholder="Digite o nome do navio" />
                        </div>
                        <!-- V/Ref -->
                        <div class="col-md-3 mb-4">
                            <x-label for="RefCliente" value="Referencia do Cliente" />
                            <x-input id="RefCliente" name="RefCliente" type="text" class="block w-full mt-1" placeholder="Digite a referência" />
                        </div>
                    </div>

                    <div class="row">
                        <input type="hidden" name="Descricao" value="Exportação de CRUD">
                        <!-- Data de Carregamento -->
                        <div class="col-md-4 mb-4">
                            <x-label for="data_carregamento" value="Data de Carregamento" />
                            <x-input id="data_carregamento" name="data_carregamento" type="date" class="block w-full mt-1" />
                        </div>

                        <!-- Quantidade de Barris -->
                        <div class="col-md-3 mb-4">
                            <x-label for="quantidade_barris" value="Quantidade de Barris" />
                            <x-input id="quantidade_barris" name="quantidade_barris" type="number" min="0" class="block w-full mt-1" placeholder="Exemplo: 1000" />
                        </div>

                        <!-- Peso Bruto -->
                        <div class="col-md-3 mb-4">
                            <x-label for="peso_bruto" value="Peso Bruto (Kg)" />
                            <x-input id="peso_bruto" name="peso_bruto" type="number" step="0.01" class="block w-full mt-1" placeholder="Exemplo: 25000.5" />
                        </div>
                    </div>

                    <div class="row">
                        <!-- Nº Deslocações -->
                        <div class="col-md-3 mb-4">
                            <x-label for="num_deslocacoes" value="Nº de Deslocações" />
                            <x-input id="num_deslocacoes" name="num_deslocacoes" type="number" min="0" class="block w-full mt-1" placeholder="Exemplo: 5" />
                        </div>

                        <!-- RSM nº -->
                        <div class="col-md-3 mb-4">
                            <x-label for="rsm_num" value="RSM Nº" />
                            <x-input id="rsm_num" name="rsm_num" type="text" class="block w-full mt-1" placeholder="Digite o RSM nº" />
                        </div>

                        <!-- Certificado de Origem Nº -->
                        <div class="col-md-3 mb-4">
                            <x-label for="certificado_origem" value="Certificado de Origem Nº" />
                            <x-input id="certificado_origem" name="certificado_origem" type="text" class="block w-full mt-1" placeholder="Digite o Certificado de Origem nº" />
                        </div>

                        <!-- Guia de Exportação -->
                        <div class="col-md-3 mb-4">
                            <x-label for="guia_exportacao" value="Guia de Exportação" />
                            <x-input id="guia_exportacao" name="guia_exportacao" type="text" class="block w-full mt-1" placeholder="Digite o Guia de Exportação" />
                        </div>
                    </div>

                    <div class="row">
                        <!-- Método de Pagamento -->
                        <div class="col-md-4 mb-4">
                            <x-label for="forma_pagamento" value="Método de Pagamento" />
                            <select id="forma_pagamento" name="forma_pagamento" class="form-control">
                                <option value="" disabled selected>Selecione</option>
                                <option value="transferencia">Transferência Bancária</option>
                                <option value="caixa kwanda">Caixa Única Tesouro Base Kwanda</option>
                                <option value="dinheiro">Dinheiro</option>
                            </select>
                        </div>

                        <!-- Câmbio -->
                        <div class="col-md-3 mb-4">
                            <x-label for="Cambio" value="Câmbio (USD/KZ)" />
                            <x-input id="Cambio" name="Cambio" type="number" step="0.01" class="block w-full mt-1" placeholder="Exemplo: 800" />
                        </div>
                    </div>

                    <div class="row">
                        <!-- Valor USD -->
                        <div class="col-md-3 mb-4">
                            <x-label for="valor_usd" value="Valor (USD)" />
                            <x-input id="valor_usd" name="valor_usd" type="number" step="0.01" class="block w-full mt-1" placeholder="Exemplo: 100000.00" />
                        </div>

                        <!-- Valor do Barril USD -->
                        <div class="col-md-3 mb-4">
                            <x-label for="valor_barril_usd" value="Valor do Barril (USD)" />
                            <x-input id="valor_barril_usd" name="valor_barril_usd" type="number" step="0.01" class="block w-full mt-1" placeholder="Exemplo: 50.00" />
                        </div>

                        <!-- Valor Kz -->
                        <div class="col-md-3 mb-4">
                            <x-label for="ValorAduaneiro" value="Valor (Kz)" />
                            <x-input id="ValorAduaneiro" name="ValorAduaneiro" type="number" step="0.01" class="block w-full mt-1" placeholder="Exemplo: 80000000.00" />
                        </div>
                    </div>

                </div>
            </div>
            
        </form>
    </div>

    <!-- Scripts -->
     <script>
        // Calculo do Valor USD em função da quantidade de barris e o valor de cada barril
        document.getElementById('quantidade_barris').addEventListener('input', function () {
            const quantidadeBarris = parseFloat(this.value) || 0;
            const valorBarrilUSD = parseFloat(document.getElementById('valor_barril_usd').value) || 0;
            document.getElementById('valor_usd').value = (quantidadeBarris * valorBarrilUSD).toFixed(2);
        });

        document.getElementById('valor_barril_usd').addEventListener('input', function () {
            const quantidadeBarris = parseFloat(document.getElementById('quantidade_barris').value) || 0;
            const valorBarrilUSD = parseFloat(this.value) || 0;
            document.getElementById('valor_usd').value = (quantidadeBarris * valorBarrilUSD).toFixed(2);
        });

        // Calculo do Valor em Kwanza (Kz) em função do Câmbio e do Valor USD
        document.getElementById('valor_usd').addEventListener('input', function () {
            const cambio = parseFloat(document.getElementById('cambio').value) || 0;
            const valorUSD = parseFloat(this.value) || 0;
            document.getElementById('valor_kz').value = (cambio * valorUSD).toFixed(2);
        });

        document.getElementById('cambio').addEventListener('input', function () {
            const cambio = parseFloat(this.value) || 0;
            const valorUSD = parseFloat(document.getElementById('valor_usd').value) || 0;
            document.getElementById('valor_kz').value = (cambio * valorUSD).toFixed(2);
        });
     </script>
</x-app-layout>
