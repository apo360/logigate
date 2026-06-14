<?php

namespace Tests\Feature\Licenciamento;

use App\Models\Licenciamento;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class LicenciamentoPolicyTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_policy_allows_only_same_tenant_licenciamento_operations(): void
    {
        [$tenantAUser, $tenantAEmpresa] = $this->createTenant('LIC-A');
        [$tenantBUser] = $this->createTenant('LIC-B');

        $licenciamento = new Licenciamento([
            'empresa_id' => $tenantAEmpresa->id,
            'txt_gerado' => null,
            'status_fatura' => 'pendente',
        ]);

        foreach (['view', 'update', 'delete', 'generateTxt', 'duplicate', 'constituteProcesso'] as $ability) {
            $this->assertTrue(Gate::forUser($tenantAUser)->allows($ability, $licenciamento), $ability);
            $this->assertFalse(Gate::forUser($tenantBUser)->allows($ability, $licenciamento), $ability);
        }

        $this->assertTrue(Gate::forUser($tenantAUser)->allows('create', Licenciamento::class));
        $this->assertFalse(Gate::forUser(User::factory()->create())->allows('create', Licenciamento::class));
    }
}
