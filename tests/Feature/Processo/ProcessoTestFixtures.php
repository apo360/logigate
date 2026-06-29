<?php

namespace Tests\Feature\Processo;

use App\Models\Customer;
use App\Models\Empresa;
use App\Models\Exportador;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ProcessoTestFixtures
{
    private function createTenant(string $suffix): array
    {
        $user = User::factory()->create([
            'email' => 'processo-' . strtolower($suffix) . '@example.com',
        ]);

        $empresaId = DB::table('empresas')->insertGetId([
            'CodFactura' => 'CF-' . $suffix,
            'CodProcesso' => 'CP-' . $suffix,
            'Empresa' => 'Empresa Processo ' . $suffix,
            'ActividadeComercial' => 'Servicos',
            'Designacao' => 'Outro',
            'NIF' => '91' . str_pad((string) random_int(1, 999999), 8, '0', STR_PAD_LEFT) . $suffix,
            'Cedula' => 'CED-PROC-' . $suffix . '-' . random_int(1000, 9999),
            'Endereco_completo' => 'Rua Processo ' . $suffix,
            'Provincia' => 'Luanda',
            'Cidade' => 'Luanda',
            'Email' => 'processo-' . strtolower($suffix) . '@empresa.test',
            'Contacto_movel' => '900000000',
            'Contacto_fixo' => '222000000',
            'Sigla' => 'PR' . strtoupper($suffix),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $empresa = Empresa::query()->findOrFail($empresaId);

        DB::table('empresa_users')->insert([
            'conta' => null,
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $empresa];
    }

    private function createLookupData(): array
    {
        $estanciaId = 1;

        if (Schema::hasTable('estancias')) {
            $estanciaId = DB::table('estancias')->insertGetId([
                'cod_estancia' => 'EST' . random_int(100, 999),
                'desc_estancia' => 'Estancia Teste',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $tipoProcessoId = 'Importação';

        if (Schema::hasTable('regiao_aduaneiras')) {
            $tipoProcessoId = (string) DB::table('regiao_aduaneiras')->insertGetId([
                'codigo' => '11',
                'abrev' => 'IM',
                'descricao' => 'Importacao Teste',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$estanciaId, $tipoProcessoId];
    }

    private function createCustomer(Empresa $empresa, User $user, string $suffix): Customer
    {
        $customerId = DB::table('customers')->insertGetId($this->onlyExistingColumns('customers', [
            'CustomerID' => 'CLI-' . $suffix,
            'AccountID' => 'ACC-' . $suffix,
            'CustomerTaxID' => 'NIF-CLI-' . $suffix,
            'CompanyName' => 'Cliente ' . $suffix,
            'Telephone' => '900000000',
            'Email' => 'cliente-' . strtolower($suffix) . '@example.com',
            'Website' => null,
            'SelfBillingIndicator' => 0,
            'CustomerType' => 'Empresa',
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $customer = (new Customer())->newQueryWithoutScopes()->findOrFail($customerId);

        if (Schema::hasTable('customers_empresas')) {
            $empresa->customers()->syncWithoutDetaching([$customer->id]);
        }

        return $customer;
    }

    private function createExportador(Empresa $empresa, User $user, string $suffix): Exportador
    {
        $exportadorId = DB::table('exportadors')->insertGetId($this->onlyExistingColumns('exportadors', [
            'ExportadorID' => 'EXP-' . $suffix,
            'ExportadorTaxID' => 'NIF-EXP-' . $suffix,
            'AccountID' => 'ACC-EXP-' . $suffix,
            'Exportador' => 'Exportador ' . $suffix,
            'Endereco' => 'Rua Exportador ' . $suffix,
            'Telefone' => '900000000',
            'Email' => 'exportador-' . strtolower($suffix) . '@example.com',
            'Pais' => 1,
            'Cidade' => 'Luanda',
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $exportador = Exportador::query()->findOrFail($exportadorId);

        if (Schema::hasTable('exportador_empresas')) {
            $empresa->exportadors()->syncWithoutDetaching([$exportador->id]);
        }

        return $exportador;
    }

    private function createProcesso(Empresa $empresa, User $user, Customer $customer, Exportador $exportador, int $estanciaId, string|int $tipoProcessoId, array $overrides = []): Processo
    {
        $attributes = array_merge([
            'NrProcesso' => 'PROC-2026-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'RefCliente' => 'REF-' . random_int(100, 999),
            'Descricao' => 'Processo Teste',
            'DataAbertura' => now()->toDateString(),
            'TipoProcesso' => (string) $tipoProcessoId,
            'Estado' => 'Aberto',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'exportador_id' => $exportador->id,
            'estancia_id' => $estanciaId,
            'forma_pagamento' => 'RD',
            'codigo_banco' => '001',
            'Cambio' => 1,
            'fob_total' => 100,
            'frete' => 10,
            'seguro' => 5,
            'cif' => 115,
            'ValorAduaneiro' => 115,
        ], $overrides);

        return Processo::withoutEvents(
            fn () => Processo::query()->create($this->onlyExistingColumns('processos', $attributes))
        );
    }

    private function onlyExistingColumns(string $table, array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(Schema::getColumnListing($table)));
    }
}
