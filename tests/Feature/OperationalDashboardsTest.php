<?php

namespace Tests\Feature;

use App\Domains\Licenciamento\Services\EstadoLicenciamentoService;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnsureModuleEntitlement;
use App\Services\Dashboard\OperationalAnalytics;
use App\Services\Dashboard\OperationalFilters;
use App\Services\Dashboard\OperationalReport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Licenciamento\LicenciamentoTestSupport;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class OperationalDashboardsTest extends TestCase
{
    use DatabaseTransactions, LicenciamentoTestSupport, ProcessoTestFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('V1_TEST_DATABASE')) {
            throw new \RuntimeException('Use the isolated dashboard runner.');
        }
        Carbon::setTestNow('2026-10-11 12:00:00');
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function filters(array $values = []): OperationalFilters
    {
        return OperationalFilters::fromRequest(Request::create('/', 'GET', array_merge(['period' => 'custom', 'start' => '2026-10-01', 'end' => '2026-10-11'], $values)));
    }

    private function reportUrl(array $args): string
    {
        $kind = $args['kind'];
        unset($args['kind']);
        return route($kind === 'processes' ? 'processos.analytics-report' : 'licenciamentos.analytics-report', $args);
    }

    private function scenario(string $suffix = 'DASH'): array
    {
        [$user,$company] = $this->createTenant($suffix);
        $this->signInTenant($user);
        $this->grantTenantPermissions($user, ['processos.view', 'licenciamentos.view']);
        [$office,$type] = $this->createLookupData();
        $customer = $this->createCustomer($company, $user, $suffix);
        $exporter = $this->createExportador($company, $user, $suffix);

        return [$user, $company, $customer, $exporter, $office, $type];
    }

    private function process(array $s, array $overrides = [])
    {
        [$user,$company,$customer,$exporter,$office,$type] = $s;

        return $this->createProcesso($company, $user, $customer, $exporter, $office, $type, array_merge(['Moeda' => 'AOA', 'ValorTotal' => 100, 'peso_bruto' => 10], $overrides));
    }

    private function snapshot(array $s, string $kind = 'processes', array $filters = []): array
    {
        return app(OperationalAnalytics::class)->snapshot($kind, $s[1]->id, $this->filters($filters));
    }

    public function test_entry_exit_portfolio_and_cohort_are_distinct_populations(): void
    {
        $s = $this->scenario();
        $this->process($s, ['DataAbertura' => '2026-09-20', 'DataFecho' => '2026-10-05', 'Estado' => 'Finalizado']);
        $this->process($s, ['DataAbertura' => '2026-10-01', 'DataFecho' => '2026-10-03', 'Estado' => 'Finalizado']);
        $this->process($s, ['DataAbertura' => '2026-10-02', 'Estado' => 'Aguardando documentos']);
        $this->process($s, ['DataAbertura' => '2026-08-01']);
        $data = $this->snapshot($s);
        self::assertSame(2, $data['kpis']['opened']);
        self::assertSame(2, $data['kpis']['closed']);
        self::assertSame(2, $data['kpis']['portfolio']);
        self::assertSame(1, $data['kpis']['awaiting']);
        self::assertEquals(50, $data['kpis']['cohort_rate']);
        self::assertEquals(8.5, $data['kpis']['duration']);
        self::assertSame(2, $data['extra']['duration_sample']);
        $client = app(OperationalAnalytics::class)->rows('processes', $s[1]->id, $this->filters(), 'clients')->first();
        self::assertEquals(2, $client->entries);
        self::assertEquals(2, $client->completed);
        self::assertEquals(2, $client->active);
        $this->process($s, ['DataAbertura'=>null]);
        Cache::flush();
        $data=$this->snapshot($s);
        self::assertSame(1,$data['extra']['age_excluded']);
        self::assertSame(2,$data['extra']['age_sample']);
    }

    public function test_missing_and_reversed_dates_are_excluded_from_duration_and_reported(): void
    {
        $s = $this->scenario();
        $this->process($s, ['DataAbertura' => null, 'DataFecho' => '2026-10-04', 'Estado' => 'Finalizado']);
        $this->process($s, ['DataAbertura' => '2026-10-08', 'DataFecho' => '2026-10-04', 'Estado' => 'Finalizado']);
        $data = $this->snapshot($s);
        self::assertSame(2, $data['kpis']['closed']);
        self::assertNull($data['kpis']['duration']);
        self::assertSame(2, $data['extra']['duration_excluded']);
        $quality = app(OperationalAnalytics::class)->rows('processes', $s[1]->id, $this->filters(), 'quality')->get();
        self::assertCount(2, $quality);
        self::assertStringContainsString('inconsistente', $quality->pluck('issue')->implode(' '));
    }

    public function test_monetary_totals_never_mix_currencies_and_missing_weight_is_not_zero(): void
    {
        $s = $this->scenario();
        $this->process($s, ['Moeda' => 'AOA', 'ValorTotal' => 100, 'peso_bruto' => null]);
        $this->process($s, ['Moeda' => 'USD', 'ValorTotal' => 20, 'peso_bruto' => 0]);
        $this->process($s, ['Moeda' => 'USD', 'ValorTotal' => null, 'peso_bruto' => -1]);
        $data = $this->snapshot($s);
        $money = collect($data['money'])->keyBy('currency');
        self::assertCount(2, $money);
        self::assertEquals(100, $money['AOA']->declared);
        self::assertEquals(20, $money['USD']->declared);
        self::assertEquals(1, $money['USD']->declared_sample);
        self::assertEquals(1, $data['weight']['sample']);
        self::assertEquals(0, $data['weight']['total']);
        $this->process($s, ['Moeda'=>null, 'ValorTotal'=>999]);
        Cache::flush();
        $unknown=collect($this->snapshot($s)['money'])->first(fn($row)=>$row->currency==='Não indicada');
        self::assertNull($unknown->declared);
        self::assertEquals(0,$unknown->declared_sample);
    }

    public function test_tenants_filters_and_cache_keys_do_not_leak_other_companies(): void
    {
        $a = $this->scenario('A');
        $b = $this->scenario('B');
        $this->process($a);
        $this->process($b, ['ValorTotal' => 999]);
        self::assertSame(1, $this->snapshot($a)['count']);
        self::assertSame(1, $this->snapshot($b)['count']);
        self::assertSame(0, $this->snapshot($a, 'processes', ['customer' => $b[2]->id])['count']);
        $options = app(OperationalAnalytics::class)->options('processes', $a[1]->id, $this->filters());
        self::assertCount(1, $options['customer']);
        self::assertEquals($a[2]->id, $options['customer'][0]->value);
        $this->process($a, ['customer_id' => $b[2]->id]);
        Cache::flush();
        $rows = app(OperationalAnalytics::class)->rows('processes', $a[1]->id, $this->filters(), 'oldest')->get();
        self::assertContains('Cliente indisponível', $rows->pluck('customer')->all());
        self::assertNotContains($b[2]->CompanyName, $rows->pluck('customer')->all());
    }

    public function test_dimensions_apply_to_all_snapshots_and_report_details(): void
    {
        $s = $this->scenario();
        $this->process($s, ['TipoTransporte' => 3]);
        $this->process($s, ['TipoTransporte' => 4]);
        $f = $this->filters(['transport' => 3, 'customer' => $s[2]->id, 'type' => $s[5], 'office' => $s[4]]);
        $service = app(OperationalAnalytics::class);
        $data = $service->snapshot('processes', $s[1]->id, $f);
        self::assertSame(1, $data['count']);
        self::assertCount(1, $service->rows('processes', $s[1]->id, $f, 'oldest')->get());
        $report = iterator_to_array(app(OperationalReport::class)->rows('processes', $s[1], $f, 'portfolio', $data));
        self::assertEquals(1, collect($report)->first(fn ($r) => $r[0] === 'Abertos no período')[1]);
    }

    public function test_months_are_year_qualified_and_empty_intervals_are_zero_filled(): void
    {
        $s = $this->scenario();
        $this->process($s, ['DataAbertura' => '2025-12-31']);
        $this->process($s, ['DataAbertura' => '2026-01-01']);
        $data = $this->snapshot($s, 'processes', ['start' => '2025-12-01', 'end' => '2026-02-28']);
        self::assertSame(['2025-12', '2026-01', '2026-02'], array_column($data['charts']['timeline'], 'month'));
        self::assertSame([1, 1, 0], array_column($data['charts']['timeline'], 'entries'));
    }

    public function test_empty_period_has_null_means_and_no_artificial_percentage(): void
    {
        $s = $this->scenario();
        $data = $this->snapshot($s);
        self::assertSame(0, $data['count']);
        self::assertNull($data['kpis']['duration']);
        self::assertNull($data['comparison']['change']);
        self::assertNull($data['kpis']['cohort_rate']);
        self::assertNull($data['weight']['mean']);
    }

    public function test_legacy_states_are_not_guessed_as_finalized_or_active(): void
    {
        $s = $this->scenario();
        $this->process($s, ['Estado' => 'Concluído']);
        $data = $this->snapshot($s);
        self::assertSame(0, $data['kpis']['closed']);
        self::assertSame(0, $data['kpis']['portfolio']);
        self::assertStringContainsString('Estado não reconhecido', app(OperationalAnalytics::class)->rows('processes', $s[1]->id, $this->filters(), 'quality')->first()->issue);
    }

    public function test_license_states_match_service_and_do_not_use_own_invoice_status(): void
    {
        $s = $this->scenario();
        $license = $this->createLicenciamentoFor($s[1], $s[0], 'STATE');
        $license->update(['txt_gerado' => 1, 'status_fatura' => 'paga']);
        $data = $this->snapshot($s, 'licenses');
        self::assertSame(1, $data['kpis']['licensing']);
        self::assertSame(0, $data['kpis']['licensed']);
        DB::table('proc_licen_sales')->insert(['empresa_id' => $s[1]->id, 'licenciamento_id' => $license->id, 'fatura_id' => 1, 'status_fatura' => 'paga']);
        Cache::flush();
        $data = $this->snapshot($s, 'licenses');
        self::assertSame(1, $data['kpis']['licensed']);
        self::assertSame('Licenciado', app(EstadoLicenciamentoService::class)->estado($license->fresh()));
    }

    public function test_license_midnight_uses_angola_and_groups_by_year_and_month(): void
    {
        $s = $this->scenario();
        $license = $this->createLicenciamentoFor($s[1], $s[0], 'YEAR');
        DB::table('licenciamentos')->where('id', $license->id)->update(['created_at' => '2025-12-31 23:30:00']);
        $data = $this->snapshot($s, 'licenses', ['start' => '2026-01-01', 'end' => '2026-01-31']);
        self::assertSame(1, $data['count']);
        self::assertSame('2026-01', $data['charts']['timeline'][0]['month']);
        self::assertSame(0, $this->snapshot($s, 'licenses', ['start' => '2025-12-01', 'end' => '2025-12-31'])['count']);
    }

    public function test_association_requires_a_same_company_existing_process(): void
    {
        $s = $this->scenario();
        $other = $this->scenario('OTHER');
        $process = $this->process($other);
        $license = $this->createLicenciamentoFor($s[1], $s[0], 'LINK');
        DB::table('licenciamento_processos')->insert(['empresa_id' => $s[1]->id, 'licenciamento_id' => $license->id, 'processo_id' => $process->id]);
        self::assertSame(0, $this->snapshot($s, 'licenses')['kpis']['linked']);
        $process = $this->process($s);
        DB::table('licenciamento_processos')->where('licenciamento_id', $license->id)->update(['processo_id' => $process->id]);
        Cache::flush();
        $data = $this->snapshot($s, 'licenses');
        self::assertSame(1, $data['kpis']['linked']);
        self::assertEquals(100, $data['extra']['link_rate']);
    }

    public function test_dashboard_and_exports_enforce_permissions_and_ignore_browser_company(): void
    {
        $s = $this->scenario();
        $this->process($s);
        $this->withoutMiddleware([CheckSubscription::class, EnsureModuleEntitlement::class]);
        $this->get(route('processos.estatistica'))->assertOk()->assertViewHas('analytics', fn ($a) => $a['count'] === 1);
        $this->get(route('processos.estatistica', ['empresa_id' => 999]))->assertForbidden();
        $this->get($this->reportUrl(['kind' => 'processes', 'report' => 'executive', 'format' => 'csv', 'empresa_id' => 999]))->assertForbidden();
        $s[0]->syncPermissions([]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->get(route('processos.estatistica'))->assertForbidden();
        $this->get($this->reportUrl(['kind' => 'processes', 'report' => 'executive', 'format' => 'csv']))->assertForbidden();
    }

    public function test_csv_xlsx_pdf_totals_and_formula_protection(): void
    {
        $s = $this->scenario();
        $s[1]->update(['Empresa' => '=HYPERLINK("bad")']);
        $s[2]->update(['CompanyName' => " \t=SUM(1,1)"]);
        $this->process($s);
        $this->withoutMiddleware([CheckSubscription::class, EnsureModuleEntitlement::class]);
        $args = ['kind' => 'processes', 'report' => 'clients'];
        $csv = $this->get($this->reportUrl($args + ['format' => 'csv']))->assertOk()->streamedContent();
        self::assertStringContainsString("'=HYPERLINK", $csv);
        self::assertStringContainsString("' ", $csv);
        self::assertStringContainsString('Abertos no período', $csv);
        $csvRows = array_map(fn ($line) => str_getcsv($line, ';', '"', ''), explode("\n", $csv));
        self::assertSame('1', collect($csvRows)->first(fn ($r) => ($r[0] ?? '') === 'Abertos no período')[1]);
        $xlsx = $this->get($this->reportUrl($args + ['format' => 'xlsx']))->assertOk();
        self::assertStringContainsString('spreadsheet', $xlsx->headers->get('Content-Type'));
        $sheet = IOFactory::load($xlsx->baseResponse->getFile()->getPathname())->getActiveSheet();
        self::assertSame("'=HYPERLINK(\"bad\")", $sheet->getCell('B2')->getValue());
        self::assertSame(DataType::TYPE_STRING, $sheet->getCell('B2')->getDataType());
        self::assertEquals(1, collect($sheet->toArray())->first(fn ($r) => $r[0] === 'Abertos no período')[1]);
        $pdf = $this->get($this->reportUrl($args + ['format' => 'pdf']))->assertOk();
        self::assertStringContainsString('pdf', $pdf->headers->get('Content-Type'));
        self::assertSame("'\t@SUM(1)", OperationalReport::safeCell("\t@SUM(1)"));
    }

    public function test_report_detail_is_not_limited_by_dashboard_pagination(): void
    {
        $s = $this->scenario();
        for ($i = 0; $i < 21; $i++) {
            $this->process($s, ['TipoTransporte' => $i + 1]);
        }
        $service = app(OperationalAnalytics::class);
        $f = $this->filters();
        $snapshot = $service->snapshot('processes', $s[1]->id, $f);
        $paged = $service->rows('processes', $s[1]->id, $f, 'oldest')->orderBy('r.id')->paginate(15);
        self::assertCount(15, $paged->items());
        self::assertSame(21, $paged->total());
        $rows = iterator_to_array(app(OperationalReport::class)->rows('processes', $s[1], $f, 'portfolio', $snapshot));
        self::assertCount(21, array_filter($rows, fn ($r) => isset($r[1]) && is_string($r[1]) && str_starts_with($r[1], 'PROC-')));
        self::assertEquals(21, $service->dimensionRows('processes', $s[1]->id, $f, 'transport')->get()->sum('entries'));
    }

    public function test_dashboard_views_render_tables_graphs_filters_and_export_routes(): void
    {
        $s = $this->scenario();
        $this->process($s);
        $this->createLicenciamentoFor($s[1], $s[0], 'VIEW');
        $this->withoutMiddleware([CheckSubscription::class, EnsureModuleEntitlement::class]);
        foreach (['processos.estatistica' => 'processes', 'licenciamento.estatistica' => 'licenses'] as $route => $kind) {
            $response = $this->get(route($route))->assertOk()->assertSee('Resumo executivo')->assertSee('Relatórios');
            file_put_contents(storage_path('app/dashboard-preview-'.$kind.'.html'), $response->getContent());
            $filtered = $this->get(route($route, ['period' => 'custom', 'start' => '2025-01-01', 'end' => '2025-02-28']))->assertOk();
            file_put_contents(storage_path('app/dashboard-preview-'.$kind.'-filtered.html'), $filtered->getContent());
            $this->get(route($route, ['table' => 'clients']))->assertOk();
            $this->get(route($route, ['table' => 'quality']))->assertOk();
            foreach (['country', 'transport', 'office'] as $dimension) {
                $this->get(route($route,['table' => $dimension]))->assertOk();
            }
        }
    }

    public function test_invalid_ranges_and_foreign_filters_are_rejected(): void
    {
        $s = $this->scenario();
        $this->process($s);
        $this->withoutMiddleware([CheckSubscription::class, EnsureModuleEntitlement::class]);
        $this->getJson(route('processos.estatistica',['period' => 'custom', 'start' => '2026-10-10', 'end' => '2026-10-01']))->assertUnprocessable();
        $this->getJson(route('processos.estatistica',['customer' => 9999]))->assertUnprocessable();
        $this->getJson(route('processos.estatistica',['period' => 'custom', 'start' => '2026-01-01', 'end' => '2027-01-01']))->assertUnprocessable();
    }

    public function test_reports_preserve_the_existing_module_entitlement_guard(): void
    {
        $this->scenario();
        $this->withoutMiddleware(CheckSubscription::class);
        foreach (['processes','licenses'] as $kind) {
            $this->get($this->reportUrl(['kind'=>$kind,'report'=>'executive','format'=>'csv']))->assertForbidden();
        }
    }
}
