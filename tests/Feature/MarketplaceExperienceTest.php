<?php

namespace Tests\Feature;

use App\Application\Marketplace\MarketplaceExperience;
use App\Models\MarketplaceProfile;
use App\Models\PautaAduaneira;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\IsolatedDatabaseTestCase;

class MarketplaceExperienceTest extends IsolatedDatabaseTestCase
{
    private MarketplaceExperience $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-05 12:00:00'));
        config(['marketplace.catalogue_revision' => 'reviewed-test-v1', 'session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array']);
        Http::preventStrayRequests(); Mail::fake();
        $this->service = app(MarketplaceExperience::class);
        DB::table('empresas')->insert([['id' => 1, 'Designacao' => 'Despachante Oficial', 'ativo' => 1], ['id' => 2, 'Designacao' => 'Outro', 'ativo' => 1]]);
        DB::table('pauta_aduaneira')->insert(['id' => 1, 'codigo' => '0203.11.00', 'descricao' => 'Mercadoria de teste']);
    }

    private function profile(array $attributes = []): MarketplaceProfile
    {
        return MarketplaceProfile::create(array_merge(['empresa_id' => 1, 'public_name' => 'Prestador fictício', 'service_provider' => true, 'consent_reference' => 'fixture-authorisation', 'history_authorized' => true, 'history_reviewed_at' => now()], $attributes));
    }

    private function operation(int $id, array $attributes = [], array $goods = []): void
    {
        DB::table('processos')->insert(array_merge(['id' => $id, 'empresa_id' => 1, 'customer_id' => 2, 'Estado' => 'Finalizado', 'DataAbertura' => '2026-08-01', 'DataFecho' => '2026-08-15'], $attributes));
        DB::table('mercadorias')->insert(array_merge(['Fk_Importacao' => $id, 'pauta_aduaneira_id' => 1, 'codigo_pautal_snapshot' => '0203.11.00', 'pauta_snapshot_at' => '2026-08-01'], $goods));
    }

    public function test_projection_counts_distinct_eligible_processes_and_never_licenses_or_customer_as_provider(): void
    {
        $profile = $this->profile(['published' => true]);
        $this->operation(1, [], ['licenciamento_id' => 10]);
        DB::table('mercadorias')->insert(['Fk_Importacao' => 1, 'licenciamento_id' => 10, 'pauta_aduaneira_id' => 1, 'codigo_pautal_snapshot' => '0203.11.00', 'pauta_snapshot_at' => '2026-08-01']);
        $this->operation(2, ['DataFecho' => '2026-09-15']);
        $this->operation(3, ['Estado' => 'Cancelado']);
        $this->operation(4, ['Estado' => 'Aberto']);
        $this->operation(5, ['empresa_id' => 2]);
        $this->operation(6, ['DataFecho' => null]);
        $this->operation(7, ['DataFecho' => '2025-09-30', 'DataAbertura' => '2025-01-01']);
        $this->operation(8, ['DataFecho' => '2026-10-01']);
        $this->operation(9, ['DataFecho' => '2026-07-01']); // inverted
        $this->operation(10, [], ['codigo_pautal_snapshot' => '9999']);
        $this->operation(11, [], ['pauta_snapshot_at' => null]);
        $this->operation(12, ['deleted_at' => now()]);
        $this->service->refresh($profile);
        $this->service->refresh($profile); // idempotent
        $item = $this->service->selection(1, '0203.11.00');
        $result = $this->service->search($item, [])['profiles']->first();
        self::assertSame(2, (int) $result->operations);
        self::assertSame(2, (int) $result->active_months);
        self::assertSame('2026-09-15', $result->last_operation);
        self::assertSame(2, DB::table('marketplace_activity_months')->count());
        self::assertSame('0203.11.00', $item->codigo);
    }

    public function test_hidden_revoked_stale_and_inactive_profiles_do_not_disclose_history(): void
    {
        $profile = $this->profile(); $this->operation(1); $this->service->refresh($profile);
        $item = PautaAduaneira::find(1);
        self::assertCount(0, $this->service->search($item, [])['profiles']);
        $profile->update(['published' => true]);
        self::assertCount(1, $this->service->search($item, [])['profiles']);
        $profile->update(['published' => false]);
        self::assertCount(0, $this->service->search($item, [])['profiles']);
        $profile->update(['published' => true, 'calculated_at' => now()->subDays(2)]);
        self::assertCount(0, $this->service->search($item, [])['profiles']);
        $profile->update(['calculated_at' => now()]); DB::table('empresas')->where('id', 1)->update(['ativo' => 0]);
        self::assertCount(0, $this->service->search($item, [])['profiles']);
    }

    public function test_declared_specialty_is_separate_and_unknown_history_is_null(): void
    {
        $profile = $this->profile(['published' => true, 'history_authorized' => false]);
        DB::table('marketplace_specialties')->insert(['profile_id' => $profile->id, 'pauta_id' => 1, 'codigo' => '0203.11.00']);
        $result = $this->service->search(PautaAduaneira::find(1), [])['profiles']->first();
        self::assertNull($result->operations);
        self::assertCount(0, $this->service->search(PautaAduaneira::find(1), ['recurrence' => 1])['profiles']);
        $this->get('/mercado?pauta_id=1&codigo=0203.11.00')->assertOk()->assertSee('Especialidade declarada')->assertDontSee('dias corridos');
    }

    public function test_shared_query_contract_filters_privacy_and_context(): void
    {
        $profile = $this->profile(['published' => true, 'public_location' => 'Luanda']); $this->operation(1); $this->service->refresh($profile);
        $api = $this->getJson('/mercado/guia?pauta_id=1&codigo=0203.11.00')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $api->assertJsonPath('selection.codigo', '0203.11.00')->assertJsonPath('profiles.0.public_name', 'Prestador fictício');
        self::assertSame(['id', 'public_name', 'public_location', 'operations', 'active_months', 'last_operation'], array_keys($api->json('profiles.0')));
        $this->get($api->json('marketplace_url'))->assertOk()->assertSee('Prestador fictício')->assertSee('pauta_id=1', false)->assertSee('codigo=0203.11.00', false);
        $this->getJson('/mercado/guia?pauta_id=1&codigo=9999')->assertUnprocessable();
        $this->getJson('/mercado/guia?codigo[]=123')->assertUnprocessable();
        self::assertCount(0, $this->service->search(PautaAduaneira::find(1), ['location' => 'Outra'])['profiles']);
        config(['marketplace.catalogue_revision' => 'different-version']);
        self::assertCount(0, $this->service->search(PautaAduaneira::find(1), [])['profiles']);
        Mail::assertNothingSent();
    }

    public function test_no_prefix_broadening_and_stable_ranking(): void
    {
        $first = $this->profile(['published' => true]); $this->operation(1); $this->service->refresh($first);
        DB::table('empresas')->insert(['id' => 3, 'Designacao' => 'Praticante', 'ativo' => 1]);
        $second = $this->profile(['empresa_id' => 3, 'published' => true]);
        $this->operation(2, ['empresa_id' => 3]); $this->service->refresh($second);
        self::assertSame([$first->id, $second->id], $this->service->search(PautaAduaneira::find(1), [])['profiles']->pluck('id')->all());
        DB::table('pauta_aduaneira')->insert(['id' => 2, 'codigo' => '0203', 'descricao' => 'Categoria sem hierarquia comprovada']);
        self::assertCount(0, $this->service->search(PautaAduaneira::find(2), [])['profiles']);
    }

    public function test_authorised_command_defaults_hidden_and_withdraws_without_operational_writes(): void
    {
        $this->artisan('marketplace:profile', ['empresa' => 1, '--name' => 'Nome público fictício', '--provider' => true, '--consent' => 'fixture', '--specialties' => '1'])->assertSuccessful();
        $profile = MarketplaceProfile::where('empresa_id', 1)->firstOrFail();
        self::assertFalse($profile->published); self::assertFalse($profile->history_authorized);
        $this->expectException(\LogicException::class);
        $this->service->refresh($profile);
    }

    public function test_month_filter_ranking_and_return_preserve_supported_context(): void
    {
        $first = $this->profile(['published' => true, 'public_location' => 'Luanda']);
        $this->operation(1); $this->service->refresh($first);
        DB::table('empresas')->insert(['id' => 3, 'Designacao' => 'Praticante', 'ativo' => 1]);
        $second = $this->profile(['empresa_id' => 3, 'published' => true, 'public_location' => 'Luanda']);
        $this->operation(2, ['empresa_id' => 3, 'DataFecho' => '2026-08-10']);
        $this->operation(3, ['empresa_id' => 3, 'DataFecho' => '2026-09-10']);
        $this->service->refresh($second);
        $item = PautaAduaneira::find(1);
        self::assertSame($second->id, $this->service->search($item, [])['profiles']->first()->id);
        self::assertCount(1, $this->service->search($item, ['months' => 1])['profiles']);
        self::assertCount(1, $this->service->search($item, ['recurrence' => 2])['profiles']);
        $response = $this->getJson('/mercado/guia?pauta_id=1&codigo=0203.11.00&months=3&location=Luanda&recurrence=2')->assertOk();
        self::assertSame($second->id, $response->json('profiles.0.id'));
        self::assertStringContainsString('months=3', $response->json('marketplace_url'));
        $this->get($response->json('marketplace_url'))->assertOk()->assertSee('months=3', false)->assertSee('recurrence=2', false);
    }

    public function test_description_search_guides_real_selection_without_matching_every_code(): void
    {
        DB::table('pauta_aduaneira')->insert(['id' => 2, 'codigo' => '9999.11.00', 'descricao' => 'Outro artigo sem correspondência']);
        $this->get('/mercado?q=Mercadoria')->assertOk()->assertSee('Mercadoria de teste')->assertDontSee('Outro artigo sem correspondência');
        $this->get('/mercado?q=0203')->assertOk()->assertSee('0203.11.00')->assertDontSee('9999.11.00');
    }
}
