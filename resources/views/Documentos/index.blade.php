<x-app-layout>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <x-breadcrumb :items="[
            ['name' => 'Dashboard', 'url' => route('dashboard')],
            ['name' => 'Facturação', 'url' => route('documentos.index')]
        ]" separator="/" />

    <div class="row">
        <div class="col-md-3">
            <!-- FILTROS - COLUNA 1 -->
            <div class="col-md-12">
                <div class="card card-dark">
                    <div class="card-header">
                        <div class="card-title">
                            <span>Filtros</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('faturas.filtrar') }}" method="GET">
                            <!-- FILTRO: Data -->
                            <h6 class="mb-3">Data</h6>
                            <div class="row">
                            <div class="form-group col-md-6">
                                <label for="dataInicial">Data Inicial:</label>
                                <input type="date" name="dataInicial" id="dataInicial" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="dataFinal">Data Final:</label>
                                <input type="date" name="dataFinal" id="dataFinal" class="form-control">
                            </div>
                            </div>

                            <!-- FILTRO: Estado -->
                            <h6 class="mt-4 mb-3">Estado</h6>
                            <div class="form-group">
                                <label for="estado">Estado:</label>
                                <select name="estado" id="estado" class="form-control">
                                    <option value="">Selecionar</option>
                                    <option value="pago">Pago</option>
                                    <option value="por pagar">Por Pagar</option>
                                    <option value="vencido">Vencido</option>
                                </select>
                            </div>

                            <!-- FILTRO: Período de Vencimento -->
                            <h6 class="mt-4 mb-3">Vencimento</h6>
                            <div class="form-group">
                                <label for="vencimento">Período de Vencimento:</label>
                                <select name="vencimento" id="vencimento" class="form-control">
                                    <option value="">Selecionar</option>
                                    <option value="vencidas">Vencidas</option>
                                    <option value="a_vencer">A Vencer</option>
                                    <option value="30_dias">Próximos 30 Dias</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- FILTROS - COLUNA 2 -->
            <div class="col-md-12">
                <div class="card card-dark">
                    <div class="card-header">
                        <div class="card-title flex justify-between items-center">
                            <span>Filtros Avançados</span>

                            <!-- Ícone para alternar entre expandir e colapsar -->
                            <button x-data="{ open: true }" @click="open = !open" class="text-lg">
                                <template x-if="open">
                                    <i class="fas fa-minus"></i> <!-- Ícone de menos (colapsar) -->
                                </template>
                                <template x-if="!open">
                                    <i class="fas fa-plus"></i> <!-- Ícone de mais (expandir) -->
                                </template>
                            </button>
                        </div>
                    </div>
                    <div x-show="open" x-transition  class="card-body">
                        <!-- FILTRO: Cliente -->
                        <h6 class="mb-3">Cliente</h6>
                        <div class="form-group">
                            <label for="cliente">Cliente:</label>
                            <select name="cliente" id="cliente" class="form-control">
                                <option value="">Selecionar</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->CompanyName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- FILTRO: Tipo de Fatura -->
                        <h6 class="mt-4 mb-3">Tipo de Fatura</h6>
                        <div class="form-group">
                            <label for="tipo">Tipo de Fatura:</label>
                            <select name="tipo" id="tipo" class="form-control">
                                <option value="">Selecionar</option>
                                <option value="FT">Fatura</option>
                                <option value="NC">Nota de Crédito</option>
                                <option value="RC">Recibo</option>
                            </select>
                        </div>

                        <!-- FILTRO: Método de Pagamento -->
                        <h6 class="mt-4 mb-3">Pagamento</h6>
                        <div class="form-group">
                            <label for="metodo_pagamento">Método de Pagamento:</label>
                            <select name="metodo_pagamento" id="metodo_pagamento" class="form-control">
                                <option value="">Selecionar</option>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="cartao">Cartão</option>
                                <option value="transferencia">Transferência Bancária</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTROS - COLUNA 3 -->
            <div class="col-md-12">
                <div class="card card-dark">
                    <div class="card-header">
                        <div class="card-title">
                            <span>Filtros Específicos</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- FILTRO: Intervalo de Valores -->
                        <h6 class="mb-3">Intervalo de Valores</h6>
                        <div class="form-group">
                            <label for="valor_min">Valor Mínimo:</label>
                            <input type="number" name="valor_min" id="valor_min" class="form-control" placeholder="Ex.: 100.00">
                        </div>
                        <div class="form-group">
                            <label for="valor_max">Valor Máximo:</label>
                            <input type="number" name="valor_max" id="valor_max" class="form-control" placeholder="Ex.: 1000.00">
                        </div>

                        <!-- FILTRO: Moeda -->
                        <h6 class="mt-4 mb-3">Moeda</h6>
                        <div class="form-group">
                            <label for="moeda">Moeda:</label>
                            <select name="moeda" id="moeda" class="form-control">
                                <option value="">Selecionar</option>
                                <option value="usd">USD</option>
                                <option value="eur">EUR</option>
                                <option value="aoa">AOA</option>
                            </select>
                        </div>

                        <!-- FILTRO: Produto/Serviço -->
                        <h6 class="mt-4 mb-3">Produto/Serviço</h6>
                        <div class="form-group">
                            <label for="produto">Produto/Serviço:</label>
                            <input type="text" name="produto" id="produto" class="form-control" placeholder="Ex.: Consultoria">
                        </div>

                        <!-- BOTÃO FILTRAR -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-dark w-100">Filtrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card card-dark">
                <div class="card-header">
                    <div class="card-title"> 
                        <span>Facturas</span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <th>Tipo</th>
                                <th>Factura</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acções</th>
                            </thead>
                            <tbody>
                                @forelse ($invoices as $key => $fatura )
                                    <tr>
                                        <td>
                                            <div id="doc-header-type">
                                                <div style="background: gray; border-radius: 50px;" class="inline-flex items-center px-3 py-2 border ">
                                                    {{$fatura->invoiceType->Code}}
                                                </div> 
                                            </div>
                                        </td>
                                        <td>{{$fatura->invoice_no}}</td>
                                        <td>{{$fatura->customer->CompanyName ?? ''}}</td>
                                        <td>{{$fatura->salesdoctotal->gross_total ?? '0.00'}}</td>
                                        <td>{{$fatura->salesdoctotal->}}</td>
                                        <td>
                                            <div class="inline-flex">
                                                <a href="{{ route('documentos.show', $fatura->id) }}" class="btn btn-sm "><i class="fas fa-eye"></i></a>
                                                <a href="{{ route('documento.print', $fatura->id) }}" class="btn btn-sm "><i class="fas fa-print"></i></a>
                                            </div>  
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($tableData['headers']) }}" class="text-center">
                                            Nenhuma fatura encontrada.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>

</x-app-layout>
