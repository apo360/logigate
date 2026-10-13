<?php

namespace App\Services\Dashboard;

use App\Models\Empresa;
use Generator;

final class OperationalReport
{
    public static function catalog(string $kind): array
    {
        return $kind === 'processes'
            ? ['executive' => 'Resumo executivo de Processos', 'portfolio' => 'Carteira activa e antiguidade', 'conclusions' => 'Conclusões e tempos de processamento', 'clients' => 'Actividade por cliente', 'dimensions' => 'Actividade por estância, país e transporte', 'quality' => 'Qualidade dos dados de Processos']
            : ['executive' => 'Resumo executivo de Licenciamentos', 'pending' => 'Pendências de licenciamento', 'association' => 'Associação a processos', 'values' => 'Valores FOB/CIF por moeda', 'clients' => 'Actividade por cliente', 'dimensions' => 'Actividade por país, transporte e porto', 'quality' => 'Qualidade dos dados de Licenciamentos'];
    }

    public function rows(string $kind, Empresa $company, OperationalFilters $f, string $report, array $snapshot): Generator
    {
        yield ['LogiGate', self::catalog($kind)[$report]];
        yield ['Empresa', $company->Empresa];
        yield ['Período', $f->start->toDateString(), $f->reference(), 'Africa/Luanda'];
        yield ['Emissão', now('Africa/Luanda')->format('Y-m-d H:i:s')];
        yield ['Dados actualizados', $snapshot['updated_at'], 'Cache até 60 segundos'];
        yield ['Filtros', json_encode($f->cacheValues(), JSON_UNESCAPED_UNICODE)];
        yield ['Unidades', 'Contagens em registos; idades/durações em dias; peso em kg; volumes em unidades; valores na moeda indicada, sem conversão.'];
        yield ['Limitação histórica', $snapshot['historical_warning']];
        yield ['Indicador', 'Valor', 'Definição'];
        foreach ($snapshot['kpis'] as $key => $value) {
            yield [$snapshot['definitions'][$key][0], $value ?? 'Indisponível', $snapshot['definitions'][$key][1]];
        }
        $extraLabels = ['duration_sample' => 'Amostra de conclusões válidas', 'duration_excluded' => 'Conclusões excluídas por datas inválidas', 'age_sample' => 'Amostra de idades válidas', 'age_excluded'=>'Activos conhecidos excluídos da idade por abertura ausente/inválida', 'cohort_completed' => 'Finalizados da coorte até à referência', 'cancelled_cohort' => 'Cancelados da coorte (estado observado)', 'cancelled_period' => 'Cancelamentos ocorridos no período', 'link_rate' => 'Associação da coorte (%)', 'approval_duration' => 'Tempo de aprovação (dias)', 'link_available' => 'Esquema canónico de associação disponível'];
        foreach ($snapshot['extra'] as $key => $value) {
            yield [$extraLabels[$key] ?? $key, is_bool($value) ? ($value ? 'Sim' : 'Não') : ($value ?? 'Indisponível')];
        }
        yield ['Peso bruto total (kg)', $snapshot['weight']['total'] ?? 'Ausente', 'Amostra', $snapshot['weight']['sample'], 'Registos da coorte', $snapshot['count']];
        yield ['Peso bruto médio (kg)', $snapshot['weight']['mean'] ?? 'Indisponível'];
        if ($snapshot['volume']) {
            yield ['Volumes (unidades)', $snapshot['volume']['total'] ?? 'Ausente', 'Amostra', $snapshot['volume']['sample']];
        }
        yield ['Valores por moeda — mercadorias, não receita do despachante'];
        $columnLabels = ['currency' => 'Moeda', 'records' => 'Registos', 'declared' => 'Valor declarado', 'declared_sample' => 'Amostra do valor declarado', 'fob' => 'FOB', 'fob_sample' => 'Amostra FOB', 'freight' => 'Frete', 'freight_sample' => 'Amostra frete', 'insurance' => 'Seguro', 'insurance_sample' => 'Amostra seguro', 'cif' => 'CIF', 'cif_sample' => 'Amostra CIF', 'id' => 'Identificador', 'number' => 'Número', 'customer' => 'Cliente', 'state' => 'Estado observado', 'date' => 'Abertura/criação', 'age' => 'Idade (dias)', 'closed_at' => 'Fecho', 'duration' => 'Conclusão média (dias)', 'issue' => 'Qualidade', 'associated' => 'Associado a processo', 'name' => 'Grupo/cliente', 'entries' => 'Entradas', 'completed' => 'Finalizados/licenciados', 'active' => 'Carteira/em aberto', 'sample' => 'Amostra válida'];
        foreach ($snapshot['money'] as $row) {
            yield array_map(fn ($key) => $columnLabels[$key] ?? $key, array_keys((array) $row));
            yield array_values((array) $row);
        }
        if (in_array($report, ['executive', 'dimensions', 'values'])) {
            foreach ($snapshot['charts'] as $name => $data) {
                yield ['Distribuição', ['states' => 'Estados', 'types' => 'Tipos de declaração', 'clients' => 'Clientes', 'origins' => 'Países de origem', 'destinations' => 'Países de destino', 'transport' => 'Transportes', 'offices' => 'Estâncias', 'timeline' => 'Evolução mensal', 'ports' => 'Portos de entrada', 'origin_ports' => 'Portos de origem', 'association' => 'Associação a processos', 'type_states' => 'Estados por tipo', 'age' => 'Faixas de idade'][$name] ?? $name, 'Gráficos dimensionais limitados aos maiores grupos; não são o total da população.'];
                foreach ($data as $row) {
                    yield array_map(fn ($value) => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value, array_values((array) $row));
                }
            }
        }
        if ($report === 'dimensions') {
            foreach ($kind === 'processes' ? ['country', 'destination', 'transport', 'office'] : ['country', 'transport', 'office', 'port'] as $dimension) {
                yield ['Análise integral', ['country' => 'País de origem', 'destination' => 'País de destino', 'transport' => 'Transporte', 'office' => 'Estância', 'port' => 'Porto de entrada'][$dimension]];
                yield ['Grupo', 'Registos'];
                foreach (app(OperationalAnalytics::class)->dimensionRows($kind, (int) $company->id, $f, $dimension)->orderByDesc('entries')->orderBy('name')->lazy(500) as $row) {
                    yield [$row->name, $row->entries];
                }
            }
        }
        $table = match ($report) {
            'portfolio','pending' => 'oldest','conclusions' => 'conclusions','clients' => 'clients','quality' => 'quality','association' => 'association',default => null
        };
        if (! $table) {
            return;
        }
        yield ['Detalhe', $report];
        if ($kind === 'licenses' && $report === 'association' && ! $snapshot['extra']['link_available']) {
            yield ['Indisponível', 'Tabela canónica licenciamento_processos ausente.'];

            return;
        }
        $query = app(OperationalAnalytics::class)->rows($kind, (int) $company->id, $f, $table);
        $query->orderBy($table === 'clients' ? 'entries' : 'date', $table === 'clients' ? 'desc' : 'asc');
        if ($table !== 'clients') {
            $query->orderBy('r.id');
        }
        $head = false;
        foreach ($query->lazy(500) as $row) {
            if (! $head) {
                yield array_map(fn ($key) => $columnLabels[$key] ?? $key, array_keys((array) $row));
                $head = true;
            }
            yield array_values((array) $row);
        }
    }

    public static function safeCell(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        // Neutralize formulas even when hidden behind whitespace/control characters.
        return preg_match('/^[\s\x00-\x1f]*[=+@-]/u', $value) ? "'".$value : $value;
    }
}
