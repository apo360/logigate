<?php

namespace Tests\Feature\Processo;

use App\Livewire\Tables\ProcessosTable;
use Livewire\Livewire;
use Tests\Support\IsolatedDatabaseTestCase;

class ProcessosTableTest extends IsolatedDatabaseTestCase
{
    use ProcessoTestFixtures;

    public function test_summary_filters_and_notifications_preserve_company_scope(): void
    {
        [$user, $company] = $this->createTenant('TABLE-A');
        [$foreignUser, $foreignCompany] = $this->createTenant('TABLE-B');
        [$estancia, $tipo] = $this->createLookupData();
        $customer = $this->createCustomer($company, $user, 'TABLE-A');
        $exporter = $this->createExportador($company, $user, 'TABLE-A');
        foreach (['Aberto', 'Em analise', 'Finalizado', 'Cancelado'] as $index => $state) {
            $this->createProcesso($company, $user, $customer, $exporter, $estancia, $tipo, [
                'NrProcesso' => 'TABLE-A-' . $index, 'Estado' => $state,
            ]);
        }
        $this->createProcesso($foreignCompany, $foreignUser,
            $this->createCustomer($foreignCompany, $foreignUser, 'TABLE-B'),
            $this->createExportador($foreignCompany, $foreignUser, 'TABLE-B'),
            $estancia, $tipo, ['NrProcesso' => 'TABLE-FOREIGN', 'Estado' => 'Aberto']);

        $this->grantTenantPermissions($user, ['processos.view']);
        $this->signInTenant($user);
        $component = Livewire::test(ProcessosTable::class)
            ->assertSee('Total Processos')->assertSee('Em andamento')
            ->assertDontSee('TABLE-FOREIGN')->assertDontSee('Novo Processo')
            ->assertViewHas('stats', fn ($stats) => (int) $stats->total === 4
                && (int) $stats->abertos === 1 && (int) $stats->em_andamento === 1
                && (int) $stats->finalizados === 1)
            ->set('status', 'Finalizado')
            ->assertViewHas('processos', fn ($rows) => $rows->total() === 1)
            ->set('search', 'no-matches')
            ->assertSee('Nenhum processo encontrado para os filtros selecionados.')
            ->call('limparFiltros')->assertSet('search', '')->assertSet('status', '')
            ->assertSet('searchDate', null)
            ->assertViewHas('processos', fn ($rows) => $rows->total() === 4);

        $this->assertCount(3, $component->get('notifications'));
    }
}
