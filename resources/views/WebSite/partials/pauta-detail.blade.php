@if($selection)
<div class="pauta-detail-body">
    <section class="pauta-description"><h3>Descrição</h3><p>{{ $selection['descricao'] }}</p></section>
    <dl class="pauta-rates">
    @foreach(['Regime geral' => $selection['regime_geral'], 'SADC' => $selection['sadc'], 'UA' => $selection['ua'], 'IVA' => $selection['impostos']['iva'], 'IEQ' => $selection['impostos']['ieq']] as $label => $value)
        <div><dt>{{ $label }}</dt><dd>{{ $value === null ? 'Não indicado na fonte' : (is_numeric($value) ? $value.'%' : $value) }}</dd></div>
    @endforeach
    </dl>
    @foreach(['Unidade' => $selection['unidade'], 'Requisitos registados' => $selection['requisitos'], 'Observações' => $selection['observacao']] as $label => $value)
        @if($value !== null && $value !== '')<section><h3>{{ $label }}</h3><p>{{ $value }}</p></section>@endif
    @endforeach
    <section class="pauta-source"><h3>Sobre esta informação</h3><p>{{ $selection['fonte']['designacao'] }}</p><p>{{ $selection['fonte']['limitacao'] }}</p>
    @if($selection['fonte']['atualizacao_registo'])<p>Actualização técnica do registo: {{ $selection['fonte']['atualizacao_registo'] }}. Não é uma data de vigência.</p>@endif
    <p>A pesquisa não valida oficialmente a classificação da mercadoria. Confirme o enquadramento aplicável à sua operação.</p></section>
</div>
@endif
