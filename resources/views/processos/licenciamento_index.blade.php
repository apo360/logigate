<x-app-layout>
    <div class="py-12">
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')]
    ]" separator="/" />

        <div class="card">
            <div class="card-header">
                <div class="float-left"></div>
                <div class="float-right">
                    <div class="btn-group">
                        <a href="{{ route('licenciamentos.create') }}" class="btn btn-sm btn-primary"> <i class="fas fa-plus-circle"></i> Licenciamento</a>
                        <a href="{{ route('licenciamentos.create') }}" class="btn btn-sm btn-default"> <i class="fas fa-download"></i> Impotação</a>
                        <a href="{{ route('licenciamentos.create') }}" class="btn btn-sm btn-default"> <i class="fas fa-upload"></i> Exportação</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <x-input type="text" id="search" placeholder="Pesquisar Licenciamento por: Referência, Cliente, Exportador" class="form-control" />
                    </div>
                    <div class="col-md-3">
                        <select name="status_factura" id="status_factura" class="form-control">
                            <option value="" selected>Estado</option>
                            <option value="">Facturas Emitidas C/ Licenciamento</option>
                            <option value="">Facturas Emitidas S/ Licenciamento</option>
                            <option value="">Facturas Pagas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="sort_by" class="form-control">
                            <option value="">Ordenar por...</option>
                            <option value="preco_asc">Preço Ascendente</option>
                            <option value="preco_desc">Preço Descendente</option>
                            <option value="maior">Maior Facturação</option>
                            <option value="menor">Menor Facturação</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header">
                    <div class="float-left">
                        <div class="btn-group">
                            <a href="" class="btn btn-sm btn-default"> <i class="fas fa-file-csv"></i> CSV</a>
                            <a href="" class="btn btn-sm btn-default"> <i class="fas fa-file-excel"></i> Excel</a>
                            <a href="" class="btn btn-sm btn-default"><i class="fas fa-file-pdf"></i> PDF</a>
                            <a href="" class="btn btn-sm btn-default"><i class="fas fa-print"></i> Imprimir</a>
                        </div>
                    </div>
                    <div class="float-right">
                        <span>Nº de Licenciamento: {{count($licenciamentos)}}</span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-stripped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Exportador</th>
                                <th>Descrição</th>
                                <th>Estado</th>
                                <th>Factura</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($licenciamentos as $licenciamento)
                            <tr>
                                <td>{{ $licenciamento->codigo_licenciamento }}</td>
                                <td>{{ $licenciamento->cliente->CompanyName }}</td>
                                <td>{{ $licenciamento->exportador->Exportador }}</td>
                                <td>{{ $licenciamento->descricao }}</td>
                                <td>{{ $licenciamento->estado_licenciamento }}</td>

                                <!-- Exibir status da fatura -->
                                @if($licenciamento->procLicenFaturas->isNotEmpty())
                                    @php
                                        $statusFatura = $licenciamento->procLicenFaturas->last()->status_fatura;
                                    @endphp
                                    <td>{{ ucfirst($statusFatura) }} <br>
                                        <span><a href="{{ route('documentos.show', $licenciamento->procLicenFaturas->last()->fatura_id) }}">{{$licenciamento->Nr_factura}}</a></span>
                                    </td>
                                @else
                                    <td>Sem Fatura</td>
                                @endif
                                <td>
                                    <a href="{{ route('licenciamentos.edit', $licenciamento->id) }}" class=""> <i class="fas fa-edit"></i> Editar</a>
                                    <!-- Mostrar botão para visualizar detalhes -->
                                    <a href="{{ route('licenciamentos.show', $licenciamento->id) }}" class=""> <i class="fas fa-eye"></i> Visualizar</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <!-- Paginação -->
                    {{ $licenciamentos->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
