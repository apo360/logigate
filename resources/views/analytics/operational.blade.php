@php
    $isProcess = $analytics['kind'] === 'processes';
    $routeName = $isProcess ? 'processos.estatistica' : 'licenciamento.estatistica';
    $labels = ['customer'=>'Cliente','type'=>'Tipo de declaração','transport'=>'Transporte','country'=>'País de origem','office'=>'Estância'];
    $format = fn($value, $decimals = 0) => $value === null ? 'Indisponível' : number_format((float)$value,$decimals,',','.');
    $percentKeys = ['cohort_rate']; $dayKeys = ['age','duration','license_age'];
    $chartCards = [];
    $timeline = $analytics['charts']['timeline'];
    $chartCards[] = ['title'=>$isProcess?'Quando entram e saem processos?':'Como evoluem as criações?', 'note'=>'Ano e mês; meses sem movimento a zero. * Intervalo mensal parcial.', 'type'=>'line', 'labels'=>array_map(fn($r)=>$r['month'].($r['partial']?' *':''),$timeline), 'datasets'=>array_merge([['label'=>$isProcess?'Aberturas':'Criações','data'=>array_column($timeline,'entries')]],$isProcess?[['label'=>'Finalizações','data'=>array_column($timeline,'closed')]]:[]),'unit'=>'registos'];
    foreach (['states'=>['Como se distribui a coorte por estado?','doughnut'],'types'=>['Quais os tipos de declaração?','bar'],'clients'=>['Quais os clientes com maior volume? · Top 10','horizontalBar'],'origins'=>['De onde vêm as mercadorias? · Maiores grupos','horizontalBar'],'transport'=>['Que transportes predominam? · Maiores grupos','bar'],'offices'=>['Que estâncias concentram actividade? · Maiores grupos','bar']] as $key=>$card) {
        $data=$analytics['charts'][$key];
        $chartCards[]=['title'=>$card[0],'note'=>'Registos abertos/criados no período. Grupos desconhecidos permanecem identificados.','type'=>$card[1],'labels'=>array_map(fn($r)=>$r->label,$data),'datasets'=>[['label'=>'Registos','data'=>array_map(fn($r)=>(int)$r->total,$data)]],'unit'=>'registos'];
    }
    foreach ($isProcess?['age'=>['Qual a idade da carteira à referência?','bar'],'destinations'=>['Quais os destinos? · Maiores grupos','horizontalBar']]:['ports'=>['Que portos de entrada predominam? · Top 10','horizontalBar'],'origin_ports'=>['Quais os portos de origem? · Top 10','horizontalBar'],'association'=>['Quantos têm processo associado?','doughnut']] as $key=>$card) {
        $data=$analytics['charts'][$key];
        $chartCards[]=['title'=>$card[0],'note'=>$key==='association'?'Indisponível se faltar a tabela canónica de vínculos. Associação observada agora.':'Contagens; antiguidade não é atraso nem SLA.','type'=>$card[1],'labels'=>array_map(fn($r)=>is_array($r)?$r['label']:$r->label,$data),'datasets'=>[['label'=>'Registos','data'=>array_map(fn($r)=>(int)(is_array($r)?$r['total']:$r->total),$data)]],'unit'=>'registos'];
    }
    if (!$isProcess) {
        $types=collect($analytics['charts']['type_states'])->pluck('label')->unique()->values()->all();
        $datasets=[];
        foreach(['Por licenciar','Em licenciamento','Licenciado'] as $state) $datasets[]=['label'=>$state,'data'=>array_map(fn($type)=>(int)(collect($analytics['charts']['type_states'])->first(fn($r)=>$r->label===$type&&$r->state===$state)->total??0),$types)];
        $chartCards[]=['title'=>'Como se distribuem os estados por tipo?','note'=>'Estado operacional actual da coorte, sem funil histórico.','type'=>'bar','stacked'=>true,'labels'=>$types,'datasets'=>$datasets,'unit'=>'registos'];
    }
    foreach($analytics['money'] as $money) {
        if($isProcess) {
            $series=array_map(function($r) use($money) { $row=collect($r['money'])->first(fn($v)=>$v->currency===$money->currency); return $row?($row->sample>0?(float)$row->total:null):0; },$timeline);
            $chartCards[]=['title'=>'Como evolui o valor declarado? · '.$money->currency,'note'=>'Mercadorias da coorte de abertura. Não é receita. Dado ausente não é zero.','type'=>'line','labels'=>array_column($timeline,'month'),'datasets'=>[['label'=>'Valor declarado','data'=>$series]],'unit'=>$money->currency];
        } else $chartCards[]=['title'=>'Qual a composição do valor? · '.$money->currency,'note'=>'FOB + frete + seguro; CIF disponível na tabela. Sem conversão monetária.','type'=>'bar','labels'=>['FOB','Frete','Seguro'],'datasets'=>[['label'=>$money->currency,'data'=>[$money->fob,$money->freight,$money->insurance]]],'unit'=>$money->currency];
    }
@endphp
<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6" data-operational-dashboard>
    <header class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-widest text-blue-700">LogiGate · Análise operacional</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-950">{{ $isProcess ? 'Processos' : 'Licenciamentos' }} — actividade e prioridades</h1>
        <p class="mt-2 text-sm text-slate-600">{{ $filters->start->format('d/m/Y') }} a {{ $filters->end->format('d/m/Y') }} · Referência {{ $filters->end->format('d/m/Y') }} · Angola</p>
        <p class="mt-1 text-xs text-slate-500">Dados actualizados em {{ $analytics['updated_at'] }} · Cache de 60 segundos · Estados observados nesta actualização.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach(['month'=>'Mês actual','quarter'=>'Trimestre actual','year'=>'Ano actual','last30'=>'Últimos 30 dias'] as $period=>$name)
                <a href="{{ route($routeName,array_merge($filters->cacheValues(),['period'=>$period])) }}" class="rounded-lg border px-3 py-2 text-sm {{ $filters->values['period']===$period?'border-blue-700 bg-blue-50 font-semibold text-blue-800':'border-slate-300 text-slate-700' }}">{{ $name }}</a>
            @endforeach
            <a class="rounded-lg border border-slate-300 px-3 py-2 text-sm" href="{{ route($routeName) }}">Limpar filtros</a>
            <a class="rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold text-white" href="{{ route($isProcess?'processos.index':'licenciamentos.index') }}">Ver listagem</a>
        </div>
        @if($errors->any())<div role="alert" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form method="GET" action="{{ route($routeName) }}" class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" data-analytics-filter>
            <label class="text-sm text-slate-700">Período<select name="period" class="mt-1 block w-full rounded-lg border-slate-300">@foreach(['month'=>'Mês actual','quarter'=>'Trimestre actual','year'=>'Ano actual','last30'=>'Últimos 30 dias','custom'=>'Intervalo personalizado'] as $value=>$label)<option value="{{ $value }}" @selected($filters->values['period']===$value)>{{ $label }}</option>@endforeach</select></label>
            <label class="text-sm text-slate-700">Início personalizado<input type="date" name="start" value="{{ $filters->start->toDateString() }}" class="mt-1 block w-full rounded-lg border-slate-300"></label>
            <label class="text-sm text-slate-700">Fim personalizado<input type="date" name="end" value="{{ $filters->reference() }}" max="{{ now('Africa/Luanda')->toDateString() }}" class="mt-1 block w-full rounded-lg border-slate-300"></label>
            @foreach($labels as $key=>$label)<label class="text-sm text-slate-700">{{ $label }}<select name="{{ $key }}" class="mt-1 block w-full rounded-lg border-slate-300"><option value="">Todos</option>@foreach($options[$key] as $option)<option value="{{ $option->value }}" @selected((string)($filters->values[$key]??'')===(string)$option->value)>{{ $option->label }}</option>@endforeach</select></label>@endforeach
            <button type="submit" class="min-h-11 rounded-lg bg-blue-700 px-4 font-semibold text-white sm:col-span-2 lg:col-span-4">Aplicar filtros</button>
        </form>
        <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-600" aria-label="Filtros activos">
            @foreach($labels as $key=>$label) @if(isset($filters->values[$key]))<span class="rounded bg-slate-100 px-2 py-1">{{ $label }}: {{ collect($options[$key])->first(fn($r)=>(string)$r->value===(string)$filters->values[$key])->label??$filters->values[$key] }}</span>@endif @endforeach
        </div>
    </header>
    <section aria-labelledby="executive-title">
        <h2 id="executive-title" class="mb-3 text-lg font-bold text-slate-900">1. Resumo executivo</h2>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($analytics['kpis'] as $key=>$value)
                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-600">{{ $analytics['definitions'][$key][0] }}</h3>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $format($value,in_array($key,array_merge($dayKeys,$percentKeys))?1:0) }}@if($value!==null && in_array($key,$percentKeys))<span class="text-base">%</span>@elseif($value!==null && in_array($key,$dayKeys))<span class="text-base"> dias</span>@endif</p>
                    <details class="mt-3 text-xs text-slate-500"><summary class="cursor-pointer">Definição e fórmula</summary><p class="mt-2 leading-5">{{ $analytics['definitions'][$key][1] }}</p></details>
                </article>
            @endforeach
        </div>
        <p class="mt-3 text-sm text-slate-600">Comparação das entradas com {{ $analytics['comparison']['start'] }} a {{ $analytics['comparison']['end'] }} (mesmo número de dias): {{ $analytics['comparison']['count'] }} registos · {{ $analytics['comparison']['change']===null?'sem base de comparação':$format($analytics['comparison']['change'],1).'% de variação' }}.</p>
        <p class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900">{{ $analytics['historical_warning'] }} Estados legados não são convertidos automaticamente. A carteira de Processos é independente da coorte de aberturas.</p>
    </section>
    <section aria-labelledby="evolution-title">
        <h2 id="evolution-title" class="mb-3 text-lg font-bold text-slate-900">2. Evolução e distribuição</h2>
        <p class="mb-3 text-xs text-slate-500">Os rankings mostram os maiores grupos, sem alterar os totais do resumo. Cada gráfico inclui uma tabela acessível.</p>
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach($chartCards as $index=>$chart)
                <article class="min-w-0 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="font-semibold text-slate-900" id="chart-title-{{ $index }}">{{ $chart['title'] }}</h3><p class="mt-1 text-xs text-slate-500">{{ $chart['note'] }}</p>
                    @if(count($chart['labels']))
                        <div class="relative mt-4 h-64"><canvas aria-labelledby="chart-title-{{ $index }}" role="img" data-operational-chart="{{ json_encode($chart,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) }}"></canvas></div>
                        <p data-chart-error hidden class="mt-3 text-sm text-amber-800">Gráfico indisponível. Consulte a tabela abaixo.</p>
                    @else<p class="mt-4 rounded-lg bg-slate-50 p-4 text-sm text-slate-500">Sem dados disponíveis para este indicador.</p>@endif
                    <details class="mt-4 text-sm"><summary class="cursor-pointer font-semibold text-blue-700">Ver dados em tabela</summary><div class="mt-2 overflow-x-auto"><table class="w-full text-left text-xs"><thead><tr><th scope="col" class="p-2">Grupo / período</th>@foreach($chart['datasets'] as $set)<th scope="col" class="p-2">{{ $set['label'] }} ({{ $chart['unit'] }})</th>@endforeach</tr></thead><tbody>@foreach($chart['labels'] as $i=>$label)<tr class="border-t border-slate-100"><th scope="row" class="p-2 font-normal">{{ $label }}</th>@foreach($chart['datasets'] as $set)<td class="p-2">{{ $format($set['data'][$i]??null,$chart['unit']==='registos'?0:2) }}</td>@endforeach</tr>@endforeach</tbody></table></div></details>
                </article>
            @endforeach
        </div>
    </section>
    <section class="rounded-xl border border-slate-200 bg-white p-5" aria-labelledby="priorities-title">
        <h2 id="priorities-title" class="text-lg font-bold text-slate-900">3. Pendências e prioridades</h2>
        <p class="mt-2 text-sm text-slate-600">Analise os registos mais antigos e os campos incompletos. Antiguidade não significa atraso: não existe SLA validado neste painel.</p>
        <div class="mt-3 flex flex-wrap gap-2">@foreach(['oldest'=>'Em aberto mais antigos','quality'=>'Qualidade dos dados'] + (!$isProcess?['unlinked'=>'Sem processo associado']:['conclusions'=>'Conclusões no período']) as $value=>$label)<a href="{{ route($routeName,array_merge($filters->values,['table'=>$value,'sort'=>'date','direction'=>'asc'])) }}#detail-title" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-blue-700">{{ $label }}</a>@endforeach</div>
        @if($isProcess)<p class="mt-3 text-xs text-slate-500">Cancelados da coorte: {{ $analytics['extra']['cancelled_cohort'] }}. Cancelamentos ocorridos no período: indisponível sem data validada do evento. Conclusões: n={{ $analytics['extra']['duration_sample'] }}, {{ $analytics['extra']['duration_excluded'] }} registos excluídos da média por datas inconsistentes. Carteira com datas válidas: n={{ $analytics['extra']['age_sample'] }}; {{ $analytics['extra']['age_excluded'] }} activos conhecidos excluídos da idade por abertura ausente/inválida.</p>
        @else<p class="mt-3 text-xs text-slate-500">Associação da coorte: {{ $format($analytics['extra']['link_rate'],1) }}{{ $analytics['extra']['link_rate']!==null?'%':'' }}. Idade em aberto: n={{ $analytics['extra']['age_sample'] }}. Tempo de aprovação/conclusão: indisponível sem data fiável de conclusão.</p>@if(!$analytics['extra']['link_available'])<p class="mt-2 text-sm text-amber-800">A tabela licenciamento_processos não existe neste ambiente. A associação está indisponível e não é apresentada como zero.</p>@endif @endif
    </section>
    <section class="rounded-xl border border-slate-200 bg-white p-5" aria-labelledby="detail-title">
        <h2 id="detail-title" class="text-lg font-bold text-slate-900">4. Análises detalhadas</h2>
        <p class="mt-2 text-sm text-slate-600">Peso bruto total: {{ $format($analytics['weight']['total'],2) }} kg · Média: {{ $format($analytics['weight']['mean'],2) }} kg · Cobertura: {{ $analytics['weight']['sample'] }}/{{ $analytics['count'] }} registos.</p>
        @if($analytics['volume'])<p class="mt-1 text-sm text-slate-600">Volumes: {{ $format($analytics['volume']['total']) }} unidades · Cobertura: {{ $analytics['volume']['sample'] }}/{{ $analytics['count'] }}.</p>@endif
        <h3 class="mt-5 font-semibold text-slate-900">{{ $isProcess?'Valor declarado':'FOB, frete, seguro e CIF' }} por moeda</h3><p class="mt-1 text-xs text-slate-500">Mercadorias, não receita do despachante. Sem somar ou converter moedas. Cada valor informa a amostra preenchida; campos ausentes não são zero.</p>
        <div class="mt-3 overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr><th scope="col" class="p-2">Moeda</th><th scope="col" class="p-2">Registos</th>@foreach($isProcess?['declared'=>'Declarado']:['fob'=>'FOB','freight'=>'Frete','insurance'=>'Seguro','cif'=>'CIF'] as $key=>$name)<th scope="col" class="p-2">{{ $name }} · amostra</th>@endforeach</tr></thead><tbody>@forelse($analytics['money'] as $money)<tr class="border-t border-slate-200"><th scope="row" class="p-2">{{ $money->currency }}</th><td class="p-2">{{ $money->records }}</td>@foreach($isProcess?['declared'=>'Declarado']:['fob'=>'FOB','freight'=>'Frete','insurance'=>'Seguro','cif'=>'CIF'] as $key=>$name)<td class="whitespace-nowrap p-2">{{ $format($money->$key,2) }} · n={{ $money->{$key.'_sample'} }}</td>@endforeach</tr>@empty<tr><td class="p-3" colspan="6">Sem valores no período.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-6 flex flex-wrap gap-2">@foreach(['oldest'=>'Antiguidade','clients'=>'Por cliente','quality'=>'Qualidade','country'=>'País de origem','transport'=>'Transporte','office'=>'Estância']+($isProcess?['conclusions'=>'Conclusões','destination'=>'Destino']:['unlinked'=>'Sem processo','port'=>'Porto']) as $value=>$label)<a href="{{ route($routeName,array_merge($filters->values,['table'=>$value,'sort'=>in_array($value,['clients','country','transport','office','port','destination'])?'count':'date','direction'=>in_array($value,['clients','country','transport','office','port','destination'])?'desc':'asc'])) }}#detail-title" class="rounded-lg border px-3 py-2 text-sm {{ $table===$value?'border-blue-700 bg-blue-50 font-semibold text-blue-800':'border-slate-300 text-slate-700' }}">{{ $label }}</a>@endforeach</div>
        <p class="mt-3 text-xs text-slate-500">{{ $table==='oldest' && $isProcess ? 'Carteira à referência, independentemente da abertura no período.' : ($table==='clients'&&$isProcess?'Entradas, saídas e carteira por cliente; populações independentes.':'Coorte filtrada; qualidade inclui aberturas ausentes identificadas pela criação no período.') }}</p>
        <div class="mt-3 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50"><tr>
            @foreach($groupTable?($table==='clients'?['name'=>'Cliente','count'=>'Entradas','completed'=>'Finalizados / licenciados','active'=>'Carteira / em aberto','duration'=>'Tempo médio · n']:['name'=>'Grupo','count'=>'Registos']):['number'=>'Número','customer'=>'Cliente','state'=>'Estado','date'=>'Abertura / criação','age'=>'Idade (dias)','duration'=>'Conclusão (dias)','issue'=>'Qualidade'] as $key=>$label)
                <th scope="col" class="whitespace-nowrap p-3">@if(in_array($key,['number','date','age','name','count']))<a class="text-blue-700" href="{{ route($routeName,array_merge($filters->values,['sort'=>$key,'direction'=>($filters->values['direction']??'asc')==='asc'?'desc':'asc'])) }}#detail-title">{{ $label }} ↕</a>@else{{ $label }}@endif</th>
            @endforeach
        </tr></thead><tbody>@forelse($rows as $row)<tr class="border-t border-slate-100">
            @if($groupTable)<th scope="row" class="p-3 font-medium">{{ $row->name }}</th><td class="p-3">{{ $row->entries }}</td>@if($table==='clients')<td class="p-3">{{ $row->completed }}</td><td class="p-3">{{ $row->active }}</td><td class="p-3">{{ $format($row->duration,1) }} · n={{ $row->sample }}</td>@endif
            @else<th scope="row" class="whitespace-nowrap p-3 font-medium"><a class="text-blue-700 underline" href="{{ route($isProcess?'processos.show':'licenciamentos.show',$row->id) }}">{{ $row->number }}</a></th><td class="p-3">{{ $row->customer }}</td><td class="p-3">{{ $row->state }}</td><td class="whitespace-nowrap p-3">{{ $row->date??'Ausente' }}</td><td class="p-3">{{ $format($row->age) }}</td><td class="p-3">{{ $format($row->duration,1) }}</td><td class="min-w-48 p-3 text-xs text-slate-500">{{ $row->issue ?: 'Sem inconsistências detectadas nos critérios avaliados' }}</td>@endif
        </tr>@empty<tr><td colspan="7" class="p-5 text-slate-500">Sem registos disponíveis com estes filtros.</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $rows->links() }}</div>
    </section>
    <section class="rounded-xl border border-slate-200 bg-white p-5" aria-labelledby="reports-title">
        <h2 id="reports-title" class="text-lg font-bold text-slate-900">5. Relatórios</h2><p class="mt-2 text-sm text-slate-600">Mesma empresa, período e filtros do painel. Incluem definições e cobertura; PDF limitado a 500 linhas, CSV e Excel com detalhe integral.</p>
        <div class="mt-4 grid gap-3 md:grid-cols-2">@foreach($reportTypes as $key=>$name)<article class="rounded-lg border border-slate-200 p-3"><h3 class="text-sm font-semibold text-slate-800">{{ $name }}</h3><div class="mt-2 flex gap-4">@foreach(['csv'=>'CSV','xlsx'=>'Excel','pdf'=>'PDF'] as $extension=>$label)<a class="text-sm font-semibold text-blue-700 underline" href="{{ route($isProcess?'processos.analytics-report':'licenciamentos.analytics-report',array_merge(['report'=>$key,'format'=>$extension],$filters->cacheValues())) }}">{{ $label }}</a>@endforeach</div></article>@endforeach</div>
    </section>
</div>
@push('scripts')<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>@endpush
