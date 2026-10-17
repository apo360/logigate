<?php

namespace App\Application\Processo\Services;

use App\Application\PautaAduaneira\Actions\CalcularTributacaoProcessoAction;
use App\Models\DocumentoArquivo;
use App\Models\ExternalInvoice;
use App\Models\Processo;
use App\Models\ProcessoCustoAlerta;
use App\Models\ProcessoCustoConfiguracao;
use App\Models\ProcessoCustoEvento;
use App\Models\ProcessoCustoRegisto;
use App\Models\ProcessoCustoSimulacao;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class ProcessoCustoService
{
    public function processo(int $id, string $ability = 'view'): Processo
    {
        $p = app(ProcessoTenantAccessService::class)->findForUserOrFail(Auth::user(), $id);
        Gate::authorize($ability, $p);

        return $p;
    }

    private function scoped(string $model, Processo $p)
    {
        return $model::query()->where('empresa_id', $p->empresa_id)->where('processo_id', $p->id);
    }

    public function configuracao(): array
    {
        abort_unless(TenantContext::empresaId(), 403);

        return array_intersect_key(ProcessoCustoConfiguracao::where('empresa_id', TenantContext::empresaId())->first()?->toArray() ?? [], array_flip(['limiar_percentual', 'limiar_absoluto', 'amostra_minima', 'periodo_dias']))
            + ['limiar_percentual' => 10, 'limiar_absoluto' => 1000, 'amostra_minima' => 3, 'periodo_dias' => 180];
    }

    public function guardarConfiguracao(int $id, array $data): void
    {
        $p = $this->processo($id, 'update');
        $data = Validator::make($data, ['limiar_percentual' => 'required|numeric|min:0|max:10000', 'limiar_absoluto' => 'required|numeric|min:0|max:999999999999', 'amostra_minima' => 'required|integer|min:3|max:100', 'periodo_dias' => 'required|integer|min:1|max:3650'])->validate();
        DB::transaction(function () use ($p, $data) {
            $before = $this->configuracao();
            ProcessoCustoConfiguracao::updateOrCreate(['empresa_id' => $p->empresa_id], $data);
            $this->evento($p, 'configuracao', ['antes' => $before, 'depois' => $data]);
        });
    }

    public function entradas(Processo $p): array
    {
        $p->loadMissing('mercadorias.pautaAduaneira', 'tipoDeclaracao');

        return [
            'natureza' => $p->natureza_operacao, 'tipo' => $p->TipoProcesso,
            'regime' => [$p->tipoDeclaracao?->abrev, $p->tipoDeclaracao?->codigo],
            'moeda' => $p->Moeda, 'cambio' => $p->Cambio, 'fob' => $p->fob_total,
            'frete' => $p->frete, 'seguro' => $p->seguro, 'valor_aduaneiro' => $p->ValorAduaneiro,
            'mercadorias' => $p->mercadorias->sortBy('id')->map(fn ($m) => [
                'id' => $m->id, 'codigo' => $m->pautaAduaneira?->codigo ?? $m->codigo_aduaneiro,
                'fob' => $m->preco_total, 'pauta' => $m->pautaAduaneira?->only(['id', 'codigo', 'rg', 'sadc', 'ua', 'iva', 'ieq']),
            ])->values()->all(),
        ];
    }

    public function guardarSimulacao(int $id, array $params): ProcessoCustoSimulacao
    {
        $this->processo($id, 'simulate');
        $params = Validator::make($params, ['regimeTaxa' => ['required', Rule::in(['rg', 'sadc', 'ua'])], 'incluirIva' => 'required|boolean', 'incluirIeq' => 'required|boolean', 'regimesPorMercadoria' => 'array', 'regimesPorMercadoria.*' => Rule::in(['rg', 'sadc', 'ua'])])->validate();

        return DB::transaction(function () use ($id, $params) {
            $p = $this->processo($id, 'simulate');
            $p = Processo::where('empresa_id', $p->empresa_id)->lockForUpdate()->findOrFail($id);
            $inputs = $this->entradas($p);
            if (strtoupper((string) $p->tipoDeclaracao?->abrev) !== 'IM' || $p->isExportacaoCrude()) {
                throw ValidationException::withMessages(['simulacao' => 'O simulador de direitos de importação não se aplica a esta operação. Registe os valores documentados no controlo.']);
            }
            $ids = $p->mercadorias->modelKeys();
            foreach (array_keys($params['regimesPorMercadoria'] ?? []) as $key) {
                abort_unless(in_array((int) $key, $ids, true), 422);
            }
            $result = app(CalcularTributacaoProcessoAction::class)->execute($p, $params['regimeTaxa'], (bool) $params['incluirIva'], (bool) $params['incluirIeq'], $params['regimesPorMercadoria'] ?? [])->toArray();
            $complete = empty($result['alertas']) && $p->mercadorias->isNotEmpty()
                && count($result['items']) === $p->mercadorias->count()
                && $p->mercadorias->every(function ($m) use ($params) {
                    if (! $m->pautaAduaneira || (float) $m->preco_total <= 0) {
                        return false;
                    }
                    $fields = [$params['regimesPorMercadoria'][$m->id] ?? $params['regimeTaxa']];
                    if ($params['incluirIva']) {
                        $fields[] = 'iva';
                    }
                    if ($params['incluirIeq']) {
                        $fields[] = 'ieq';
                    }
                    foreach ($fields as $field) {
                        if ($m->pautaAduaneira->$field === null || ! is_numeric($m->pautaAduaneira->$field) || (float) $m->pautaAduaneira->$field < 0) {
                            return false;
                        }
                    }

                    return true;
                })
                && abs((float) $p->mercadorias->sum('preco_total') - (float) $p->fob_total) <= 0.01
                && filled($p->Moeda) && $p->frete !== null && $p->seguro !== null
                && (float) $p->frete >= 0 && (float) $p->seguro >= 0;
            $fingerprint = hash('sha256', json_encode([$params, $inputs, $result, $complete], JSON_THROW_ON_ERROR));
            $latest = $this->scoped(ProcessoCustoSimulacao::class, $p)->latest('versao')->first();
            if ($latest?->fingerprint === $fingerprint) {
                if (Gate::allows('update', $p) && Gate::allows('view', $p)) {
                    $this->sincronizar($id);
                }

                return $latest;
            }
            $s = ProcessoCustoSimulacao::create(['empresa_id' => $p->empresa_id, 'processo_id' => $p->id, 'created_by' => Auth::id(), 'versao' => ($latest?->versao ?? 0) + 1, 'parametros' => $params, 'entradas' => $inputs, 'resultado' => $result, 'completa' => $complete, 'fingerprint' => $fingerprint]);
            $this->evento($p, 'simulacao_guardada', ['id' => $s->id, 'versao' => $s->versao, 'completa' => $complete]);
            if (Gate::allows('update', $p) && Gate::allows('view', $p)) {
                $this->sincronizar($id);
            }

            return $s;
        });
    }

    public function documentos(Processo $p)
    {
        return DocumentoArquivo::where('empresa_id', $p->empresa_id)->where('processo_id', $p->id)->where('status', 'activo')->get()
            ->filter(fn ($d) => Gate::allows('view', $d));
    }

    public function facturas(Processo $p)
    {
        return ExternalInvoice::where('empresa_id', $p->empresa_id)->where('processo_id', $p->id)
            ->where('customer_id', $p->customer_id)->whereIn('status', ['issued', 'paid'])->whereIn('document_type', ['FT', 'FR'])
            ->where('currency', 'AOA')->get();
    }

    public function registar(int $id, array $data): ProcessoCustoRegisto
    {
        $this->processo($id, 'update');
        $data = Validator::make($data, [
            'fase' => ['required', Rule::in(['declarado', 'pago', 'faturado', 'despesa'])],
            'escopo' => ['required', Rule::in(['tributos', 'custos'])],
            'valor' => 'required|numeric|min:0|max:999999999999.99|decimal:0,2', 'data' => 'required|date|before_or_equal:today',
            'referencia' => 'required|string|max:150', 'descricao' => 'required|string|max:2000',
            'documento_id' => 'nullable|integer|min:1', 'external_invoice_id' => 'nullable|integer|min:1', 'origem_id' => 'nullable|integer|min:1',
        ])->validate();

        return DB::transaction(function () use ($id, $data) {
            $p = $this->processo($id, 'update');
            $p = Processo::where('empresa_id', $p->empresa_id)->lockForUpdate()->findOrFail($id);
            $data['referencia'] = trim($data['referencia']);
            if ($data['referencia'] === '') {
                throw ValidationException::withMessages(['referencia' => 'Indique a referência.']);
            }
            if ($this->scoped(ProcessoCustoRegisto::class, $p)->whereNull('anulado_at')->where('fase', $data['fase'])->where('escopo', $data['escopo'])->where('referencia', $data['referencia'])->exists()) {
                throw ValidationException::withMessages(['referencia' => 'Referência já registada nesta fase e âmbito.']);
            }
            if (! empty($data['documento_id'])) {
                $doc = DocumentoArquivo::where('empresa_id', $p->empresa_id)->where('processo_id', $p->id)->where('status', 'activo')->findOrFail($data['documento_id']);
                Gate::authorize('view', $doc);
            }
            if ($data['fase'] !== 'faturado' && (! empty($data['external_invoice_id']) || ! empty($data['origem_id']))) {
                throw ValidationException::withMessages(['fase' => 'Factura e afectação de origem são exclusivas da fase facturado.']);
            }
            if ($data['fase'] === 'faturado') {
                if (empty($data['external_invoice_id'])) {
                    throw ValidationException::withMessages(['external_invoice_id' => 'Seleccione uma factura emitida em AOA deste processo.']);
                }
                $invoice = $this->facturas($p)->firstWhere('id', (int) $data['external_invoice_id']);
                abort_unless($invoice, 422, 'Factura inválida para este processo.');
                // The invoice lock also serializes allocations across processes and requests.
                $invoice = ExternalInvoice::where('empresa_id', $p->empresa_id)->lockForUpdate()->findOrFail($invoice->id);
                abort_unless(in_array($invoice->status, ['issued', 'paid'], true) && in_array($invoice->document_type, ['FT', 'FR'], true)
                    && $invoice->currency === 'AOA' && (int) $invoice->processo_id === $p->id && (int) $invoice->customer_id === (int) $p->customer_id, 422, 'A factura deixou de ser elegível.');
                $allocated = ProcessoCustoRegisto::where('empresa_id', $p->empresa_id)->where('external_invoice_id', $invoice->id)->whereNull('anulado_at')->sum('valor');
                if ($this->cents($allocated) + $this->cents($data['valor']) > $this->cents($invoice->gross_total)) {
                    throw ValidationException::withMessages(['valor' => 'A afectação ultrapassa o total disponível da factura.']);
                }
                if (! empty($data['origem_id'])) {
                    $origin = $this->scoped(ProcessoCustoRegisto::class, $p)->whereNull('anulado_at')->whereIn('fase', ['pago', 'despesa'])->where('escopo', $data['escopo'])->findOrFail($data['origem_id']);
                    $used = $this->scoped(ProcessoCustoRegisto::class, $p)->whereNull('anulado_at')->where('origem_id', $origin->id)->sum('valor');
                    if ($this->cents($used) + $this->cents($data['valor']) > $this->cents($origin->valor)) {
                        throw ValidationException::withMessages(['valor' => 'A afectação ultrapassa o valor da origem.']);
                    }
                }
            }
            $record = ProcessoCustoRegisto::create($data + ['empresa_id' => $p->empresa_id, 'processo_id' => $p->id, 'created_by' => Auth::id(), 'contexto' => $this->entradas($p)]);
            $this->evento($p, 'registo_criado', ['id' => $record->id, 'fase' => $record->fase, 'valor' => $record->valor, 'referencia' => $record->referencia]);
            $this->sincronizar($id);

            return $record;
        });
    }

    public function anular(int $id, int $recordId, string $reason): void
    {
        Validator::make(['motivo' => $reason], ['motivo' => 'required|string|min:10|max:2000'])->validate();
        DB::transaction(function () use ($id, $recordId, $reason) {
            $p = $this->processo($id, 'update');
            Processo::where('empresa_id', $p->empresa_id)->lockForUpdate()->findOrFail($id);
            $r = $this->scoped(ProcessoCustoRegisto::class, $p)->whereNull('anulado_at')->findOrFail($recordId);
            if ($this->scoped(ProcessoCustoRegisto::class, $p)->where('origem_id', $r->id)->whereNull('anulado_at')->exists()) {
                throw ValidationException::withMessages(['motivo' => 'Anule primeiro as afectações de facturação associadas.']);
            }
            $r->update(['anulado_at' => now(), 'anulado_por' => Auth::id(), 'motivo_anulacao' => $reason]);
            $this->evento($p, 'registo_anulado', ['id' => $r->id, 'motivo' => $reason]);
            $this->sincronizar($id);
        });
    }

    private function cents($value): int
    {
        return (int) round((float) $value * 100);
    }

    private function canonical(array $value): string
    {
        $sort = function (array $array) use (&$sort): array {
            if (! array_is_list($array)) {
                ksort($array);
            }
            foreach ($array as &$item) {
                if (is_array($item)) {
                    $item = $sort($item);
                }
            }

            return $array;
        };

        return json_encode($sort($value), JSON_THROW_ON_ERROR);
    }

    public function confirmado(ProcessoCustoRegisto $r, Processo $p): bool
    {
        if ($r->anulado_at) {
            return false;
        }
        if ($r->fase === 'faturado') {
            return $this->facturas($p)->contains('id', $r->external_invoice_id);
        }

        return $r->documento_id && DocumentoArquivo::where('empresa_id', $p->empresa_id)->where('processo_id', $p->id)->where('status', 'activo')->whereKey($r->documento_id)->exists();
    }

    public function relatorio(int $id): array
    {
        $p = $this->processo($id);
        $records = $this->scoped(ProcessoCustoRegisto::class, $p)->orderByDesc('id')->get();
        $active = $records->whereNull('anulado_at');
        $versions = $this->scoped(ProcessoCustoSimulacao::class, $p)->orderByDesc('versao')->get();
        $latest = $versions->first();
        $stale = $latest && $this->canonical($latest->entradas) !== $this->canonical($this->entradas($p));
        $totals = ['estimado' => $latest && $latest->completa && ! $stale ? (float) $latest->resultado['totais']['total_impostos'] : null];
        $docs = DocumentoArquivo::where('empresa_id', $p->empresa_id)->where('processo_id', $p->id)->where('status', 'activo')->whereIn('id', $active->pluck('documento_id')->filter())->pluck('id');
        $invoiceIds = $this->facturas($p)->pluck('id');
        $confirmed = $active->filter(fn ($r) => $r->fase === 'faturado' ? $invoiceIds->contains($r->external_invoice_id) : $docs->contains($r->documento_id));
        $confirmation = $confirmed->pluck('id')->flip();
        foreach (['tributos', 'custos'] as $scope) {
            foreach (['declarado', 'pago', 'faturado', 'despesa'] as $phase) {
                $rows = $active->where('escopo', $scope)->where('fase', $phase);
                if ($phase === 'declarado') {
                    $rows = $rows->take(1);
                }
                $totals[$scope][$phase] = $rows->isNotEmpty() && $rows->every(fn ($r) => $confirmed->contains('id', $r->id)) ? round((float) $rows->sum('valor'), 2) : null;
            }
        }
        $alerts = [];
        $add = function ($key, $title, $detail, $data = []) use (&$alerts) {
            $alerts[$key] = ['titulo' => $title, 'explicacao' => $detail, 'dados' => $data];
        };
        if ($totals['estimado'] === null) {
            $add('estimativa', 'Estimativa por confirmar', $stale ? 'Os dados do processo ou da pauta mudaram desde a última versão. Recalcule e guarde uma versão.' : 'Guarde uma simulação completa aplicável à operação. Nas exportações use os valores documentados; o simulador de importação não se aplica.');
        }
        $config = $this->configuracao();
        $declared = $totals['tributos']['declarado'];
        if ($declared !== null && $totals['estimado'] !== null && $this->desvio($declared, $totals['estimado'], $config)) {
            $add('desvio_tributos', 'Tributos declarados diferem da estimativa', 'Comparação do total de tributos em Kz. Confirme se a declaração abrange os mesmos tributos da simulação.', ['estimado' => $totals['estimado'], 'declarado' => $declared, 'diferenca' => round($declared - $totals['estimado'], 2)]);
        }
        if ($declared !== null && $totals['tributos']['pago'] !== null && $this->desvio($totals['tributos']['pago'], $declared, $config)) {
            $add('desvio_pagamento', 'Tributos pagos diferem do declarado', 'Confira pagamentos parciais, revisões da declaração e o âmbito dos comprovativos antes do fecho.', ['declarado' => $declared, 'pago' => $totals['tributos']['pago'], 'diferenca' => round($totals['tributos']['pago'] - $declared, 2)]);
        }
        foreach ($active as $r) {
            if (! $confirmed->contains('id', $r->id)) {
                $add('evidencia_'.$r->id, 'Origem por confirmar: '.$r->referencia, 'Falta comprovativo activo do processo ou a factura associada deixou de estar emitida/activa.', ['registo' => $r->id, 'valor' => $r->valor]);
            }
            if ($r->fase === 'pago' && $confirmed->contains('id', $r->id)) {
                $recovered = (float) $confirmed->where('fase', 'faturado')->where('origem_id', $r->id)->sum('valor');
                if ($this->cents($r->valor) > $this->cents($recovered)) {
                    $add('recuperacao_'.$r->id, 'Pagamento sem facturação integral', 'Associe uma afectação de factura a este pagamento ou justifique que o custo não será repercutido ao cliente.', ['registo' => $r->id, 'pago' => $r->valor, 'faturado' => $recovered, 'por_repercutir' => round((float) $r->valor - $recovered, 2)]);
                }
            }
        }
        foreach (['declarado', 'pago', 'faturado'] as $phase) {
            if ($totals['tributos'][$phase] === null) {
                $add('fecho_'.$phase, 'Antes do fecho: '.$phase.' por confirmar', 'Não existe um total documentado completo de tributos nesta fase. Zero só é confirmado através de um registo explícito com evidência.');
            }
        }
        $comparables = $this->comparaveis($p, $active->where('escopo', 'tributos')->where('fase', 'declarado')->first(), $config);
        if ($comparables['desvio']) {
            $add('historico', 'Carga tributária fora do histórico comparável', 'Tributos / valor aduaneiro em Kz, face à mediana de operações da mesma empresa, natureza, regime, moeda e perfil pautal. Indício para revisão; não determina imposto devido.', $comparables);
        }
        $tarifa = $p->emolumentoTarifa;
        $manualTotal = $tarifa ? app(EmolumentoTarifaTotalsService::class)->guiaFiscal($tarifa) : null;
        $expenseRows = $active->where('fase', 'despesa');
        if ($manualTotal !== null && $expenseRows->isNotEmpty() && $expenseRows->every(fn ($r) => $confirmed->contains('id', $r->id))) {
            $expenses = (float) $expenseRows->sum('valor');
            if ($this->desvio($expenses, $manualTotal, $config)) {
                $add('desvio_despesas', 'Despesas documentadas diferem do formulário', 'Compare a composição das parcelas documentadas com as despesas e imposições lançadas no formulário; diferenças de âmbito devem ser explicadas.', ['formulario' => $manualTotal, 'documentado' => $expenses]);
            }
        }

        return compact('p', 'records', 'versions', 'totals', 'alerts', 'comparables', 'stale', 'manualTotal', 'confirmation') + [
            'workflow' => $this->scoped(ProcessoCustoAlerta::class, $p)->get()->keyBy('chave'),
            'history' => $this->scoped(ProcessoCustoEvento::class, $p)->latest('id')->limit(100)->get(),
        ];
    }

    private function desvio(float $actual, float $base, array $config): bool
    {
        $difference = abs($actual - $base);

        return $difference > (float) $config['limiar_absoluto']
            && ($base == 0 || $difference / abs($base) * 100 > (float) $config['limiar_percentual']);
    }

    private function perfil(array $context): array
    {
        $codes = array_values(array_unique(array_filter(array_column($context['mercadorias'] ?? [], 'codigo'))));
        sort($codes);

        return [$context['natureza'] ?? null, $context['regime'] ?? null, $context['moeda'] ?? null, $codes];
    }

    private function comparaveis(Processo $p, ?ProcessoCustoRegisto $current, array $config): array
    {
        $empty = ['amostra' => [], 'mediana' => null, 'taxa_actual' => null, 'desvio' => false, 'minimo' => (int) $config['amostra_minima'], 'periodo_dias' => (int) $config['periodo_dias']];
        if (! $current || ! $this->confirmado($current, $p) || (float) ($current->contexto['valor_aduaneiro'] ?? 0) <= 0 || empty($this->perfil($current->contexto)[3])) {
            return $empty;
        }
        // Latest active declaration per process, before comparing; an older revision never fills a missing latest value.
        $latestIds = ProcessoCustoRegisto::where('empresa_id', $p->empresa_id)->where('fase', 'declarado')->where('escopo', 'tributos')->whereNull('anulado_at')->where('processo_id', '!=', $p->id)->selectRaw('MAX(id)')->groupBy('processo_id');
        $rows = ProcessoCustoRegisto::whereIn('id', $latestIds)->whereDate('data', '>=', now()->subDays((int) $config['periodo_dias']))->whereDate('data', '<=', today())->orderByDesc('data')->limit(500)->get();
        $sample = [];
        foreach ($rows as $r) {
            if ($this->perfil($r->contexto) !== $this->perfil($current->contexto) || (float) ($r->contexto['valor_aduaneiro'] ?? 0) <= 0) {
                continue;
            }
            $other = Processo::where('empresa_id', $p->empresa_id)->find($r->processo_id);
            if (! $other || ! Gate::allows('view', $other) || ! $this->confirmado($r, $other)) {
                continue;
            }
            $sample[] = ['processo_id' => $other->id, 'numero' => $other->NrProcesso, 'referencia' => $r->referencia, 'taxa' => round((float) $r->valor / (float) $r->contexto['valor_aduaneiro'] * 100, 4)];
        }
        $rate = (float) $current->valor / (float) $current->contexto['valor_aduaneiro'] * 100;
        $median = count($sample) >= (int) $config['amostra_minima'] ? collect($sample)->median('taxa') : null;
        $equivalent = $median === null ? 0 : $median / 100 * (float) $current->contexto['valor_aduaneiro'];

        return array_replace($empty, ['amostra' => $sample, 'taxa_actual' => round($rate, 4), 'mediana' => $median, 'desvio' => $median !== null && $this->desvio((float) $current->valor, $equivalent, $config)]);
    }

    public function sincronizar(int $id): void
    {
        DB::transaction(function () use ($id) {
            $p = $this->processo($id, 'update');
            Processo::where('empresa_id', $p->empresa_id)->lockForUpdate()->findOrFail($id);
            $report = $this->relatorio($id);
            $keys = [];
            foreach ($report['alerts'] as $key => $details) {
                $keys[] = $key;
                $hash = hash('sha256', json_encode($details, JSON_THROW_ON_ERROR));
                $alert = $this->scoped(ProcessoCustoAlerta::class, $p)->firstOrNew(['chave' => $key]);
                if (! $alert->exists || $alert->fingerprint !== $hash || ! $alert->detectado) {
                    $this->evento($p, 'alerta_detectado', ['chave' => $key, 'antes' => $alert->exists ? $alert->only(['estado', 'fingerprint']) : null, 'detalhes' => $details]);
                    $alert->fill(['empresa_id' => $p->empresa_id, 'processo_id' => $p->id, 'fingerprint' => $hash, 'detalhes' => $details, 'estado' => 'aberto', 'detectado' => true, 'justificacao' => null])->save();
                }
            }
            foreach ($this->scoped(ProcessoCustoAlerta::class, $p)->where('detectado', true)->whereNotIn('chave', $keys)->get() as $alert) {
                $alert->update(['detectado' => false]);
                $this->evento($p, 'alerta_nao_detectado', ['chave' => $alert->chave]);
            }
            $this->evento($p, 'conferencia_actualizada', ['alertas' => count($keys)]);
        });
    }

    public function tratar(int $id, string $key, array $data): void
    {
        $p = $this->processo($id, 'update');
        $data = Validator::make($data, ['estado' => ['required', Rule::in(['aberto', 'encaminhado', 'justificado', 'resolvido'])], 'responsavel_id' => 'nullable|integer|min:1', 'prazo' => 'nullable|date', 'justificacao' => 'required|string|min:10|max:2000'])->validate();
        if (! empty($data['responsavel_id']) && ! DB::table('empresa_users')->where('empresa_id', $p->empresa_id)->where('user_id', $data['responsavel_id'])->exists()) {
            throw ValidationException::withMessages(['responsavel_id' => 'Responsável não pertence à empresa.']);
        }
        if ($data['estado'] === 'encaminhado' && empty($data['responsavel_id'])) {
            throw ValidationException::withMessages(['responsavel_id' => 'Seleccione o responsável.']);
        }
        DB::transaction(function () use ($p, $key, $data) {
            Processo::where('empresa_id', $p->empresa_id)->lockForUpdate()->findOrFail($p->id);
            $this->sincronizar($p->id);
            $alert = $this->scoped(ProcessoCustoAlerta::class, $p)->where('chave', $key)->firstOrFail();
            $before = $alert->only(['estado', 'responsavel_id', 'prazo', 'justificacao']);
            $alert->update($data);
            $this->evento($p, 'alerta_tratado', ['chave' => $key, 'antes' => $before, 'depois' => $data]);
        });
    }

    private function evento(Processo $p, string $action, array $details): void
    {
        ProcessoCustoEvento::create(['empresa_id' => $p->empresa_id, 'processo_id' => $p->id, 'user_id' => Auth::id(), 'acao' => $action, 'detalhes' => $details, 'created_at' => now()]);
    }
}
