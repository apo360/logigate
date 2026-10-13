<?php

namespace App\Services\Dashboard;

use App\Application\Licenciamento\Services\LicenciamentoProcessLink;
use App\Domains\Licenciamento\Services\EstadoLicenciamentoService;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class OperationalAnalytics
{
    private const ACTIVE = ['Aberto', 'Em analise', 'Aguardando documentos', 'Em despacho'];

    private const DEFINITIONS = [
        'opened' => ['Abertos no período', 'DataAbertura no intervalo seleccionado.'],
        'closed' => ['Finalizados no período', 'Estado Finalizado e DataFecho no intervalo; população de saída independente das aberturas.'],
        'portfolio' => ['Carteira à referência', 'Estados activos reconhecidos com abertura válida até à referência; inclui finalizados com fecho posterior. Cancelamentos sem data histórica impedem reconstrução integral.'],
        'awaiting' => ['A aguardar documentos', 'Carteira à referência cujo estado actual é Aguardando documentos.'],
        'age' => ['Idade média da carteira', 'Média de referência − DataAbertura, em dias, na carteira com datas válidas.'],
        'duration' => ['Tempo médio de conclusão', 'Média de DataFecho − DataAbertura, em dias, nos finalizados no intervalo com fecho ≥ abertura.'],
        'customers' => ['Clientes da coorte', 'Clientes distintos e identificáveis entre registos abertos/criados no intervalo.'],
        'cohort_rate' => ['Finalização da coorte', 'Finalizados com datas válidas até à referência ÷ processos abertos no intervalo.'],
        'created' => ['Criados no período', 'created_at no intervalo civil de Angola; não é data de entrada da mercadoria.'],
        'pending' => ['Por licenciar', 'txt_gerado = 0 na coorte criada no intervalo.'],
        'licensing' => ['Em licenciamento', 'TXT gerado, sem relação paga em proc_licen_sales da mesma empresa.'],
        'licensed' => ['Licenciados', 'TXT gerado e relação paga em proc_licen_sales da mesma empresa, segundo o serviço operacional.'],
        'linked' => ['Associados a processos', 'Coorte com vínculo licenciamento_processos e processo existente da mesma empresa.'],
        'unlinked' => ['Sem processo associado', 'Coorte sem vínculo válido na tabela canónica; indisponível quando a tabela não existe.'],
        'link_rate' => ['Associação da coorte', 'Associados ÷ criados no intervalo; associação observada na actualização, não conversão histórica.'],
        'license_age' => ['Idade média em aberto', 'Referência − data de criação local; estados Por licenciar/Em licenciamento da coorte.'],
    ];

    public function base(string $kind, int $company, OperationalFilters $filters): Builder
    {
        $process = $kind === 'processes';
        $q = DB::table($process ? 'processos as r' : 'licenciamentos as r')->where('r.empresa_id', $company);
        if ($process) {
            $q->whereNull('r.deleted_at');
        }
        foreach (['customer' => $process ? 'customer_id' : 'cliente_id', 'type' => $process ? 'TipoProcesso' : 'tipo_declaracao', 'transport' => $process ? 'TipoTransporte' : 'tipo_transporte', 'country' => $process ? 'Pais_origem' : 'pais_origem', 'office' => 'estancia_id'] as $key => $column) {
            if (isset($filters->values[$key])) {
                $q->where('r.'.$column, $filters->values[$key]);
            }
        }

        return $q;
    }

    private function date(string $kind): string
    {
        return $kind === 'processes' ? 'r.DataAbertura' : "DATE(CONVERT_TZ(r.created_at, '+00:00', '+01:00'))";
    }

    private function state(): string
    {
        return app(EstadoLicenciamentoService::class)->analyticsExpression('r');
    }

    private function link(): string
    {
        return app(LicenciamentoProcessLink::class)->analyticsExpression('r');
    }

    private function validDate(string $date): string
    {
        return "$date IS NOT NULL AND $date >= '1000-01-01'";
    }

    public function cohort(string $kind, int $company, OperationalFilters $f): Builder
    {
        $q = $this->base($kind, $company, $f);
        if ($kind === 'processes') {
            return $q->whereBetween('r.DataAbertura', [$f->start->toDateString(), $f->reference()]);
        }
        [$start, $end] = $f->utcBounds();

        return $q->where('r.created_at', '>=', $start)->where('r.created_at', '<', $end);
    }

    private function closed(int $company, OperationalFilters $f): Builder
    {
        return $this->base('processes', $company, $f)->where('r.Estado', EstadoProcessoEnum::FINALIZADO->value)->whereBetween('r.DataFecho', [$f->start->toDateString(), $f->reference()]);
    }

    public function portfolio(int $company, OperationalFilters $f): Builder
    {
        return $this->base('processes', $company, $f)->whereRaw($this->validDate('r.DataAbertura'))->where('r.DataAbertura', '<=', $f->reference())
            ->where(fn ($q) => $q->whereIn('r.Estado', self::ACTIVE)->orWhere(fn ($closed) => $closed->where('r.Estado', 'Finalizado')->where('r.DataFecho', '>', $f->reference())))
            ->where(fn ($q) => $q->whereNull('r.DataFecho')->orWhere('r.DataFecho', '>', $f->reference()));
    }

    private function customerJoin(Builder $q, string $kind, int $company): Builder
    {
        // Shared customers may belong through the pivot; corrupt cross-company references expose no names.
        return $q->leftJoin('customers as c', function ($join) use ($kind, $company) {
            $join->on('c.id', '=', 'r.'.($kind === 'processes' ? 'customer_id' : 'cliente_id'))->whereNull('c.deleted_at')
                ->where(function ($tenant) use ($company) {
                    $tenant->where('c.empresa_id', $company)->orWhereExists(fn ($pivot) => $pivot->selectRaw('1')->from('customers_empresas as ce')->whereColumn('ce.customer_id', 'c.id')->where('ce.empresa_id', $company));
                });
        });
    }

    public function snapshot(string $kind, int $company, OperationalFilters $f): array
    {
        $key = 'analytics:v1:'.$kind.':'.$company.':'.hash('sha256', json_encode($f->cacheValues()));

        return Cache::remember($key, 60, function () use ($kind, $company, $f) {
            $cohort = $this->cohort($kind, $company, $f);
            $count = $cohort->count();
            $customerCount = $this->customerJoin(clone $cohort, $kind, $company)->distinct()->count('c.id');
            $kpis = [];
            $extra = [];
            $charts = [];
            if ($kind === 'processes') {
                $closed = $this->closed($company, $f);
                $portfolio = $this->portfolio($company, $f);
                $duration = (clone $closed)->whereRaw($this->validDate('r.DataAbertura'))->whereColumn('r.DataFecho', '>=', 'r.DataAbertura')->selectRaw('AVG(DATEDIFF(r.DataFecho,r.DataAbertura)) AS mean, COUNT(*) AS sample')->first();
                $age = (clone $portfolio)->selectRaw('AVG(DATEDIFF(?,r.DataAbertura)) AS mean, COUNT(*) AS sample', [$f->reference()])->first();
                $completed = (clone $cohort)->where('r.Estado', 'Finalizado')->whereRaw($this->validDate('r.DataFecho'))->whereColumn('r.DataFecho', '>=', 'r.DataAbertura')->where('r.DataFecho', '<=', $f->reference())->count();
                $kpis = ['opened' => $count, 'closed' => $closed->count(), 'portfolio' => $portfolio->count(), 'awaiting' => (clone $portfolio)->where('r.Estado', 'Aguardando documentos')->count(), 'age' => $age->mean, 'duration' => $duration->mean, 'customers' => $customerCount, 'cohort_rate' => $count ? 100 * $completed / $count : null];
                $ageExcluded = $this->base($kind, $company, $f)->whereIn('r.Estado', self::ACTIVE)->where('r.created_at', '<', $f->utcBounds()[1])->whereRaw('NOT ('.$this->validDate('r.DataAbertura').')')->count();
                $extra = ['duration_sample' => (int) $duration->sample, 'duration_excluded' => $kpis['closed'] - (int) $duration->sample, 'age_sample' => (int) $age->sample, 'age_excluded' => $ageExcluded, 'cohort_completed' => $completed, 'cancelled_cohort' => (clone $cohort)->where('r.Estado', 'Cancelado')->count(), 'cancelled_period' => null];
                $charts['states'] = $this->distribution(clone $cohort, 'r.Estado');
                $charts['age'] = (clone $portfolio)->selectRaw("CASE WHEN DATEDIFF(?,r.DataAbertura) <= 7 THEN '01 · 0–7 dias' WHEN DATEDIFF(?,r.DataAbertura) <= 30 THEN '02 · 8–30 dias' WHEN DATEDIFF(?,r.DataAbertura) <= 60 THEN '03 · 31–60 dias' WHEN DATEDIFF(?,r.DataAbertura) <= 90 THEN '04 · 61–90 dias' ELSE '05 · Mais de 90 dias' END AS label, COUNT(*) AS total", array_fill(0, 4, $f->reference()))->groupBy('label')->orderBy('label')->get()->toArray();
                $charts['destinations'] = $this->lookupDistribution(clone $cohort, 'paises', 'Pais_destino', 'pais');
            } else {
                $states = $this->distribution(clone $cohort, $this->state());
                $totals = collect($states)->pluck('total', 'label');
                $available = Schema::hasTable('licenciamento_processos');
                $linked = $available ? (clone $cohort)->whereRaw($this->link().' = 1')->count() : null;
                $age = (clone $cohort)->whereRaw($this->state()." <> 'Licenciado'")->selectRaw('AVG(DATEDIFF(?,'.$this->date($kind).')) AS mean, COUNT(*) AS sample', [$f->reference()])->first();
                $kpis = ['created' => $count, 'pending' => (int) ($totals['Por licenciar'] ?? 0), 'licensing' => (int) ($totals['Em licenciamento'] ?? 0), 'licensed' => (int) ($totals['Licenciado'] ?? 0), 'linked' => $linked, 'unlinked' => $available ? $count - $linked : null, 'customers' => $customerCount, 'license_age' => $age->mean];
                $extra = ['link_rate' => $count && $available ? 100 * $linked / $count : null, 'age_sample' => (int) $age->sample, 'approval_duration' => null, 'link_available' => $available];
                $charts['states'] = $states;
                $charts['association'] = $available ? [['label' => 'Associados', 'total' => $linked], ['label' => 'Sem processo', 'total' => $count - $linked]] : [];
                $charts['ports'] = $this->distribution(clone $cohort, 'r.porto_entrada', 10);
                $charts['origin_ports'] = $this->distribution(clone $cohort, 'r.porto_origem', 10);
                $charts['type_states'] = (clone $cohort)->selectRaw("CONCAT('Tipo ',r.tipo_declaracao) AS label, ".$this->state().' AS state, COUNT(*) AS total')->groupBy('r.tipo_declaracao', 'state')->orderBy('r.tipo_declaracao')->get()->toArray();
            }
            $charts['types'] = $kind === 'processes' ? $this->lookupDistribution(clone $cohort, 'regiao_aduaneiras', 'TipoProcesso', 'descricao') : $this->licenseTypes(clone $cohort);
            $charts['clients'] = $this->customerJoin(clone $cohort, $kind, $company)->selectRaw("COALESCE(c.CompanyName,'Cliente indisponível') AS label, COUNT(*) AS total")->groupBy('c.id', 'c.CompanyName')->orderByDesc('total')->limit(10)->get()->toArray();
            $charts['origins'] = $this->lookupDistribution(clone $cohort, 'paises', $kind === 'processes' ? 'Pais_origem' : 'pais_origem', 'pais');
            $charts['transport'] = $kind === 'processes' ? $this->lookupDistribution(clone $cohort, 'tipo_transportes', 'TipoTransporte', 'descricao') : $this->distribution(clone $cohort, $this->licenseTransport());
            $charts['offices'] = $this->lookupDistribution(clone $cohort, 'estancias', 'estancia_id', 'desc_estancia');
            $charts['timeline'] = $this->timeline($kind, $company, $f);
            $money = $this->money(clone $cohort, $kind);
            $weight = (clone $cohort)->selectRaw('SUM(CASE WHEN peso_bruto >= 0 THEN peso_bruto END) AS total, AVG(CASE WHEN peso_bruto >= 0 THEN peso_bruto END) AS mean, COUNT(CASE WHEN peso_bruto >= 0 THEN 1 END) AS sample')->first();
            $volume = $kind === 'licenses' ? (clone $cohort)->selectRaw('SUM(CASE WHEN qntd_volume >= 0 THEN qntd_volume END) AS total, COUNT(CASE WHEN qntd_volume >= 0 THEN 1 END) AS sample')->first() : null;
            $previous = $f->previous();
            $previousCount = $this->cohort($kind, $company, $previous)->count();

            return ['kind' => $kind, 'count' => $count, 'kpis' => $kpis, 'extra' => $extra, 'charts' => $charts, 'money' => $money, 'weight' => (array) $weight, 'volume' => $volume ? (array) $volume : null, 'definitions' => self::DEFINITIONS,
                'reference' => $f->reference(), 'updated_at' => CarbonImmutable::now('Africa/Luanda')->format('Y-m-d H:i:s'), 'comparison' => ['start' => $previous->start->toDateString(), 'end' => $previous->reference(), 'count' => $previousCount, 'change' => $previousCount ? 100 * ($count - $previousCount) / $previousCount : null],
                'historical_warning' => 'Estados observados na actualização. Sem histórico validado de transições não se reconstitui integralmente a carteira passada, não se calcula data de cancelamento nem duração de aprovação.',
            ];
        });
    }

    private function licenseTypes(Builder $q): array
    {
        return $q->selectRaw("CASE r.tipo_declaracao WHEN 11 THEN 'Importação (11)' WHEN 21 THEN 'Exportação (21)' ELSE CONCAT('Tipo registado ',r.tipo_declaracao) END AS label, COUNT(*) AS total")->groupBy('r.tipo_declaracao')->orderByDesc('total')->get()->toArray();
    }

    private function licenseTransport(): string
    {
        return "CASE r.tipo_transporte WHEN 1 THEN 'Marítimo' WHEN 2 THEN 'Ferroviário' WHEN 3 THEN 'Rodoviário' WHEN 4 THEN 'Aéreo' WHEN 5 THEN 'Correio' WHEN 6 THEN 'Multimodal' WHEN 7 THEN 'Instalação Fixa' WHEN 8 THEN 'Fluvial' ELSE CONCAT('Código registado ',r.tipo_transporte) END";
    }

    private function distribution(Builder $q, string $expression, int $limit = 20): array
    {
        return $q->selectRaw("COALESCE(NULLIF($expression,''),'Não indicado') AS label, COUNT(*) AS total")->groupBy('label')->orderByDesc('total')->limit($limit)->get()->toArray();
    }

    private function lookupDistribution(Builder $q, string $table, string $foreign, string $label): array
    {
        return $q->leftJoin($table.' as dimension', 'dimension.id', '=', 'r.'.$foreign)->selectRaw("COALESCE(dimension.$label,CONCAT('Não identificado · ',COALESCE(r.$foreign,'—'))) AS label, COUNT(*) AS total")->groupBy('dimension.id', 'dimension.'.$label, 'r.'.$foreign)->orderByDesc('total')->limit(20)->get()->toArray();
    }

    private function money(Builder $q, string $kind): array
    {
        $currency = $kind === 'processes' ? 'Moeda' : 'moeda';
        $columns = $kind === 'processes' ? ['declared' => 'ValorTotal'] : ['fob' => 'fob_total', 'freight' => 'frete', 'insurance' => 'seguro', 'cif' => 'cif'];
        $q->selectRaw("COALESCE(NULLIF(UPPER(TRIM(r.$currency)),''),'Não indicada') AS currency, COUNT(*) AS records");
        foreach ($columns as $key => $column) {
            $q->selectRaw("SUM(CASE WHEN NULLIF(TRIM(r.$currency),'') IS NOT NULL AND r.$column >= 0 THEN r.$column END) AS $key, COUNT(CASE WHEN NULLIF(TRIM(r.$currency),'') IS NOT NULL AND r.$column >= 0 THEN 1 END) AS {$key}_sample");
        }

        return $q->groupBy('currency')->orderBy('currency')->get()->toArray();
    }

    private function timeline(string $kind, int $company, OperationalFilters $f): array
    {
        $entries = $this->cohort($kind, $company, $f)->selectRaw('DATE_FORMAT('.$this->date($kind).",'%Y-%m') AS month, COUNT(*) AS total")->groupBy('month')->pluck('total', 'month');
        $closed = $kind === 'processes' ? $this->closed($company, $f)->selectRaw("DATE_FORMAT(r.DataFecho,'%Y-%m') AS month, COUNT(*) AS total")->groupBy('month')->pluck('total', 'month') : collect();
        $money = $kind === 'processes' ? $this->cohort($kind, $company, $f)->selectRaw("DATE_FORMAT(r.DataAbertura,'%Y-%m') AS month, COALESCE(NULLIF(UPPER(TRIM(r.Moeda)),''),'Não indicada') AS currency, SUM(CASE WHEN NULLIF(TRIM(r.Moeda),'') IS NOT NULL AND r.ValorTotal >= 0 THEN r.ValorTotal END) AS total, COUNT(CASE WHEN NULLIF(TRIM(r.Moeda),'') IS NOT NULL AND r.ValorTotal >= 0 THEN 1 END) AS sample")->groupBy('month', 'currency')->get()->groupBy('month') : collect();
        $result = [];
        for ($month = $f->start->startOfMonth(); $month->lte($f->end); $month = $month->addMonth()) {
            $key = $month->format('Y-m');
            $result[] = ['month' => $key, 'entries' => (int) ($entries[$key] ?? 0), 'closed' => (int) ($closed[$key] ?? 0), 'partial' => $f->start->gt($month) || $f->end->lt($month->endOfMonth()->startOfDay()), 'money' => ($money[$key] ?? collect())->toArray()];
        }

        return $result;
    }

    public function options(string $kind, int $company, OperationalFilters $f): array
    {
        $unfiltered = new OperationalFilters([], $f->start, $f->end);
        $base = $this->base($kind, $company, $unfiltered);
        $customers = $this->customerJoin(clone $base, $kind, $company)->whereNotNull('c.id')->select('c.id as value', 'c.CompanyName as label')->distinct()->orderBy('label')->get();
        $result = ['customer' => $customers];
        foreach (['type' => $kind === 'processes' ? 'TipoProcesso' : 'tipo_declaracao', 'transport' => $kind === 'processes' ? 'TipoTransporte' : 'tipo_transporte', 'country' => $kind === 'processes' ? 'Pais_origem' : 'pais_origem', 'office' => 'estancia_id'] as $key => $column) {
            $q = (clone $base)->whereNotNull('r.'.$column);
            [$table,$name] = match ($key) {
                'type' => ['regiao_aduaneiras', 'descricao'],'transport' => ['tipo_transportes', 'descricao'],'country' => ['paises', 'pais'],'office' => ['estancias', 'desc_estancia']
            };
            if ($kind === 'licenses' && $key === 'transport') {
                $q->selectRaw('r.tipo_transporte AS value, '.$this->licenseTransport().' AS label');
            } elseif ($kind === 'licenses' && $key === 'type') {
                $q->selectRaw("r.$column AS value, ".($key === 'type' ? "CASE r.tipo_declaracao WHEN 11 THEN 'Importação (11)' WHEN 21 THEN 'Exportação (21)' ELSE CONCAT('Tipo ',r.tipo_declaracao) END" : "r.$column").' AS label');
            } else {
                $q->leftJoin($table.' as d', 'd.id', '=', 'r.'.$column)->selectRaw("r.$column AS value, COALESCE(d.$name,CONCAT('ID ',r.$column)) AS label");
            }
            $result[$key] = $q->distinct()->orderBy('label')->get();
        }

        return $result;
    }

    public function rows(string $kind, int $company, OperationalFilters $f, string $table): Builder
    {
        if ($table === 'clients') {
            return $this->clientPerformance($kind, $company, $f);
        }
        if (in_array($table, ['country', 'destination', 'transport', 'office', 'port'])) {
            return $this->dimensionRows($kind, $company, $f, $table);
        }
        $q = match ($table) {
            'oldest' => $kind === 'processes' ? $this->portfolio($company, $f) : $this->cohort($kind, $company, $f)->whereRaw($this->state()." <> 'Licenciado'"), 'conclusions' => $kind === 'processes' ? $this->closed($company, $f) : $this->cohort($kind, $company, $f), default => $this->cohort($kind, $company, $f)
        };
        if ($table === 'unlinked') {
            $q->whereRaw($kind === 'licenses' ? $this->link().' = 0' : '1=0');
        }
        if ($table === 'quality') {
            // Include records with missing opening date by their creation date, explicitly as quality records only.
            if ($kind === 'processes') {
                $q = $this->base($kind, $company, $f)->where(fn ($dates) => $dates->whereBetween('r.DataAbertura', [$f->start->toDateString(), $f->reference()])->orWhere(fn ($fallback) => $fallback->whereRaw('NOT ('.$this->validDate('r.DataAbertura').')')->whereBetween('r.created_at', $f->utcBounds())));
            }
            $q->whereRaw($this->qualityCondition($kind, $company));
        }
        $q = $this->customerJoin($q, $kind, $company);
        $date = $this->date($kind);
        $q = $q->select('r.id')->selectRaw(($kind === 'processes' ? 'r.NrProcesso' : 'r.codigo_licenciamento')." AS number, COALESCE(c.CompanyName,'Cliente indisponível') AS customer, ".($kind === 'processes' ? 'r.Estado' : $this->state())." AS state, $date AS date, CASE WHEN $date >= '1000-01-01' AND $date <= ? THEN DATEDIFF(?,$date) END AS age", [$f->reference(), $f->reference()])->addSelect(DB::raw($kind === 'processes' ? 'r.DataFecho AS closed_at' : 'NULL AS closed_at'))->selectRaw($kind === 'processes' ? "CASE WHEN r.Estado='Finalizado' AND r.DataAbertura >= '1000-01-01' AND r.DataFecho >= r.DataAbertura THEN DATEDIFF(r.DataFecho,r.DataAbertura) END AS duration" : 'NULL AS duration')->selectRaw($this->qualityReason($kind, $company).' AS issue');
        if ($table === 'association') {
            $q->selectRaw($this->link().' AS associated');
        }

        return $q;
    }

    public function dimensionRows(string $kind, int $company, OperationalFilters $f, string $dimension): Builder
    {
        $q = $this->cohort($kind, $company, $f);
        if ($dimension === 'transport' && $kind === 'licenses') {
            $expression = $this->licenseTransport();
        } elseif ($dimension === 'port' && $kind === 'licenses') {
            $expression = "COALESCE(NULLIF(r.porto_entrada,''),'Não indicado')";
        } else {
            [$lookup,$column,$name] = match ($dimension) {
                'country' => ['paises', $kind === 'processes' ? 'Pais_origem' : 'pais_origem', 'pais'],
                'destination' => ['paises', 'Pais_destino', 'pais'],
                'office' => ['estancias', 'estancia_id', 'desc_estancia'],
                'transport' => ['tipo_transportes', 'TipoTransporte', 'descricao'],
                default => ['portos', 'porto_desembarque_id', 'porto'],
            };
            if ($kind === 'licenses' && $dimension === 'destination') {
                return $q->whereRaw('1=0')->selectRaw("'Indisponível' AS name, COUNT(*) AS entries")->groupBy('name');
            }
            $q->leftJoin($lookup.' as d', 'd.id', '=', 'r.'.$column);
            $expression = "COALESCE(d.$name,CONCAT('Não identificado · ',COALESCE(r.$column,'—')))";
        }

        return $q->selectRaw($expression.' AS name, COUNT(*) AS entries')->groupBy('name');
    }

    private function qualityCondition(string $kind, int $company): string
    {
        return '('.$this->qualityReason($kind, $company).") <> ''";
    }

    private function qualityReason(string $kind, int $company): string
    {
        $client = $kind === 'processes' ? 'customer_id' : 'cliente_id';
        $currency = $kind === 'processes' ? 'Moeda' : 'moeda';
        $date = $this->date($kind);
        $validCustomer = "EXISTS (SELECT 1 FROM customers qc WHERE qc.id=r.$client AND qc.deleted_at IS NULL AND (qc.empresa_id=$company OR EXISTS(SELECT 1 FROM customers_empresas qce WHERE qce.customer_id=qc.id AND qce.empresa_id=$company)))";
        $parts = ["IF(NOT ($validCustomer),'Cliente não acessível; ','')", "IF(r.$currency IS NULL OR TRIM(r.$currency)='','Moeda ausente; ','')", 'IF(NOT ('.$this->validDate($date)."),'Data ausente/inválida; ','')", "IF(r.peso_bruto IS NULL OR r.peso_bruto < 0,'Peso ausente/inválido; ','')"];
        if ($kind === 'processes') {
            $known = implode(',', array_map(fn ($state) => DB::getPdo()->quote($state->value), EstadoProcessoEnum::cases()));
            $parts[] = "IF(r.Estado NOT IN ($known),'Estado não reconhecido pelo enum; ','')";
            $parts[] = "IF(r.Estado='Finalizado' AND (r.DataFecho IS NULL OR r.DataFecho < '1000-01-01' OR r.DataFecho < r.DataAbertura),'Fecho ausente/inconsistente; ','')";
            $parts[] = "IF(r.Estado IN ('Aberto','Em analise','Aguardando documentos','Em despacho') AND r.DataFecho IS NOT NULL,'Estado activo com fecho; ','')";
            $parts[] = "IF(r.ValorTotal IS NULL OR r.ValorTotal < 0,'Valor declarado ausente/inválido; ','')";
        } else {
            $parts[] = "IF(r.fob_total IS NULL OR r.frete IS NULL OR r.seguro IS NULL OR r.cif IS NULL OR r.fob_total < 0 OR r.frete < 0 OR r.seguro < 0 OR r.cif < 0,'Valores monetários incompletos/inválidos; ','')";
        }

        return 'CONCAT('.implode(',', $parts).')';
    }

    private function clientPerformance(string $kind, int $company, OperationalFilters $f): Builder
    {
        if ($kind === 'licenses') {
            return $this->customerJoin($this->cohort($kind, $company, $f), $kind, $company)->selectRaw("COALESCE(c.CompanyName,'Cliente indisponível') AS name, COUNT(*) AS entries, SUM(".$this->state()." = 'Licenciado') AS completed, SUM(".$this->state()." <> 'Licenciado') AS active, NULL AS duration, 0 AS sample")->groupBy('c.id', 'c.CompanyName');
        }
        $q = $this->customerJoin($this->base($kind, $company, $f), $kind, $company);
        $start = $f->start->toDateString();
        $end = $f->reference();
        $portfolioSql = $this->portfolio($company, $f)->select('r.id');
        $q->where(fn ($rows) => $rows->whereBetween('r.DataAbertura',[$start, $end])->orWhere(fn ($closed) => $closed->where('r.Estado','Finalizado')->whereBetween('r.DataFecho',[$start, $end]))->orWhereIn('r.id',$portfolioSql));

        return $q->selectRaw("COALESCE(c.CompanyName,'Cliente indisponível') AS name, SUM(r.DataAbertura BETWEEN ? AND ?) AS entries, SUM(r.Estado='Finalizado' AND r.DataFecho BETWEEN ? AND ?) AS completed",[$start, $end, $start, $end])->selectRaw('SUM(r.id IN ('.$portfolioSql->toSql().')) AS active',$portfolioSql->getBindings())->selectRaw("AVG(CASE WHEN r.Estado='Finalizado' AND r.DataFecho BETWEEN ? AND ? AND r.DataAbertura >= '1000-01-01' AND r.DataFecho >= r.DataAbertura THEN DATEDIFF(r.DataFecho,r.DataAbertura) END) AS duration, COUNT(CASE WHEN r.Estado='Finalizado' AND r.DataFecho BETWEEN ? AND ? AND r.DataAbertura >= '1000-01-01' AND r.DataFecho >= r.DataAbertura THEN 1 END) AS sample",[$start, $end, $start, $end])->groupBy('c.id','c.CompanyName');
    }
}
