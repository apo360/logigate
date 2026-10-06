<form method="GET" action="{{ route('marketplace') }}" class="mp-search">
    <label for="commodity-search">Que mercadoria pretende importar ou exportar?</label>
    <div class="mp-search-row"><input id="commodity-search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Por exemplo: arroz, veículos ou máquinas"><button class="mp-button" type="submit">Pesquisar na pauta</button></div>
</form>
@if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
@if($choices)
    <div class="mp-choices"><h3>Seleccione a mercadoria para encontrar despachantes</h3>
    @forelse($choices as $choice)
        <a href="{{ route('marketplace', ['pauta_id' => $choice->id, 'codigo' => (string) $choice->codigo]) }}">{{ $choice->codigo }} · {{ $choice->descricao }}</a>
    @empty<p>Não foram encontrados resultados na pauta. Tente outra descrição.</p>@endforelse
    {{ $choices->links('pagination::simple-default') }}</div>
@endif
@if($selection)
    <div class="mp-selected"><span>Mercadoria seleccionada</span><h3>{{ $selection->codigo }} · {{ $selection->descricao }}</h3>
    @php
        $returnContext = \Illuminate\Support\Arr::only($filters, ['months', 'location', 'recurrence']);
        foreach (['q', 'tipo', 'page', 'per_page'] as $key) { if (isset($filters['pauta_'.$key])) $returnContext[$key] = $filters['pauta_'.$key]; }
    @endphp
    <a class="mp-link" href="{{ route('consultar.pauta', array_merge($returnContext, ['codigo' => (string) $selection->codigo, 'pauta_id' => $selection->id, 'mercadoria_descricao' => $selection->descricao])) }}">Regressar à pauta com esta selecção →</a>
    <a class="mp-link" href="{{ route('marketplace') }}">Limpar selecção</a></div>
@endif
<details class="mp-filters"><summary>Filtros avançados</summary>
<form method="GET" action="{{ route('marketplace') }}">
    @foreach(\Illuminate\Support\Arr::only($filters, ['pauta_q','pauta_tipo','pauta_page','pauta_per_page']) as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
    @if($selection)<input type="hidden" name="pauta_id" value="{{ $selection->id }}">@endif
    <label>Código pautal<input name="codigo" value="{{ $selection->codigo ?? '' }}" maxlength="50" pattern="[0-9.]+" inputmode="decimal"></label>
    <label>Localização pública<input name="location" value="{{ $filters['location'] ?? '' }}" maxlength="255" placeholder="Localização exacta publicada"></label>
    <label>Período (meses completos)<input type="number" name="months" min="1" max="{{ config('marketplace.window_months') }}" value="{{ $filters['months'] ?? config('marketplace.window_months') }}"></label>
    <label>Meses mínimos com actividade<input type="number" name="recurrence" min="1" max="{{ config('marketplace.window_months') }}" value="{{ $filters['recurrence'] ?? '' }}"></label>
    <button type="submit" class="mp-button">Aplicar filtros</button>
</form></details>
<p class="mp-note">Histórico registado no LogiGate de {{ $directory['start']->format('d/m/Y') }} a {{ $directory['end']->copy()->subDay()->format('d/m/Y') }}. Não representa todo o percurso profissional nem avaliações de clientes.</p>
<div class="mp-profile-grid">
@forelse($directory['profiles'] as $profile)
    <article class="mp-profile"><h3>{{ $profile->public_name }}</h3><p>{{ $profile->public_location ?: 'Localização pública não indicada' }}</p>
    @if($profile->operations !== null)
        <p><strong>Experiência no código exacto seleccionado</strong></p>
        <p>Actividade em {{ $profile->active_months }} meses · {{ $profile->operations }} operações concluídas.</p>
        <details><summary>Como interpretar este histórico</summary><p>Processos distintos finalizados, contados pela data de fecho registada. Última operação: {{ \Carbon\Carbon::parse($profile->last_operation)->format('d/m/Y') }}. Linhas repetidas contam uma vez por processo e mercadoria.</p><p>Estes números não comprovam qualidade, habilitação profissional ou prazos futuros.</p></details>
    @elseif($selection)<p><strong>Especialidade declarada</strong> nesta mercadoria. Sem histórico público disponível para o período.</p>
    @else<p>Prestador aderente ao catálogo público. Seleccione uma mercadoria para consultar a correspondência.</p>@endif
    </article>
@empty
    <div class="mp-unavailable"><h3>{{ $directory['ready'] ? 'Sem perfis públicos para estes critérios.' : 'O catálogo ainda não está disponível nesta página.' }}</h3><p>Não há histórico público autorizado disponível para esta pesquisa. Isso não significa ausência de experiência profissional.</p><div class="mp-state-actions"><a class="mp-button" href="{{ route('marketplace') }}">Explorar o marketplace</a><a class="mp-link" href="{{ route('home') }}#contactos">Falar com a equipa LogiGate →</a></div></div>
@endforelse
</div>
{{ $directory['profiles']->links('pagination::simple-default') }}
