<?php

namespace Tests\Feature;

use App\Application\PautaAduaneira\Actions\AssociarPautaMercadoriaAction;
use App\Application\PautaAduaneira\Actions\CalcularTaxasPautaAction;
use App\Application\PautaAduaneira\Actions\CalcularTributacaoProcessoAction;
use App\Application\PautaAduaneira\DTOs\CalculoPautaDTO;
use App\Application\PautaAduaneira\IA\PautaSuggestionDTO;
use App\Application\PautaAduaneira\IA\SugerirCodigoPautalAction;
use App\Application\PautaAduaneira\Services\PautaSearchService;
use App\Models\Mercadoria;
use App\Models\MercadoriaPautaAudit;
use App\Models\PautaAduaneira;
use App\Models\Processo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PautaAduaneiraTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_by_exact_codigo_with_dots(): void
    {
        $pauta = $this->createPauta(['codigo' => '0203.11.00']);

        $results = app(PautaSearchService::class)->search(['q' => '0203.11.00'], 10);

        $this->assertTrue($results->contains('id', $pauta->id));
    }

    public function test_search_by_codigo_without_dots(): void
    {
        $pauta = $this->createPauta(['codigo' => '0203.11.00']);

        $results = app(PautaSearchService::class)->search(['q' => '02031100'], 10);

        $this->assertTrue($results->contains('id', $pauta->id));
    }

    public function test_search_by_descricao(): void
    {
        $pauta = $this->createPauta(['descricao' => 'Carnes de bovino congeladas']);

        $results = app(PautaSearchService::class)->search(['q' => 'bovino'], 10);

        $this->assertTrue($results->contains('id', $pauta->id));
    }

    public function test_calculate_using_rg(): void
    {
        $pauta = $this->createPauta(['rg' => 10, 'sadc' => 5, 'ua' => 2, 'iva' => 14, 'ieq' => 1]);

        $result = $this->calculate($pauta->id, 'rg');

        $this->assertSame(10.0, $result['taxa_aplicada']);
        $this->assertSame(100.0, $result['direitos_importacao']);
    }

    public function test_calculate_using_sadc(): void
    {
        $pauta = $this->createPauta(['rg' => 10, 'sadc' => 5, 'ua' => 2, 'iva' => 14, 'ieq' => 1]);

        $result = $this->calculate($pauta->id, 'sadc');

        $this->assertSame(5.0, $result['taxa_aplicada']);
        $this->assertSame(50.0, $result['direitos_importacao']);
    }

    public function test_calculate_using_ua(): void
    {
        $pauta = $this->createPauta(['rg' => 10, 'sadc' => 5, 'ua' => 2, 'iva' => 14, 'ieq' => 1]);

        $result = $this->calculate($pauta->id, 'ua');

        $this->assertSame(2.0, $result['taxa_aplicada']);
        $this->assertSame(20.0, $result['direitos_importacao']);
    }

    public function test_calculate_iva(): void
    {
        $pauta = $this->createPauta(['rg' => 10, 'iva' => 14, 'ieq' => 1]);

        $result = $this->calculate($pauta->id, 'rg');

        $this->assertSame(155.4, $result['iva']);
    }

    public function test_calculate_ieq(): void
    {
        $pauta = $this->createPauta(['rg' => 10, 'iva' => 14, 'ieq' => 1]);

        $result = $this->calculate($pauta->id, 'rg');

        $this->assertSame(10.0, $result['ieq']);
    }

    public function test_associate_pauta_with_mercadoria(): void
    {
        $pauta = $this->createPauta(['codigo' => '8703.21.00']);
        $mercadoria = $this->createMercadoria();

        $updated = app(AssociarPautaMercadoriaAction::class)->execute($mercadoria->id, $pauta->id);

        $this->assertSame($pauta->id, $updated->pauta_aduaneira_id);
        $this->assertSame('8703.21.00', $updated->codigo_aduaneiro);
    }

    public function test_snapshot_values_into_mercadoria(): void
    {
        $pauta = $this->createPauta([
            'codigo' => '8407.10.00',
            'descricao' => 'Motores de aeronaves',
            'rg' => 7,
            'sadc' => 3,
            'ua' => 1,
            'iva' => 14,
            'ieq' => 2,
        ]);
        $mercadoria = $this->createMercadoria();

        $updated = app(AssociarPautaMercadoriaAction::class)->execute($mercadoria->id, $pauta->id);

        $this->assertSame('8407.10.00', $updated->codigo_pautal_snapshot);
        $this->assertSame('Motores de aeronaves', $updated->descricao_pautal_snapshot);
        $this->assertSame('7', (string) $updated->rg_snapshot);
        $this->assertSame('3', (string) $updated->sadc_snapshot);
        $this->assertSame('1', (string) $updated->ua_snapshot);
        $this->assertSame('14', (string) $updated->iva_snapshot);
        $this->assertSame('2', (string) $updated->ieq_snapshot);
        $this->assertNotNull($updated->pauta_snapshot_at);
    }

    public function test_local_suggestion_returns_existing_pauta_candidates(): void
    {
        $pauta = $this->createPauta([
            'codigo' => '8703.21.00',
            'descricao' => 'Automoveis de passageiros com motor a gasolina',
        ]);

        $results = app(SugerirCodigoPautalAction::class)->execute(new PautaSuggestionDTO(
            descricao: 'Automoveis',
            subcategoriaId: null,
        ));

        $this->assertNotEmpty($results);
        $this->assertSame($pauta->id, $results[0]['pauta_aduaneira_id']);
    }

    public function test_process_simulator_prorates_freight_and_insurance(): void
    {
        $processo = $this->createProcesso(['fob_total' => 1000, 'frete' => 100, 'seguro' => 50, 'Cambio' => 2]);
        $pauta = $this->createPauta(['rg' => 10, 'iva' => 14, 'ieq' => 1]);
        $mercadoria = $this->createMercadoria(['Fk_Importacao' => $processo->id, 'preco_total' => 500]);
        app(AssociarPautaMercadoriaAction::class)->execute($mercadoria->id, $pauta->id);

        $result = app(CalcularTributacaoProcessoAction::class)
            ->execute($processo->refresh(), 'rg', true, true)
            ->toArray();

        $this->assertSame(50.0, $result['items'][0]['frete_rateado']);
        $this->assertSame(25.0, $result['items'][0]['seguro_rateado']);
        $this->assertSame(1150.0, $result['items'][0]['valor_aduaneiro']);
    }

    public function test_initial_pauta_association_creates_audit(): void
    {
        $pauta = $this->createPauta(['codigo' => '0101.21.00']);
        $mercadoria = $this->createMercadoria();

        app(AssociarPautaMercadoriaAction::class)->execute($mercadoria->id, $pauta->id);

        $this->assertSame(1, MercadoriaPautaAudit::query()->where('mercadoria_id', $mercadoria->id)->count());
    }

    public function test_pauta_change_creates_old_and_new_audit(): void
    {
        $old = $this->createPauta(['codigo' => '0101.21.00']);
        $new = $this->createPauta(['codigo' => '0101.29.00']);
        $mercadoria = $this->createMercadoria();

        app(AssociarPautaMercadoriaAction::class)->execute($mercadoria->id, $old->id);
        app(AssociarPautaMercadoriaAction::class)->execute($mercadoria->id, $new->id, 'Correção de classificação.', 'manual');

        $audit = MercadoriaPautaAudit::query()->where('mercadoria_id', $mercadoria->id)->latest('id')->first();

        $this->assertSame($old->id, $audit->old_pauta_aduaneira_id);
        $this->assertSame($new->id, $audit->new_pauta_aduaneira_id);
        $this->assertSame('manual', $audit->source);
        $this->assertSame('Correção de classificação.', $audit->reason);
    }

    private function createPauta(array $overrides = []): PautaAduaneira
    {
        return PautaAduaneira::query()->create(array_merge([
            'codigo' => '0203.11.00',
            'descricao' => 'Carnes suinas',
            'uq' => 'kg',
            'rg' => 0,
            'sadc' => 0,
            'ua' => 0,
            'requisitos' => '0',
            'observacao' => '0',
            'iva' => 0,
            'ieq' => 0,
        ], $overrides));
    }

    private function createMercadoria(array $overrides = []): Mercadoria
    {
        Schema::disableForeignKeyConstraints();

        try {
            return Mercadoria::query()->create(array_merge([
                'Fk_Importacao' => 1,
                'Descricao' => 'Mercadoria de teste',
                'Quantidade' => 1,
                'Unidade' => 'Kg',
                'Peso' => 1,
                'preco_unitario' => 100,
                'preco_total' => 100,
            ], $overrides));
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function createProcesso(array $overrides = []): Processo
    {
        Schema::disableForeignKeyConstraints();

        try {
            return Processo::query()->create(array_merge([
                'NrProcesso' => 'PROC-TESTE',
                'TipoProcesso' => 'Importação',
                'DataAbertura' => now()->toDateString(),
                'customer_id' => 1,
                'user_id' => 1,
                'empresa_id' => 1,
                'Estado' => 'Aberto',
                'Moeda' => 'USD',
                'Cambio' => 1,
                'fob_total' => 0,
                'frete' => 0,
                'seguro' => 0,
            ], $overrides));
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function calculate(int $pautaId, string $regime): array
    {
        return app(CalcularTaxasPautaAction::class)
            ->execute(new CalculoPautaDTO(
                pautaAduaneiraId: $pautaId,
                valorAduaneiro: 1000,
                regimeTaxa: $regime,
                incluirIva: true,
                incluirIeq: true,
            ))
            ->toArray();
    }
}
