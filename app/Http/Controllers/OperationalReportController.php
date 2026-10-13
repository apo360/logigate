<?php

namespace App\Http\Controllers;

use App\Exports\OperationalDashboardExport;
use App\Models\Licenciamento;
use App\Models\Processo;
use App\Services\Dashboard\OperationalAnalytics;
use App\Services\Dashboard\OperationalFilters;
use App\Services\Dashboard\OperationalReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

final class OperationalReportController extends AuthenticatedController
{
    public function __invoke(Request $request, string $report, string $format)
    {
        $kind = $request->route('kind');
        abort_unless(in_array($kind, ['processes', 'licenses']) && in_array($format, ['csv', 'xlsx', 'pdf']) && isset(OperationalReport::catalog($kind)[$report]), 404);
        Gate::authorize('viewAny', $kind === 'processes' ? Processo::class : Licenciamento::class);
        $filters = OperationalFilters::fromRequest($request);
        $service = app(OperationalAnalytics::class);
        $options = $service->options($kind, (int) $this->empresa->id, $filters);
        foreach (['customer', 'type', 'transport', 'country', 'office'] as $dimension) {
            if (isset($filters->values[$dimension]) && ! collect($options[$dimension])->contains(fn ($item) => (string) $item->value === (string) $filters->values[$dimension])) {
                abort(422, 'Filtro indisponível na empresa activa.');
            }
        }
        $snapshot = $service->snapshot($kind, (int) $this->empresa->id, $filters);
        $rows = fn () => app(OperationalReport::class)->rows($kind, $this->empresa, $filters, $report, $snapshot);
        $filename = 'logigate-'.$kind.'-'.$report.'-'.$filters->reference();
        if ($format === 'xlsx') {
            return Excel::download(new OperationalDashboardExport($rows), $filename.'.xlsx');
        }
        if ($format === 'csv') {
            return response()->streamDownload(function () use ($rows) {
                $stream = fopen('php://output', 'w');
                fwrite($stream, "\xEF\xBB\xBF");
                foreach ($rows() as $row) {
                    fputcsv($stream, array_map(OperationalReport::safeCell(...), $row), ';', '"', '');
                }
                fclose($stream);
            }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
        }
        // PDF is intentionally bounded; CSV/Excel include all detailed rows via lazy queries.
        $pdfRows = [];
        $truncated = false;
        foreach ($rows() as $row) {
            if (count($pdfRows) >= 500) {
                $truncated = true;
                break;
            }$pdfRows[] = $row;
        }

        return Pdf::loadView('reports.operational', ['rows' => $pdfRows, 'truncated' => $truncated, 'title' => OperationalReport::catalog($kind)[$report]])->setPaper('a4','landscape')->download($filename.'.pdf');
    }
}
