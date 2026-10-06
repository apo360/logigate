<section class="pauta-intro" aria-labelledby="pauta-title">
  <div class="container mx-auto px-4 lg:px-8">
    <nav class="breadcrumb" aria-label="Caminho da página"><a href="{{ route('home') }}">Início</a><span aria-hidden="true"> / </span><span>Pauta Aduaneira</span></nav>
    <h1 id="pauta-title">Consulte a <span class="gradient-text">Pauta Aduaneira</span>.</h1>
    <p>Pesquise mercadorias por descrição ou código pautal e consulte a informação disponível para a sua operação.</p>
    <div class="pauta-search-wrap">
      <form id="pauta-search-form" method="GET" action="{{ route('consultar.pauta') }}">
        <div class="pauta-search-field"><label for="searchInput">Descrição da mercadoria ou código pautal</label>
          <p id="pauta-search-help">Não precisa de conhecer o código para pesquisar por descrição. Uma descrição pode corresponder a vários códigos.</p>
          <div class="search-box"><input type="text" id="searchInput" name="q" class="search-input" value="{{ $filters['q'] ?? '' }}" minlength="2" maxlength="100" placeholder="Ex.: arroz, máquinas ou 0203.11.00" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="suggestions" aria-describedby="pauta-search-help"><button id="searchButton" class="search-button" type="submit">Pesquisar</button></div>
          <div id="suggestions" role="listbox" aria-label="Sugestões de mercadorias" hidden></div>
        </div>
        <div class="pauta-filters">
          <label for="pauta-search-type">Pesquisar por<select id="pauta-search-type" name="tipo"><option value="auto" @selected(($filters['tipo'] ?? 'auto') === 'auto')>Descrição ou código</option><option value="descricao" @selected(($filters['tipo'] ?? '') === 'descricao')>Descrição</option><option value="codigo" @selected(($filters['tipo'] ?? '') === 'codigo')>Código pautal</option></select></label>
          <label for="pauta-page-size">Resultados por página<select id="pauta-page-size" name="per_page">@foreach(array_unique([20,50,100,(int)($filters['per_page'] ?? 20)]) as $size)<option value="{{ $size }}" @selected(($filters['per_page'] ?? 20) == $size)>{{ $size }}</option>@endforeach</select></label>
          <a class="pauta-clear" id="pauta-clear" href="{{ route('consultar.pauta') }}">Limpar filtros</a>
        </div>
      </form>
      @if($errors->any())<p class="pauta-error" role="alert">{{ $errors->first() }}</p>@endif
    </div>
  </div>
</section>
<section class="pauta-results-section" aria-labelledby="pauta-results-title">
 <div class="container mx-auto px-4 lg:px-8">
  <h2 id="pauta-results-title">Mercadorias da pauta</h2>
  <p id="pauta-status" role="status" aria-live="polite" aria-atomic="true"></p>
  <div id="loading" hidden><span class="spinner" aria-hidden="true"></span><p>A carregar resultados…</p></div>
  <div id="results" class="glass-card">@include('WebSite.partials.pauta-results')</div>
  <nav id="pagination" aria-label="Paginação dos resultados">
   @if($listing['meta']['total'] > 0 && $listing['meta']['current_page'] > $listing['meta']['last_page'])<a data-pauta-page="1" href="{{ route('consultar.pauta', array_merge(\Illuminate\Support\Arr::only($filters, ['q','tipo','per_page']), ['page'=>1])) }}">Primeira página</a>@endif
   @if($listing['meta']['current_page'] > 1)<a data-pauta-page="{{ $listing['meta']['current_page'] - 1 }}" href="{{ route('consultar.pauta', array_merge(\Illuminate\Support\Arr::only($filters, ['q', 'tipo', 'per_page']), ['page' => $listing['meta']['current_page'] - 1])) }}">Anterior</a>@endif
   @if($listing['meta']['total'] > 0)<span>Página {{ $listing['meta']['current_page'] }} de {{ $listing['meta']['last_page'] }}</span>@endif
   @if($listing['meta']['current_page'] < $listing['meta']['last_page'])<a data-pauta-page="{{ $listing['meta']['current_page'] + 1 }}" href="{{ route('consultar.pauta', array_merge(\Illuminate\Support\Arr::only($filters, ['q', 'tipo', 'per_page']), ['page' => $listing['meta']['current_page'] + 1])) }}">Seguinte</a>@endif
  </nav>
  <aside class="pauta-support"><h2>Precisa de orientação?</h2><p>A classificação deve considerar as características concretas da mercadoria. A fonte normativa, a versão e a vigência não estão identificadas neste catálogo.</p><a href="{{ route('home') }}#contactos">Falar com a equipa LogiGate →</a><a href="{{ route('marketplace') }}">Explorar o marketplace →</a></aside>
 </div>
</section>
<script type="application/json" id="pauta-bootstrap">@json(['filters' => $filters, 'listing' => $listing, 'selection' => $selection])</script>
