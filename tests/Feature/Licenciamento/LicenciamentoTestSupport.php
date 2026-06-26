<?php

namespace Tests\Feature\Licenciamento;

use App\Models\Empresa;
use App\Models\Licenciamento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

trait LicenciamentoTestSupport
{
    private function grantLicenciamentoPermissions(User $user, array $permissions = [
        'licenciamentos.view',
        'licenciamentos.create',
        'licenciamentos.update',
        'licenciamentos.delete',
    ]): void {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createLicenciamentoFor(Empresa $empresa, User $user, string $suffix = 'LIC'): Licenciamento
    {
        [$estanciaId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, $suffix);
        $exportador = $this->createExportador($empresa, $user, $suffix);

        $id = DB::table('licenciamentos')->insertGetId($this->onlyExistingLicenciamentoColumns([
            'codigo_licenciamento' => 'LIC-' . $suffix . '-' . random_int(1000, 9999),
            'estancia_id' => $estanciaId,
            'cliente_id' => $customer->id,
            'exportador_id' => $exportador->id,
            'empresa_id' => $empresa->id,
            'referencia_cliente' => 'REF-' . $suffix,
            'factura_proforma' => 'FP-' . $suffix,
            'descricao' => 'Licenciamento ' . $suffix,
            'moeda' => 'AOA',
            'tipo_declaracao' => '11',
            'tipo_transporte' => '3',
            'registo_transporte' => '',
            'manifesto' => '',
            'data_entrada' => null,
            'porto_entrada' => 'LAD',
            'peso_bruto' => 100,
            'adicoes' => 1,
            'metodo_avaliacao' => 'GATT',
            'codigo_volume' => 'B',
            'qntd_volume' => 1,
            'forma_pagamento' => 'RD',
            'codigo_banco' => '',
            'fob_total' => 100,
            'frete' => 10,
            'seguro' => 5,
            'cif' => 115,
            'status_fatura' => 'pendente',
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return Licenciamento::query()->findOrFail($id);
    }

    private function validLicenciamentoPayload(int $estanciaId, int $customerId, int $exportadorId, array $overrides = []): array
    {
        return array_merge([
            'cliente_id' => $customerId,
            'exportador_id' => $exportadorId,
            'estancia_id' => $estanciaId,
            'referencia_cliente' => 'REF-LIC',
            'factura_proforma' => 'FP-LIC',
            'descricao' => 'Licenciamento Teste',
            'moeda' => 'AOA',
            'tipo_declaracao' => '11',
            'tipo_transporte' => '3',
            'registo_transporte' => '',
            'manifesto' => '',
            'data_entrada' => null,
            'porto_entrada' => 'LAD',
            'peso_bruto' => 100,
            'adicoes' => 1,
            'metodo_avaliacao' => 'GATT',
            'codigo_volume' => 'B',
            'qntd_volume' => 1,
            'forma_pagamento' => 'RD',
            'codigo_banco' => '',
            'fob_total' => 100,
            'frete' => 10,
            'seguro' => 5,
            'cif' => 115,
            'status_fatura' => 'pendente',
        ], $overrides);
    }

    private function onlyExistingLicenciamentoColumns(array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(Schema::getColumnListing('licenciamentos')));
    }
}
