<?php

declare(strict_types=1);

namespace App\Application\Licenciamento\Services;

use App\Models\Licenciamento;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

final class LicenciamentoOperationalReadinessService
{
    public function analyze(Licenciamento $licenciamento): array
    {
        $licenciamento->loadMissing([
            'cliente',
            'exportador',
            'estancia',
            'mercadorias',
            'mercadoriasAgrupadas',
            'procLicenFaturas.processo',
            'procLicenFaturas.fatura.salesdoctotal',
        ]);

        $checklist = $this->buildChecklist($licenciamento);
        $txtBlockers = $this->buildTxtBlockers($licenciamento, $checklist);
        $processBlockers = $this->buildProcessBlockers($licenciamento, $txtBlockers);
        $alerts = $this->buildAlerts($licenciamento);

        return [
            'score' => $this->score($checklist),
            'checklist' => $checklist,
            'alerts' => $alerts,
            'financial_summary' => $this->buildFinancialSummary($licenciamento),
            'timeline' => $this->buildTimeline($licenciamento),
            'ready_for_txt' => $txtBlockers === [],
            'ready_for_process' => $processBlockers === [],
            'txt_blockers' => $txtBlockers,
            'process_blockers' => $processBlockers,
        ];
    }

    private function buildChecklist(Licenciamento $licenciamento): array
    {
        $mercadorias = $this->mercadorias($licenciamento);
        $expectedCif = $this->expectedCif($licenciamento);
        $cif = $this->number($licenciamento->cif);

        return [
            $this->item('Cliente associado', (bool) $licenciamento->cliente_id && $licenciamento->relationLoaded('cliente') && $licenciamento->cliente !== null, 'Cliente informado.', 'Cliente não informado.'),
            $this->item('Exportador associado', (bool) $licenciamento->exportador_id && $licenciamento->relationLoaded('exportador') && $licenciamento->exportador !== null, 'Exportador informado.', 'Exportador não informado.'),
            $this->item('Estância associada', (bool) $licenciamento->estancia_id && $licenciamento->relationLoaded('estancia') && $licenciamento->estancia !== null, 'Estância informada.', 'Estância não informada.'),
            $this->item('Referência do cliente', $this->filled($licenciamento->referencia_cliente), 'Referência informada.', 'Referência do cliente não informada.', 'warning'),
            $this->item('Factura proforma', $this->filled($licenciamento->factura_proforma), 'Factura proforma informada.', 'Factura proforma não informada.', 'warning'),
            $this->item('Descrição', $this->filled($licenciamento->descricao), 'Descrição informada.', 'Descrição não informada.', 'warning'),
            $this->item('Moeda', $this->filled($licenciamento->moeda), 'Moeda informada.', 'Moeda não informada.'),
            $this->item('Tipo de declaração', $this->filled($licenciamento->tipo_declaracao), 'Tipo de declaração informado.', 'Tipo de declaração não informado.'),
            $this->item('Tipo de transporte', $this->filled($licenciamento->tipo_transporte), 'Tipo de transporte informado.', 'Tipo de transporte não informado.'),
            $this->item('Método de avaliação', $this->filled($licenciamento->metodo_avaliacao), 'Método de avaliação informado.', 'Método de avaliação não informado.'),
            $this->item('Código de volume', $this->filled($licenciamento->codigo_volume), 'Código de volume informado.', 'Código de volume não informado.'),
            $this->item('Quantidade de volumes', $this->number($licenciamento->qntd_volume) > 0, 'Quantidade de volumes informada.', 'Quantidade de volumes deve ser maior que zero.'),
            $this->item('FOB total', $this->number($licenciamento->fob_total) > 0, 'FOB total informado.', 'FOB total deve ser maior que zero.'),
            $this->item('Mercadorias adicionadas', $mercadorias->isNotEmpty(), 'Mercadorias adicionadas.', 'Não existem mercadorias associadas.'),
            $this->item('Peso bruto', $this->number($licenciamento->peso_bruto) > 0 || $mercadorias->sum(fn ($m) => $this->number($m->Peso)) > 0, 'Peso bruto informado ou calculável.', 'Peso bruto não informado.'),
            $this->item('CIF', $cif > 0 && abs($cif - $expectedCif) <= 0.01, 'CIF válido.', 'CIF ausente ou diferente de FOB + Frete + Seguro.', 'warning'),
            $this->item('País de origem', $this->filled($licenciamento->pais_origem) || $this->filled($licenciamento->nacionalidade_transporte), 'País de origem/transporte informado.', 'País de origem não informado.', 'info'),
            $this->item('Porto de origem', $this->filled($licenciamento->porto_origem), 'Porto de origem informado.', 'Porto de origem não informado.', 'warning'),
        ];
    }

    private function buildTxtBlockers(Licenciamento $licenciamento, array $checklist): array
    {
        $blockers = [];
        $requiredLabels = [
            'Cliente associado',
            'Exportador associado',
            'Estância associada',
            'Moeda',
            'Tipo de declaração',
            'Tipo de transporte',
            'Método de avaliação',
            'Código de volume',
            'Quantidade de volumes',
            'FOB total',
            'Peso bruto',
            'Porto de origem',
        ];

        foreach ($checklist as $item) {
            if (in_array($item['label'], $requiredLabels, true) && ! $item['ok']) {
                $blockers[] = $item['message'];
            }
        }

        if ($this->mercadorias($licenciamento)->isEmpty()) {
            $blockers[] = 'Não existem mercadorias associadas.';
        }

        if ($this->mercadoriasAgrupadas($licenciamento)->isEmpty()) {
            $blockers[] = 'Não existem mercadorias agrupadas para gerar o TXT.';
        }

        if ($this->number($licenciamento->frete) <= 0) {
            $blockers[] = 'Frete deve ser maior que zero.';
        }

        if ($this->number($licenciamento->seguro) <= 0) {
            $blockers[] = 'Seguro deve ser maior que zero.';
        }

        return array_values(array_unique($blockers));
    }

    private function buildProcessBlockers(Licenciamento $licenciamento, array $txtBlockers): array
    {
        $blockers = $txtBlockers;

        if (! (bool) $licenciamento->txt_gerado) {
            $blockers[] = 'TXT ainda não foi gerado.';
        }

        if ($this->mercadorias($licenciamento)->isEmpty()) {
            $blockers[] = 'Não existem mercadorias associadas.';
        }

        if ($this->faturas($licenciamento)->contains(fn ($fatura) => $fatura->processo_id !== null)) {
            $blockers[] = 'Já existe processo constituído para este licenciamento.';
        }

        if ($this->faturas($licenciamento)->contains(fn ($fatura) => $fatura->status_fatura === 'anulada')) {
            $blockers[] = 'Existe fatura anulada associada.';
        }

        return array_values(array_unique($blockers));
    }

    private function buildAlerts(Licenciamento $licenciamento): array
    {
        $alerts = [];
        $mercadorias = $this->mercadorias($licenciamento);
        $faturas = $this->faturas($licenciamento);
        $expectedCif = $this->expectedCif($licenciamento);
        $cif = $this->number($licenciamento->cif);

        if ($mercadorias->isEmpty()) {
            $alerts[] = $this->alert('warning', 'Sem mercadorias associadas', 'Adicione mercadorias antes das ações operacionais.');
        }

        if ($this->number($licenciamento->fob_total) <= 0) {
            $alerts[] = $this->alert('danger', 'FOB total zerado', 'Informe o FOB total antes de gerar TXT ou constituir processo.');
        }

        if ($cif <= 0 || abs($cif - $expectedCif) > 0.01) {
            $alerts[] = $this->alert('warning', 'CIF inconsistente', 'O CIF deve corresponder a FOB + Frete + Seguro.');
        }

        if ($this->number($licenciamento->peso_bruto) <= 0) {
            $alerts[] = $this->alert('warning', 'Peso bruto não informado', 'Informe ou calcule o peso bruto do licenciamento.');
        }

        if ($mercadorias->contains(fn ($m) => ! $this->filled($m->codigo_aduaneiro))) {
            $alerts[] = $this->alert('danger', 'Mercadoria sem código aduaneiro', 'Há mercadoria sem código aduaneiro informado.');
        }

        if ($mercadorias->contains(fn ($m) => $this->number($m->Quantidade) <= 0)) {
            $alerts[] = $this->alert('danger', 'Quantidade inválida', 'Há mercadoria com quantidade inválida.');
        }

        if ($mercadorias->contains(fn ($m) => $this->number($m->preco_unitario) <= 0)) {
            $alerts[] = $this->alert('warning', 'Preço unitário inválido', 'Há mercadoria com preço unitário inválido.');
        }

        if ($mercadorias->contains(fn ($m) => $this->number($m->preco_total) <= 0)) {
            $alerts[] = $this->alert('danger', 'Preço total inválido', 'Há mercadoria com preço total inválido.');
        }

        if ((bool) $licenciamento->txt_gerado) {
            $alerts[] = $this->alert('info', 'TXT já gerado', 'Este licenciamento já teve TXT gerado.');
        }

        if ($faturas->contains(fn ($fatura) => $fatura->status_fatura === 'paga')) {
            $alerts[] = $this->alert('success', 'Fatura paga', 'Existe fatura paga associada a este licenciamento.');
        }

        if ($faturas->contains(fn ($fatura) => $fatura->status_fatura === 'anulada')) {
            $alerts[] = $this->alert('danger', 'Fatura anulada', 'Existe fatura anulada associada a este licenciamento.');
        }

        if ($faturas->contains(fn ($fatura) => $fatura->processo_id !== null)) {
            $alerts[] = $this->alert('info', 'Processo já constituído', 'Este licenciamento já possui processo associado.');
        }

        if (! $licenciamento->podeSerEditado()) {
            $alerts[] = $this->alert('warning', 'Edição limitada', 'As regras atuais indicam que este licenciamento não pode ser editado.');
        }

        return $alerts;
    }

    private function buildFinancialSummary(Licenciamento $licenciamento): array
    {
        $mercadorias = $this->mercadorias($licenciamento);

        return [
            'fob_total' => $this->number($licenciamento->fob_total),
            'frete' => $this->number($licenciamento->frete),
            'seguro' => $this->number($licenciamento->seguro),
            'cif' => $this->number($licenciamento->cif),
            'peso_bruto' => $this->number($licenciamento->peso_bruto),
            'mercadorias_count' => $mercadorias->count(),
            'volumes_total' => $this->number($licenciamento->qntd_volume),
            'moeda' => $licenciamento->moeda ?: 'Não informada',
            'codigos_aduaneiros_distintos' => $mercadorias
                ->pluck('codigo_aduaneiro')
                ->filter(fn ($codigo) => $this->filled($codigo))
                ->unique()
                ->count(),
        ];
    }

    private function buildTimeline(Licenciamento $licenciamento): array
    {
        $events = [];

        if ($licenciamento->created_at) {
            $events[] = $this->event('Licenciamento criado', 'Registo inicial do licenciamento.', $licenciamento->created_at);
        }

        if ($licenciamento->updated_at && (! $licenciamento->created_at || ! $licenciamento->updated_at->equalTo($licenciamento->created_at))) {
            $events[] = $this->event('Licenciamento actualizado', 'Dados do licenciamento foram actualizados.', $licenciamento->updated_at);
        }

        if ($this->mercadorias($licenciamento)->isNotEmpty()) {
            $events[] = $this->event('Mercadorias associadas', $this->mercadorias($licenciamento)->count() . ' mercadoria(s) vinculada(s).', $this->mercadorias($licenciamento)->max('created_at'));
        }

        if ((bool) $licenciamento->txt_gerado) {
            $events[] = $this->event('TXT gerado', 'O TXT foi marcado como gerado.', $licenciamento->updated_at ?? $licenciamento->created_at);
        }

        foreach ($this->faturas($licenciamento) as $fatura) {
            $status = ucfirst((string) ($fatura->status_fatura ?: 'emitida'));
            $events[] = $this->event('Fatura ' . strtolower($status), 'Status da fatura: ' . $status . '.', $fatura->updated_at ?? $fatura->created_at);

            if ($fatura->processo_id) {
                $events[] = $this->event('Processo constituído', 'Processo associado ao licenciamento.', $fatura->updated_at ?? $fatura->created_at);
            }
        }

        return collect($events)
            ->sortBy('date_sort')
            ->values()
            ->map(fn ($event) => collect($event)->except('date_sort')->all())
            ->all();
    }

    private function score(array $checklist): int
    {
        if ($checklist === []) {
            return 0;
        }

        $ok = collect($checklist)->where('ok', true)->count();

        return (int) round(($ok / count($checklist)) * 100);
    }

    private function item(string $label, bool $ok, string $okMessage, string $failMessage, string $failSeverity = 'danger'): array
    {
        return [
            'label' => $label,
            'ok' => $ok,
            'severity' => $ok ? 'success' : $failSeverity,
            'message' => $ok ? $okMessage : $failMessage,
        ];
    }

    private function alert(string $type, string $title, string $message): array
    {
        return compact('type', 'title', 'message');
    }

    private function event(string $title, string $message, mixed $date): array
    {
        $date = $this->toCarbon($date);

        return [
            'title' => $title,
            'message' => $message,
            'date' => $date ? $date->format('d/m/Y H:i') : 'Data não informada',
            'date_sort' => $date ? $date->timestamp : 0,
        ];
    }

    private function toCarbon(mixed $date): ?Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance($date);
        }

        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }

    private function expectedCif(Licenciamento $licenciamento): float
    {
        return round(
            $this->number($licenciamento->fob_total)
            + $this->number($licenciamento->frete)
            + $this->number($licenciamento->seguro),
            2
        );
    }

    private function mercadorias(Licenciamento $licenciamento): Collection
    {
        return $licenciamento->relationLoaded('mercadorias')
            ? $licenciamento->mercadorias
            : collect();
    }

    private function mercadoriasAgrupadas(Licenciamento $licenciamento): Collection
    {
        return $licenciamento->relationLoaded('mercadoriasAgrupadas')
            ? $licenciamento->mercadoriasAgrupadas
            : collect();
    }

    private function faturas(Licenciamento $licenciamento): Collection
    {
        return $licenciamento->relationLoaded('procLicenFaturas')
            ? $licenciamento->procLicenFaturas
            : collect();
    }

    private function number(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }

    private function filled(mixed $value): bool
    {
        return trim((string) ($value ?? '')) !== '';
    }
}
