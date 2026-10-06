<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\IsolatedDatabaseTestCase;

class PublicPautaTest extends IsolatedDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array']);
        Http::preventStrayRequests();
        Mail::fake();
        foreach (range(1, 25) as $id) {
            DB::table('pauta_aduaneira')->insert([
                'id' => $id, 'codigo' => sprintf('0203.%02d.00', $id), 'descricao' => 'Mercadoria de demonstração '.$id,
                'iva' => $id === 1 ? null : '0', 'ieq' => '14', 'rg' => '5', 'sadc' => 'N/A', 'uq' => 'kg', 'updated_at' => '2026-01-02 10:00:00',
            ]);
        }
        DB::table('pauta_aduaneira')->insert(['id' => 26, 'codigo' => '9999.00.00', 'descricao' => 'Outro artigo']);
    }

    public function test_description_has_true_total_pagination_and_cache_size_identity(): void
    {
        $this->getJson('/api/v1/pauta/busca?q=Mercadoria&tipo=descricao&limit=10&page=2')->assertOk()
            ->assertJsonPath('meta.total', 25)->assertJsonPath('meta.shown', 10)->assertJsonPath('meta.current_page', 2)->assertJsonPath('meta.last_page', 3)->assertJsonCount(10, 'data');
        $this->getJson('/api/v1/pauta/busca?q=Mercadoria&tipo=descricao&limit=2&page=1')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.last_page', 13);
        $this->getJson('/api/v1/pauta?q=Mercadoria&tipo=auto&per_page=10&page=3')->assertOk()->assertJsonCount(5, 'data')->assertJsonPath('meta.total', 25);
    }

    public function test_suggestions_use_real_matching_description_identity_and_leading_zero(): void
    {
        $response = $this->getJson('/api/v1/pauta/sugestoes?termo=Mercadoria')->assertOk()->assertJsonCount(8, 'data');
        self::assertSame('0203.01.00', $response->json('data.0.codigo'));
        self::assertSame(1, $response->json('data.0.id'));
        $this->getJson('/api/v1/pauta/sugestoes?termo=0203.02&tipo=codigo')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 2);
        $this->getJson('/api/v1/pauta?q=0203.02&tipo=codigo')->assertOk()->assertJsonPath('data.0.codigo', '0203.02.00');
        $this->getJson('/api/v1/pauta?q=Mercadoria&tipo=codigo')->assertUnprocessable();
    }

    public function test_details_distinguish_missing_zero_and_text_rates_and_identify_source_gaps(): void
    {
        $response = $this->getJson('/api/v1/pauta/detalhes/1?codigo=0203.01.00')->assertOk()
            ->assertJsonPath('data.impostos.iva', null)->assertJsonPath('data.impostos.ieq', 14)
            ->assertJsonPath('data.sadc', 'N/A')->assertJsonPath('data.fonte.versao', null)->assertJsonPath('data.fonte.vigencia', null);
        self::assertSame('2026-01-02 10:00:00', $response->json('data.fonte.atualizacao_registo'));
        $this->getJson('/api/v1/pauta/detalhes/2')->assertOk()->assertJsonPath('data.impostos.iva', 0);
        $this->getJson('/api/v1/pauta/detalhes/1?codigo=9999')->assertUnprocessable();
        $this->getJson('/api/v1/pauta/detalhes/12345')->assertNotFound();
        $this->getJson('/api/v1/pauta/8888')->assertNotFound();
        $this->getJson('/api/v1/pauta/02030100')->assertOk()->assertJsonPath('data.codigo', '0203.01.00');
    }

    public function test_ambiguous_codes_require_explicit_identity_and_do_not_mix_versions(): void
    {
        DB::table('pauta_aduaneira')->insert(['id' => 27, 'codigo' => '0203.01.00', 'descricao' => 'Outra identidade, versão desconhecida']);
        $this->getJson('/api/v1/pauta/0203.01.00')->assertStatus(409);
        $this->getJson('/api/v1/pauta/detalhes/27?codigo=0203.01.00')->assertOk()->assertJsonPath('data.id', 27);
        $this->get('/consultar-pauta-aduaneira?pauta_id=27&codigo=0203.01.00')->assertOk()->assertSee('Outra identidade, versão desconhecida');
    }

    public function test_empty_search_and_empty_catalogue_are_distinct(): void
    {
        $this->getJson('/api/v1/pauta?q=inexistente&tipo=descricao')->assertOk()->assertJsonPath('meta.total', 0)->assertJsonPath('meta.catalogue_empty', false);
        $this->get('/consultar-pauta-aduaneira?q=inexistente')->assertOk()->assertSee('Não encontrámos mercadorias para esta pesquisa.');
        // Only disposable fixtures inside rollback transaction.
        DB::table('pauta_aduaneira')->delete();
        \Illuminate\Support\Facades\Cache::flush();
        $this->getJson('/api/v1/pauta')->assertOk()->assertJsonPath('meta.catalogue_empty', true);
    }

    public function test_server_rendered_search_details_and_marketplace_context_are_safe(): void
    {
        DB::table('pauta_aduaneira')->where('id', 1)->update(['descricao' => 'Mercadoria <script>window.privateLeak=1</script>']);
        $response = $this->get('/consultar-pauta-aduaneira?q=Mercadoria&tipo=descricao&per_page=10&page=2&pauta_id=1&codigo=0203.01.00')->assertOk()
            ->assertSee('Consulte a')->assertSee('25 resultados no total.')->assertSee('Descrição da mercadoria ou código pautal')
            ->assertSee('Não indicado na fonte')->assertSee('Ver todos no marketplace')->assertDontSee('<script>window.privateLeak=1</script>', false)->assertDontSee('pauta aduaneira angolana vigente');
        $guide = $this->getJson('/mercado/guia?pauta_id=1&codigo=0203.01.00&pauta_q=Mercadoria&pauta_tipo=descricao&pauta_page=2&pauta_per_page=10')->assertOk();
        $this->get($guide->json('marketplace_url'))->assertOk()->assertSee('q=Mercadoria', false)->assertSee('page=2', false)->assertSee('per_page=10', false);
        $this->getJson('/mercado/guia?pauta_id=1&mercadoria_descricao=outra')->assertUnprocessable();
        $guide->assertJsonCount(0, 'profiles');
        Mail::assertNothingSent();
    }

    public function test_invalid_parameters_do_not_reach_the_catalogue_query(): void
    {
        $this->getJson('/api/v1/pauta?q[]=Mercadoria')->assertUnprocessable();
        $this->getJson('/api/v1/pauta?page=-1')->assertUnprocessable();
        $this->getJson('/api/v1/pauta?codigo=....')->assertUnprocessable();
        $this->getJson('/api/v1/pauta?per_page=101')->assertUnprocessable();
        $this->get('/consultar-pauta-aduaneira?q=x')->assertUnprocessable()->assertSee('value="x"', false)->assertSee('Não foi possível consultar os resultados.');
    }

    public function test_source_errors_do_not_expose_internal_details(): void
    {
        $repository = \Mockery::mock(\App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface::class);
        $repository->shouldReceive('search')->andThrow(new \Illuminate\Database\QueryException('mysql', 'internal-private-query', [], new \RuntimeException('internal-secret')));
        app()->instance(\App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface::class, $repository);
        $this->getJson('/api/v1/pauta?q=novo')->assertStatus(503)->assertDontSee('internal-secret')->assertDontSee('internal-private-query');
        $this->get('/consultar-pauta-aduaneira?q=novo')->assertStatus(503)->assertDontSee('internal-secret')->assertSee('temporariamente indisponível');
    }

    public function test_guide_first_page_is_independent_of_pauta_result_pagination(): void
    {
        foreach (range(1, 4) as $id) {
            DB::table('empresas')->insert(['id' => $id, 'Designacao' => 'Praticante', 'ativo' => 1]);
            DB::table('marketplace_profiles')->insert(['id' => $id, 'empresa_id' => $id, 'public_name' => 'Prestador fictício '.$id, 'service_provider' => true, 'published' => true, 'consent_reference' => 'fixture']);
            DB::table('marketplace_specialties')->insert(['profile_id' => $id, 'pauta_id' => 1, 'codigo' => '0203.01.00']);
        }
        $this->get('/consultar-pauta-aduaneira?q=Mercadoria&page=2&per_page=10&pauta_id=1&codigo=0203.01.00')->assertOk()
            ->assertSee('Prestador fictício 1')->assertSee('Prestador fictício 3')->assertDontSee('Prestador fictício 4');
    }

    public function test_description_wildcards_are_literal_and_queries_remain_parameterized(): void
    {
        DB::table('pauta_aduaneira')->insert([['id' => 27, 'codigo' => '0203.27.00', 'descricao' => 'Taxa_15% literal'], ['id' => 28, 'codigo' => '0203.28.00', 'descricao' => 'TaxaX15Y outra']]);
        $this->getJson('/api/v1/pauta?q='.urlencode('Taxa_15%').'&tipo=descricao')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 27);
        $this->getJson('/api/v1/pauta?q='.urlencode("' OR 1=1 --").'&tipo=descricao')->assertOk()->assertJsonPath('meta.total', 0);
    }
}
