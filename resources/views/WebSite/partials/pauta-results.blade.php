@if(!$listing['success'])
    <h2>Não foi possível consultar os resultados.</h2><p>Corrija os campos indicados ou tente novamente.</p>
@elseif($listing['meta']['catalogue_empty'])
    <h2>O catálogo pautal ainda não tem dados disponíveis.</h2><p>A consulta não permite concluir que uma mercadoria está isenta ou sem requisitos.</p>
@elseif($listing['meta']['total'] === 0)
    <h2>Não encontrámos mercadorias para esta pesquisa.</h2><p>Ajuste a descrição ou o código, ou limpe os filtros para consultar o catálogo.</p>
@elseif(count($listing['data']) === 0)
    <h2>Esta página já não tem resultados.</h2><p>Regresse à primeira página ou ajuste a pesquisa.</p>
@else
    <p class="pauta-count">{{ count($listing['data']) }} mercadorias nesta página · {{ $listing['meta']['total'] }} resultados no total.</p>
    <div class="pauta-result-list">
    @foreach($listing['data'] as $item)
        <article class="result-card">
            <h3><a data-pauta-select data-id="{{ $item['id'] }}" data-code="{{ $item['codigo'] }}" href="{{ route('consultar.pauta', array_merge(\Illuminate\Support\Arr::only($filters, ['q', 'tipo', 'page', 'per_page', 'months', 'location', 'recurrence']), ['pauta_id' => $item['id'], 'codigo' => $item['codigo']])) }}">{{ $item['codigo'] }}</a></h3>
            <p>{{ $item['descricao'] }}</p>
            <p class="pauta-result-rate">IVA: {{ $item['impostos']['iva'] === null ? 'Não indicado na fonte' : (is_numeric($item['impostos']['iva']) ? $item['impostos']['iva'].'%' : $item['impostos']['iva']) }}</p>
            <a data-pauta-select data-id="{{ $item['id'] }}" data-code="{{ $item['codigo'] }}" class="pauta-detail-link" href="{{ route('consultar.pauta', array_merge(\Illuminate\Support\Arr::only($filters, ['q', 'tipo', 'page', 'per_page', 'months', 'location', 'recurrence']), ['pauta_id' => $item['id'], 'codigo' => $item['codigo']])) }}">Consultar detalhes de {{ $item['codigo'] }} →</a>
        </article>
    @endforeach
    </div>
@endif
