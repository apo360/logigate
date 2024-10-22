<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')],
        ['name' => 'Visualizar Licenciamento', 'url' => route('licenciamentos.show', $licenciamento->id)]
    ]" separator="/" />

    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    Detalhes do Licenciamento
                </div>
                <div class="card-body">
                    <p><strong>FOB Total:</strong> {{ $licenciamento->fob_total }}</p>
                    <p><strong>Seguro:</strong> {{ $licenciamento->seguro }}</p>
                    <p><strong>Frete:</strong> {{ $licenciamento->frete }}</p>
                    <p><strong>CIF Total:</strong> {{ $licenciamento->cif }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-navy">
                <div class="card-header">
                    <div class="card-title">
                        <span>Info</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($licenciamento->procLicenFaturas->isNotEmpty())
                        @php
                            $statusFatura = $licenciamento->procLicenFaturas->last()->status_fatura;
                        @endphp
                        {{__('Documentos Relacionados') }}
                        <hr>
                        <span><a href="{{ route('documentos.show', $licenciamento->procLicenFaturas->last()->fatura_id) }}">{{$licenciamento->Nr_factura}}</a></span> 
                        <!-- Encontar um formar de buscar e listar todas as facturas relacionadas com a factura original -->
                    @else
                        <span>Sem Fatura</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    

    <div class="card">
        <h2 class="mt-4">Mercadorias</h2>
        @if($licenciamento->mercadorias->count() > 0)
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Descrição</th>
                        <th>Quantidade</th>
                        <th>Valor Unitário</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($licenciamento->mercadorias as $mercadoria)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $mercadoria->Descricao }}</td>
                            <td>{{ $mercadoria->Quantidade }}</td>
                            <td>{{ $mercadoria->preco_unitario }}</td>
                            <td>{{ $mercadoria->preco_total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Não há mercadorias associadas a este licenciamento.</p>
        @endif
    </div>

    <a href="{{ route('licenciamentos.edit', $licenciamento->id) }}" class="btn btn-primary mt-3">Editar Licenciamento</a>
    <a href="{{ route('mercadorias.create', ['licenciamento_id' => $licenciamento->id]) }}" class="btn btn-default mt-3">Inserir Mercadoria</a>
    
    <a href="{{ route('documentos.create', ['licenciamento_id' => $licenciamento->id]) }}" class="btn btn-success">
                                <i class="fas fa-file-invoice"></i> {{ __('Emitir Factura') }}
                            </a>

    <!-- Ao clicar quero verificar se os campos frete e seguro estão vazio. Caso estejam deve me alertar por mensagem para preencher -->
    <a href="{{ route('gerar.txt', ['IdProcesso' => $licenciamento->id]) }}" class="btn btn-default">
                                <i class="fas fa-file-download"></i> {{ __('Licenciamento (txt)') }}
                            </a>
</x-app-layout>