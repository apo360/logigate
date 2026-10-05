<?php

declare(strict_types=1);

namespace Tests\Feature\Mercadoria;

use App\Application\Mercadoria\Actions\SincronizarContentoresMercadoriaAction;
use App\Application\Mercadoria\DTOs\MercadoriaData;
use App\Models\Contentor;
use App\Models\Mercadoria;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

final class ContentorMercadoriaSyncTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_sync_accepts_only_contentores_of_the_process_and_company_and_replaces_links(): void
    {
        [$user, $empresa] = $this->createTenant('CM-SYNC');
        [$estancia, $tipo] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'CM-SYNC');
        $exportador = $this->createExportador($empresa, $user, 'CM-SYNC');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estancia, $tipo);
        $mercadoria = Mercadoria::query()->create(['Fk_Importacao' => $processo->id, 'Descricao' => 'Teste']);
        $one = Contentor::withoutEvents(fn () => Contentor::query()->create(['empresa_id' => $empresa->id, 'processo_id' => $processo->id, 'numero' => 'SYNC-001']));
        $two = Contentor::withoutEvents(fn () => Contentor::query()->create(['empresa_id' => $empresa->id, 'processo_id' => $processo->id, 'numero' => 'SYNC-002']));

        $this->grantTenantPermissions($user, ['mercadorias.view', 'mercadorias.create', 'mercadorias.update', 'mercadorias.delete', 'licenciamentos.update', 'processos.update']);
        $this->signInTenant($user);
        $action = app(SincronizarContentoresMercadoriaAction::class);
        $action->execute($mercadoria, MercadoriaData::fromLivewire(['contentor_ids' => [$one->id, $two->id]], 'processo', $processo->id));
        $action->execute($mercadoria, MercadoriaData::fromLivewire(['contentor_ids' => [$two->id]], 'processo', $processo->id));

        self::assertSame([$two->id], $mercadoria->contentores()->pluck('contentores.id')->all());
        $action->execute($mercadoria, MercadoriaData::fromLivewire([], 'processo', $processo->id));
        self::assertSame([], $mercadoria->contentores()->pluck('contentores.id')->all());
    }

    public function test_sync_rejects_another_process_or_company(): void
    {
        [$user, $empresa] = $this->createTenant('CM-XPROC');
        [$estancia, $tipo] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'CM-XPROC');
        $exportador = $this->createExportador($empresa, $user, 'CM-XPROC');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estancia, $tipo);
        $otherProcess = $this->createProcesso($empresa, $user, $customer, $exportador, $estancia, $tipo);
        $mercadoria = Mercadoria::query()->create(['Fk_Importacao' => $processo->id, 'Descricao' => 'Teste']);
        $validContentor = Contentor::withoutEvents(fn () => Contentor::query()->create(['empresa_id' => $empresa->id, 'processo_id' => $processo->id, 'numero' => 'SYNC-VALID']));
        $wrongProcess = Contentor::withoutEvents(fn () => Contentor::query()->create(['empresa_id' => $empresa->id, 'processo_id' => $otherProcess->id, 'numero' => 'SYNC-XPROC']));
        [, $otherEmpresa] = $this->createTenant('CM-XEMP');
        $wrongCompany = Contentor::withoutEvents(fn () => Contentor::query()->create(['empresa_id' => $otherEmpresa->id, 'processo_id' => $processo->id, 'numero' => 'SYNC-XEMP']));
        $mercadoria->contentores()->attach($validContentor->id);
        $this->grantTenantPermissions($user, ['mercadorias.view', 'mercadorias.create', 'mercadorias.update', 'mercadorias.delete', 'licenciamentos.update', 'processos.update']);
        $this->signInTenant($user);
        $action = app(SincronizarContentoresMercadoriaAction::class);

        foreach ([$wrongProcess, $wrongCompany] as $contentor) {
            try {
                $action->execute($mercadoria, MercadoriaData::fromLivewire(['contentor_ids' => [$contentor->id]], 'processo', $processo->id));
                self::fail('A cross-process/company container must be rejected.');
            } catch (AuthorizationException) {
                self::assertSame([$validContentor->id], $mercadoria->contentores()->pluck('contentores.id')->all());
            }
        }
    }

    public function test_licenciamento_context_does_not_change_existing_container_links(): void
    {
        [$user, $empresa] = $this->createTenant('CM-LIC');
        [$estancia, $tipo] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'CM-LIC');
        $exportador = $this->createExportador($empresa, $user, 'CM-LIC');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estancia, $tipo);
        $mercadoria = Mercadoria::query()->create(['Fk_Importacao' => $processo->id, 'Descricao' => 'Teste']);
        $contentor = Contentor::withoutEvents(fn () => Contentor::query()->create(['empresa_id' => $empresa->id, 'processo_id' => $processo->id, 'numero' => 'SYNC-LIC']));
        $mercadoria->contentores()->attach($contentor->id);

        $this->grantTenantPermissions($user, ['mercadorias.view', 'mercadorias.create', 'mercadorias.update', 'mercadorias.delete', 'licenciamentos.update', 'processos.update']);
        $this->signInTenant($user);
        app(SincronizarContentoresMercadoriaAction::class)->execute($mercadoria, MercadoriaData::fromLivewire(['contentor_ids' => []], 'licenciamento', 1));

        self::assertSame([$contentor->id], $mercadoria->contentores()->pluck('contentores.id')->all());
    }
}
