<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiagnoseV1Data extends Command
{
    protected $signature = 'v1:diagnostico {--limit=100 : Limite de amostras por achado}';
    protected $description = 'Diagnóstico read-only de schema, números, vínculos e totais da V1';
    public function handle(): int
    {
        $limit = max(1, min((int) $this->option('limit'), 1000));
        $report = ['read_only' => true, 'schema' => [], 'findings' => []];
        foreach (['operational_sequences', 'licenciamento_processos'] as $table) {
            $report['schema'][$table] = Schema::hasTable($table);
        }
        foreach (['DataPartida', 'cambio_confirmado', 'cambio_origem', 'cambio_data'] as $field) {
            $report['schema']['processos.' . $field] = Schema::hasColumn('processos', $field);
        }
        $report['schema']['migracaos.result'] = Schema::hasColumn('migracaos', 'result');
        foreach (['NrProcesso', 'ContaDespacho'] as $field) {
            $query = DB::table('processos')->whereNotNull($field)->select('empresa_id', $field)->selectRaw('COUNT(*) AS records')->groupBy('empresa_id', $field)->havingRaw('COUNT(*) > 1');
            $report['findings']['duplicates_' . $field] = $query->limit($limit)->get()->all();
        }
        $report['findings']['cross_company_items'] = DB::table('mercadorias as m')->join('licenciamentos as l', 'l.id', '=', 'm.licenciamento_id')->join('processos as p', 'p.id', '=', 'm.Fk_Importacao')->whereColumn('l.empresa_id', '!=', 'p.empresa_id')->select('m.id', 'm.licenciamento_id', 'm.Fk_Importacao')->limit($limit)->get()->all();
        $report['findings']['ambiguous_license_links'] = DB::table('mercadorias')->whereNotNull('licenciamento_id')->whereNotNull('Fk_Importacao')->select('licenciamento_id')->selectRaw('COUNT(DISTINCT Fk_Importacao) AS processes')->groupBy('licenciamento_id')->havingRaw('COUNT(DISTINCT Fk_Importacao) > 1')->limit($limit)->get()->all();
        foreach (['processos', 'licenciamentos'] as $table) {
            $query = DB::table($table)->whereRaw('ABS(COALESCE(cif, 0) - (COALESCE(fob_total, 0) + COALESCE(frete, 0) + COALESCE(seguro, 0))) > 0.01');
            $report['findings'][$table . '_cif_mismatch'] = ['count' => (clone $query)->count(), 'ids' => $query->limit($limit)->pluck('id')->all()];
        }
        if (Schema::hasTable('licenciamento_processos')) {
            $report['findings']['missing_permanent_links'] = DB::table('mercadorias as m')->whereNotNull('m.licenciamento_id')->whereNotNull('m.Fk_Importacao')->whereNotExists(fn ($q) => $q->selectRaw('1')->from('licenciamento_processos as lp')->whereColumn('lp.licenciamento_id', 'm.licenciamento_id'))->distinct()->limit($limit)->pluck('m.licenciamento_id')->all();
        }
        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return self::SUCCESS;
    }
}
