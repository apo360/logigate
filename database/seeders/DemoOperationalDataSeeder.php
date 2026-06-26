<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Actions\Subscriptions\ActivateSubscriptionAction;
use App\Models\Subscricao;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DemoOperationalDataSeeder extends Seeder
{
    private Carbon $now;

    public function run(): void
    {
        $this->now = now();

        DB::transaction(function () {
            $userId = $this->ensureUser();
            $empresaId = $this->ensureEmpresa($userId);

            $this->ensureServices($empresaId);
            $this->ensureActiveSubscription($empresaId, $userId);
            $pautaIds = $this->ensurePauta();
            $this->ensurePermission($userId);

            $customerIds = $this->ensureCustomers($empresaId, $userId);
            $exportadorIds = $this->ensureExportadores($empresaId, $userId);

            $licenciamentoIds = $this->ensureLicenciamentos($empresaId, $customerIds, $exportadorIds);
            $processoIds = $this->ensureProcessos($empresaId, $userId, $customerIds, $exportadorIds, $licenciamentoIds);

            $this->ensureMercadorias($processoIds, $licenciamentoIds, $pautaIds);
        });
    }

    private function ensureUser(): int
    {
        $userValues = [
            'name' => 'Operador Demo Logigate',
            'password' => Hash::make('password'),
            'email_verified_at' => $this->now,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];

        if (Schema::hasColumn('users', 'is_active')) {
            $userValues['is_active'] = true;
        }

        if (Schema::hasColumn('users', 'password_changed')) {
            $userValues['password_changed'] = true;
        }

        if (Schema::hasColumn('users', 'last_change_password')) {
            $userValues['last_change_password'] = $this->now->toDateString();
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'demo.operacional@logigate.local'],
            $userValues
        );

        return (int) DB::table('users')->where('email', 'demo.operacional@logigate.local')->value('id');
    }

    private function ensureEmpresa(int $userId): int
    {
        DB::table('empresas')->updateOrInsert(
            ['NIF' => '5417069000'],
            [
                'CodFactura' => 'LGFT',
                'CodProcesso' => 'LGPR',
                'Empresa' => 'Logigate Demo Despachos',
                'ActividadeComercial' => 'Gestão aduaneira e comércio externo',
                'Designacao' => 'Despachante Oficial',
                'Cedula' => 'CED-DEMO-2026',
                'Endereco_completo' => 'Avenida 4 de Fevereiro, Luanda',
                'Provincia' => 'Luanda',
                'Cidade' => 'Luanda',
                'Dominio' => 'demo.logigate.local',
                'Email' => 'demo@logigate.local',
                'Contacto_movel' => '923000100',
                'Sigla' => 'LGD',
                'ativo' => true,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]
        );

        $empresaId = (int) DB::table('empresas')->where('NIF', '5417069000')->value('id');

        DB::table('empresa_users')->updateOrInsert(
            ['user_id' => $userId, 'empresa_id' => $empresaId],
            ['conta' => 'DEMO-OPERACIONAL', 'created_at' => $this->now, 'updated_at' => $this->now]
        );

        return $empresaId;
    }

    private function ensureServices(int $empresaId): void
    {
        $services = [
            ['DESP001', 'Serviço de despacho aduaneiro'],
            ['LIC002', 'Preparação de licenciamento'],
            ['DU003', 'Emissão e validação de DU'],
            ['CLASS004', 'Classificação pautal'],
            ['DOC005', 'Gestão documental aduaneira'],
            ['TER006', 'Acompanhamento em terminal'],
            ['CONS007', 'Consultoria de comércio externo'],
            ['EXP008', 'Apoio à exportação'],
        ];

        foreach ($services as $index => [$code, $description]) {
            DB::table('produtos')->updateOrInsert(
                ['ProductCode' => $code, 'empresa_id' => $empresaId],
                [
                    'ProductType' => 'S',
                    'ProductGroup' => '1',
                    'ProductDescription' => $description,
                    'ProductNumberCode' => $code,
                    'status' => 0,
                    'created_at' => $this->now->copy()->subDays($index),
                    'updated_at' => $this->now,
                ]
            );
        }
    }

    private function ensureActiveSubscription(int $empresaId, int $userId): void
    {
        $planoId = (int) (DB::table('planos')->where('codigo', 'FREE')->value('id')
            ?: DB::table('planos')->orderBy('preco_mensal')->value('id'));

        if (! $planoId) {
            throw new \RuntimeException('Crie pelo menos um plano antes de executar o seeder demo.');
        }

        $subscriptionId = DB::table('subscricoes')
            ->where('empresa_id', $empresaId)
            ->where('plano_id', $planoId)
            ->where('tipo_plano', 'demo')
            ->latest('id')
            ->value('id');

        $values = [
            'tipo_plano' => 'demo',
            'modalidade_pagamento' => 'monthly',
            'valor_pago' => 0,
            'data_subscricao' => $this->now,
            'data_inicio' => null,
            'data_expiracao' => null,
            'activated_at' => null,
            'status' => Subscricao::STATUS_PENDENTE,
            'renovacao_automatica' => false,
            'created_by' => $userId,
            'updated_by' => $userId,
            'updated_at' => $this->now,
        ];

        if ($subscriptionId) {
            DB::table('subscricoes')->where('id', $subscriptionId)->update($values);
        } else {
            $subscriptionId = DB::table('subscricoes')->insertGetId([
                ...$values,
                'empresa_id' => $empresaId,
                'plano_id' => $planoId,
                'created_at' => $this->now,
            ]);
        }

        app(ActivateSubscriptionAction::class)
            ->execute(Subscricao::findOrFail($subscriptionId));
    }

    private function ensurePermission(int $userId): void
    {

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $guard = 'web';

            /**
             * Permissões principais do módulo Customer.
             */
            $customerPermissions = [
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                'customers.view',
                'customers.create',
                'customers.update',
                'customers.delete',
                'customers.activate',
                'customers.deactivate',
                'customers.manage_portal_credentials',
                'customers.view_processos',
                'customers.view_licenciamentos',
                'customers.view_documents',
                'customers.associate_empresa',
            ];

            /**
             * Permissões relacionadas que o Customer Show usa para navegar/consultar.
             */
            $relatedPermissions = [
                'processos.view',
                'processos.create',
                'processos.update',
                'processos.delete',

                'licenciamentos.view',
                'licenciamentos.create',
                'licenciamentos.update',
                'licenciamentos.delete',

                'mercadorias.view',
                'mercadorias.create',
                'mercadorias.update',
                'mercadorias.delete',
                'licenciamentos.manage_mercadorias',
                'processos.manage_mercadorias',
                
                'documents.view',
                'documents.create',
                'documents.download',

                'invoices.view',
                'invoices.create',

                'payments.view',
                'receipts.view',
            ];

            $permissions = array_values(array_unique([
                ...$customerPermissions,
                ...$relatedPermissions,
            ]));

            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]);
            }

            /**
             * Role operacional para utilizadores internos da empresa.
             */
            $role = Role::firstOrCreate([
                'name' => 'customer-manager',
                'guard_name' => $guard,
            ]);

            $role->syncPermissions($permissions);

            /**
             * Dar permissões ao User 1.
             */
            $user = User::query()->find($userId);

            if (!$user) {
                $this->command?->error('User 1 não encontrado.');
                return;
            }

            /**
             * Garante que o utilizador usa o mesmo guard.
             */
            $user->assignRole($role);

            /**
             * Também dá permissões directas ao user.
             * Isto ajuda caso alguma verificação use $user->can()
             * directamente sem depender apenas da role.
             */
            $user->givePermissionTo($permissions);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->command?->info('Permissões Customer criadas e atribuídas ao User 201 com sucesso.');
            $this->command?->info('Role atribuída: customer-manager');

    }

    private function ensurePauta(): array
    {
        $rows = [
            ['8703.23.90', 'Veículos automóveis ligeiros usados', 'UN', 10, 0, 0, 'Inspecção e DU', 'Demo', 14, 0],
            ['8429.52.00', 'Escavadoras e máquinas de construção', 'UN', 5, 0, 0, 'Licença sectorial', 'Demo', 14, 0],
            ['1006.30.00', 'Arroz semibranqueado ou branqueado', 'KG', 2, 0, 0, 'Certificado fitossanitário', 'Demo', 5, 0],
            ['2710.19.41', 'Óleos lubrificantes', 'L', 5, 0, 0, 'Certificado de qualidade', 'Demo', 14, 2],
            ['0901.11.00', 'Café não torrado', 'KG', 0, 0, 0, 'Certificado de origem', 'Demo exportação', 0, 0],
            ['0306.17.00', 'Camarões congelados', 'KG', 0, 0, 0, 'Certificado sanitário', 'Demo exportação', 0, 0],
        ];

        foreach ($rows as $row) {
            DB::table('pauta_aduaneira')->updateOrInsert(
                ['codigo' => $row[0]],
                [
                    'descricao' => $row[1],
                    'uq' => $row[2],
                    'rg' => (string) $row[3],
                    'sadc' => (string) $row[4],
                    'ua' => (string) $row[5],
                    'requisitos' => $row[6],
                    'observacao' => $row[7],
                    'iva' => (string) $row[8],
                    'ieq' => (string) $row[9],
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]
            );
        }

        return DB::table('pauta_aduaneira')
            ->whereIn('codigo', array_column($rows, 0))
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function ensureCustomers(int $empresaId, int $userId): array
    {
        $names = [
            'Atlântico Importações', 'Kwanza Foods', 'Nova Rota Comércio', 'Baía Azul Distribuição',
            'AngoMed Suprimentos', 'Savana Máquinas', 'Lobito Trading', 'Huíla Agro',
            'Bengo Construções', 'Zaire Marine', 'Namibe Cold Chain', 'Cunene Retail',
            'Malanje Equipamentos', 'Moxico Logística', 'Luanda Pharma', 'Benguela Motors',
            'Cazenga Textil', 'Soyo Energia', 'Bié Cereais', 'Cabinda Serviços',
        ];

        foreach ($names as $index => $name) {
            $code = sprintf('CUST-DEMO-%03d', $index + 1);

            DB::table('customers')->updateOrInsert(
                ['CustomerID' => $code],
                [
                    'AccountID' => sprintf('311-DEMO-%03d', $index + 1),
                    'CustomerTaxID' => sprintf('500900%04d', $index + 1),
                    'CompanyName' => $name,
                    'Telephone' => sprintf('9231%05d', $index + 1),
                    'Email' => 'cliente'.($index + 1).'@demo.logigate.local',
                    'Website' => 'https://demo.logigate.local',
                    'SelfBillingIndicator' => 0,
                    'CustomerType' => 'Empresa',
                    'is_active' => 1,
                    'user_id' => $userId,
                    'empresa_id' => $empresaId,
                    'nacionality' => 'Angolana',
                    'doc_type' => 'NIF',
                    'doc_num' => sprintf('500900%04d', $index + 1),
                    'validade_date_doc' => $this->now->copy()->addYears(2)->toDateString(),
                    'metodo_pagamento' => $index % 2 === 0 ? 'Transferência' : 'Referência',
                    'tipo_cliente' => ['importador', 'exportador', 'ambos'][$index % 3],
                    'tipo_mercadoria' => ['Alimentos', 'Máquinas', 'Veículos', 'Farmacêuticos'][$index % 4],
                    'frequencia' => ['ocasional', 'mensal', 'anual'][$index % 3],
                    'observacoes' => 'Cliente demo para testes operacionais.',
                    'num_licenca' => sprintf('LIC-CLIENTE-%03d', $index + 1),
                    'validade_licenca' => $this->now->copy()->addMonths(18)->toDateString(),
                    'moeda_operacao' => $index % 2 === 0 ? 'AOA' : 'USD',
                    'created_at' => $this->now->copy()->subDays(80 - $index),
                    'updated_at' => $this->now,
                ]
            );

            $customerId = (int) DB::table('customers')->where('CustomerID', $code)->value('id');

            DB::table('customers_empresas')->updateOrInsert(
                ['customer_id' => $customerId, 'empresa_id' => $empresaId],
                [
                    'codigo_cliente' => $code,
                    'status' => 'ATIVO',
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]
            );
        }

        return DB::table('customers')
            ->where('CustomerID', 'like', 'CUST-DEMO-%')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function ensureExportadores(int $empresaId, int $userId): array
    {
        $names = [
            'Global Harvest Export', 'Congo River Commodities', 'Atlântico Café Export',
            'Namibe Fish Traders', 'SADC Minerals Supply', 'Lobito Timber Export',
            'Benguela Fruit Company', 'Luena Honey Export', 'Cabinda Oil Services', 'Huambo Seeds',
        ];

        $paisIds = $this->ids('paises');

        foreach ($names as $index => $name) {
            $code = sprintf('EXP-DEMO-%03d', $index + 1);

            DB::table('exportadors')->updateOrInsert(
                ['ExportadorID' => $code],
                [
                    'AccountID' => sprintf('221-DEMO-%03d', $index + 1),
                    'ExportadorTaxID' => sprintf('700800%04d', $index + 1),
                    'Exportador' => $name,
                    'Endereco' => 'Zona industrial demo '.($index + 1),
                    'Telefone' => sprintf('9242%05d', $index + 1),
                    'Email' => 'exportador'.($index + 1).'@demo.logigate.local',
                    'Website' => 'https://export.demo.logigate.local',
                    'Pais' => $paisIds[$index % count($paisIds)],
                    'Cidade' => ['Luanda', 'Lobito', 'Namibe', 'Cabinda'][$index % 4],
                    'user_id' => $userId,
                    'empresa_id' => $empresaId,
                    'created_at' => $this->now->copy()->subDays(60 - $index),
                    'updated_at' => $this->now,
                ]
            );

            $exportadorId = (int) DB::table('exportadors')->where('ExportadorID', $code)->value('id');

            DB::table('exportador_empresas')->updateOrInsert(
                ['exportador_id' => $exportadorId, 'empresa_id' => $empresaId],
                [
                    'codigo_exportador' => $code,
                    'additional_info' => 'Exportador demo associado automaticamente.',
                    'status' => 'ATIVO',
                    'data_associacao' => $this->now,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]
            );
        }

        return DB::table('exportadors')
            ->where('ExportadorID', 'like', 'EXP-DEMO-%')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function ensureLicenciamentos(int $empresaId, array $customerIds, array $exportadorIds): array
    {
        $estanciaIds = $this->ids('estancias');
        $transporteIds = $this->ids('tipo_transportes');
        $status = ['pendente', 'emitida', 'paga', 'anulada'];
        $tipoDeclaracoes = [11, 21, 23, 31, 41];

        for ($i = 1; $i <= 57; $i++) {
            $fob = 2500 + ($i * 310);
            $frete = 350 + ($i * 18);
            $seguro = 75 + ($i * 7);
            $code = sprintf('LIC-DEMO-%04d', $i);

            DB::table('licenciamentos')->updateOrInsert(
                ['codigo_licenciamento' => $code],
                [
                    'estancia_id' => $estanciaIds[$i % count($estanciaIds)],
                    'cliente_id' => $customerIds[($i - 1) % count($customerIds)],
                    'exportador_id' => $exportadorIds[($i - 1) % count($exportadorIds)],
                    'empresa_id' => $empresaId,
                    'referencia_cliente' => sprintf('REF-LIC-%04d', $i),
                    'factura_proforma' => sprintf('PF-%04d/2026', $i),
                    'descricao' => 'Licenciamento demo para mercadorias diversas',
                    'moeda' => $i % 2 === 0 ? 'USD' : 'AOA',
                    'tipo_declaracao' => $tipoDeclaracoes[$i % count($tipoDeclaracoes)],
                    'tipo_transporte' => $transporteIds[$i % count($transporteIds)],
                    'registo_transporte' => sprintf('NAV-DEMO-%03d', $i),
                    'nacionalidade_transporte' => (string) $this->ids('paises')[$i % count($this->ids('paises'))],
                    'manifesto' => sprintf('MF%05d', $i),
                    'data_entrada' => $this->now->copy()->subDays(70 - ($i % 50))->toDateString(),
                    'porto_entrada' => ['3POLA', '3DLTC', '3DLSL', '1DLMA'][$i % 4],
                    'peso_bruto' => 1200 + ($i * 43),
                    'adicoes' => 1 + ($i % 5),
                    'metodo_avaliacao' => '1',
                    'codigo_volume' => ['CT', 'PK', 'BX'][$i % 3],
                    'qntd_volume' => 5 + ($i % 20),
                    'forma_pagamento' => ['TRF', 'REF', 'NUM'][$i % 3],
                    'codigo_banco' => ['BAI', 'BFA', 'ATL'][$i % 3],
                    'fob_total' => $fob,
                    'frete' => $frete,
                    'seguro' => $seguro,
                    'cif' => $fob + $frete + $seguro,
                    'pais_origem' => ['China', 'Portugal', 'Brasil', 'Namibia', 'Angola'][$i % 5],
                    'porto_origem' => ['Shanghai', 'Lisboa', 'Santos', 'Walvis Bay', 'Luanda'][$i % 5],
                    'txt_gerado' => $i % 4 === 0,
                    'Nr_factura' => $i % 3 === 0 ? sprintf('FT-LIC-%04d', $i) : null,
                    'status_fatura' => $status[$i % count($status)],
                    'created_at' => $this->now->copy()->subDays(70 - ($i % 50)),
                    'updated_at' => $this->now,
                ]
            );
        }

        return DB::table('licenciamentos')
            ->where('codigo_licenciamento', 'like', 'LIC-DEMO-%')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function ensureProcessos(int $empresaId, int $userId, array $customerIds, array $exportadorIds, array $licenciamentoIds): array
    {
        $estanciaIds = $this->ids('estancias');
        $paisIds = $this->ids('paises');
        $transporteIds = $this->ids('tipo_transportes');
        $this->ids('regiao_aduaneiras');
        $importTypeId = (int) (DB::table('regiao_aduaneiras')->where('abrev', 'IM')->value('id') ?: DB::table('regiao_aduaneiras')->value('id'));
        $exportTypeId = (int) (DB::table('regiao_aduaneiras')->where('descricao', 'like', '%Exportação Definitiva%')->value('id') ?: DB::table('regiao_aduaneiras')->where('abrev', 'EX')->value('id') ?: $importTypeId);
        $states = ['Aberto', 'Em processamento', 'Aguardando documentos', 'Submetido', 'Concluído', 'Suspenso'];

        for ($i = 1; $i <= 87; $i++) {
            $isFromLicenciamento = $i <= 32;
            $isCrudExport = $i === 87;
            $isExport = $isCrudExport || $i % 5 === 0;
            $openedAt = $this->now->copy()->subDays(95 - ($i % 80));
            $state = $states[$i % count($states)];
            $fob = 3200 + ($i * 265);
            $frete = 420 + ($i * 15);
            $seguro = 95 + ($i * 6);
            $nrProcesso = $isCrudExport ? 'EXP-CRUD-DEMO-001' : sprintf('PRC-DEMO-%04d', $i);

            DB::table('processos')->updateOrInsert(
                ['NrProcesso' => $nrProcesso],
                [
                    'ContaDespacho' => sprintf('CD-DEMO-%04d', $i),
                    'RefCliente' => $isFromLicenciamento
                        ? sprintf('ORIGEM-LIC-%04d', $i)
                        : ($isCrudExport ? 'CRUD-EXPORT-001' : sprintf('REF-PRC-%04d', $i)),
                    'Descricao' => $isCrudExport
                        ? 'Processo de exportação CRUD para testes funcionais'
                        : ($isFromLicenciamento ? 'Processo proveniente de licenciamento demo' : 'Processo operacional demo'),
                    'DataAbertura' => $openedAt->toDateString(),
                    'DataFecho' => in_array($state, ['Concluído'], true) ? $openedAt->copy()->addDays(12)->toDateString() : null,
                    'TipoProcesso' => $isExport ? $exportTypeId : $importTypeId,
                    'Estado' => $state,
                    'customer_id' => $customerIds[($i - 1) % count($customerIds)],
                    'user_id' => $userId,
                    'empresa_id' => $empresaId,
                    'exportador_id' => $exportadorIds[($i - 1) % count($exportadorIds)],
                    'estancia_id' => $estanciaIds[$i % count($estanciaIds)],
                    'NrDU' => sprintf('DU-DEMO-%05d', $i),
                    'N_Dar' => 80000 + $i,
                    'MarcaFiscal' => sprintf('MF-DEMO-%03d', $i),
                    'BLC_Porte' => sprintf('BLC-DEMO-%05d', $i),
                    'Pais_origem' => $paisIds[$i % count($paisIds)],
                    'Pais_destino' => $paisIds[($i + 3) % count($paisIds)],
                    'PortoOrigem' => ['Luanda', 'Lobito', 'Namibe', 'Soyo'][$i % 4],
                    'DataChegada' => $openedAt->copy()->addDays(8)->toDateString(),
                    'TipoTransporte' => $transporteIds[$i % count($transporteIds)],
                    'registo_transporte' => sprintf('TR-DEMO-%04d', $i),
                    'nacionalidade_transporte' => (string) $paisIds[($i + 5) % count($paisIds)],
                    'forma_pagamento' => ['TRF', 'REF', 'NUM'][$i % 3],
                    'codigo_banco' => ['BAI', 'BFA', 'ATL'][$i % 3],
                    'Moeda' => $i % 2 === 0 ? 'USD' : 'AOA',
                    'Cambio' => $i % 2 === 0 ? 850 : 1,
                    'ValorTotal' => $fob + $frete + $seguro,
                    'ValorAduaneiro' => $fob + $frete + $seguro,
                    'fob_total' => $fob,
                    'frete' => $frete,
                    'seguro' => $seguro,
                    'cif' => $fob + $frete + $seguro,
                    'peso_bruto' => 900 + ($i * 37),
                    'observacoes' => $isCrudExport ? 'Processo especial solicitado: exportação CRUD.' : 'Dados demo gerados para testes.',
                    'created_at' => $openedAt,
                    'updated_at' => $this->now,
                ]
            );
        }

        return DB::table('processos')
            ->where(function ($query) {
                $query->where('NrProcesso', 'like', 'PRC-DEMO-%')
                    ->orWhere('NrProcesso', 'EXP-CRUD-DEMO-001');
            })
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function ensureMercadorias(array $processoIds, array $licenciamentoIds, array $pautaIds): void
    {
        $descriptions = [
            'Arroz embalado em sacos de 25kg',
            'Máquina escavadora hidráulica',
            'Viatura ligeira usada',
            'Óleo lubrificante industrial',
            'Café verde para exportação',
            'Camarão congelado para exportação',
        ];

        $demoMercadoriaIds = DB::table('mercadorias')
            ->where(function ($query) use ($descriptions) {
                $query->whereIn('Descricao', $descriptions)
                    ->orWhere('Descricao', 'like', 'Mercadoria complementar do licenciamento %');
            })
            ->pluck('id');

        if ($demoMercadoriaIds->isNotEmpty()) {
            DB::table('processo_licenciamento_mercadoria')
                ->whereIn('mercadoria_id', $demoMercadoriaIds)
                ->delete();

            DB::table('mercadorias')
                ->whereIn('id', $demoMercadoriaIds)
                ->delete();
        }

        foreach ($processoIds as $index => $processoId) {
            $licenciamentoId = $index < 32 ? ($licenciamentoIds[$index] ?? null) : null;
            $pautaId = $pautaIds[$index % count($pautaIds)];
            $pauta = DB::table('pauta_aduaneira')->where('id', $pautaId)->first();
            $quantity = 2 + ($index % 12);
            $unitPrice = 125 + ($index * 9);
            $description = $descriptions[$index % count($descriptions)];

            DB::table('mercadorias')->updateOrInsert(
                [
                    'Fk_Importacao' => $processoId,
                    'Descricao' => $description,
                    'licenciamento_id' => $licenciamentoId,
                ],
                [
                    'preco_unitario' => $unitPrice,
                    'preco_total' => $quantity * $unitPrice,
                    'codigo_aduaneiro' => $pauta?->codigo,
                    'NCM_HS' => $pauta?->codigo,
                    'NCM_HS_Numero' => (int) preg_replace('/\D/', '', $pauta?->codigo ?? '0'),
                    'Quantidade' => $quantity,
                    'Qualificacao' => 'Comercial',
                    'Unidade' => $index % 3 === 0 ? 'Ton' : 'Kg',
                    'Peso' => 150 + ($index * 12),
                    'marca' => $index % 3 === 0 ? 'DemoBrand' : null,
                    'modelo' => $index % 3 === 0 ? 'LG-'.$index : null,
                    'chassis' => $index % 7 === 0 ? sprintf('CHSDEMO%08d', $index + 1) : null,
                    'ano_fabricacao' => $index % 7 === 0 ? 2018 + ($index % 7) : null,
                    'potencia' => $index % 5 === 0 ? 75 + $index : null,
                    'pauta_aduaneira_id' => $pautaId,
                    'codigo_pautal_snapshot' => $pauta?->codigo,
                    'descricao_pautal_snapshot' => $pauta?->descricao,
                    'rg_snapshot' => $pauta?->rg,
                    'sadc_snapshot' => $pauta?->sadc,
                    'ua_snapshot' => $pauta?->ua,
                    'iva_snapshot' => $pauta?->iva,
                    'ieq_snapshot' => $pauta?->ieq,
                    'pauta_snapshot_at' => $this->now,
                    'created_at' => $this->now->copy()->subDays(40 - ($index % 30)),
                    'updated_at' => $this->now,
                ]
            );

            $mercadoriaId = (int) DB::table('mercadorias')
                ->where('Fk_Importacao', $processoId)
                ->where('Descricao', $description)
                ->when($licenciamentoId, fn ($query) => $query->where('licenciamento_id', $licenciamentoId), fn ($query) => $query->whereNull('licenciamento_id'))
                ->value('id');

            DB::table('processo_licenciamento_mercadoria')->updateOrInsert(
                [
                    'processo_id' => $processoId,
                    'licenciamento_id' => $licenciamentoId,
                    'mercadoria_id' => $mercadoriaId,
                ],
                [
                    'quantidade' => $quantity,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]
            );
        }

        foreach ($licenciamentoIds as $index => $licenciamentoId) {
            if ($index < 32) {
                continue;
            }

            $processoId = $processoIds[$index % 32];
            $pautaId = $pautaIds[$index % count($pautaIds)];
            $pauta = DB::table('pauta_aduaneira')->where('id', $pautaId)->first();
            $description = 'Mercadoria complementar do licenciamento '.sprintf('%03d', $index + 1);

            DB::table('mercadorias')->updateOrInsert(
                [
                    'Fk_Importacao' => $processoId,
                    'Descricao' => $description,
                    'licenciamento_id' => $licenciamentoId,
                ],
                [
                    'preco_unitario' => 210 + ($index * 5),
                    'preco_total' => (3 + ($index % 8)) * (210 + ($index * 5)),
                    'codigo_aduaneiro' => $pauta?->codigo,
                    'NCM_HS' => $pauta?->codigo,
                    'NCM_HS_Numero' => (int) preg_replace('/\D/', '', $pauta?->codigo ?? '0'),
                    'Quantidade' => 3 + ($index % 8),
                    'Qualificacao' => 'Comercial',
                    'Unidade' => 'Kg',
                    'Peso' => 90 + ($index * 8),
                    'pauta_aduaneira_id' => $pautaId,
                    'codigo_pautal_snapshot' => $pauta?->codigo,
                    'descricao_pautal_snapshot' => $pauta?->descricao,
                    'rg_snapshot' => $pauta?->rg,
                    'sadc_snapshot' => $pauta?->sadc,
                    'ua_snapshot' => $pauta?->ua,
                    'iva_snapshot' => $pauta?->iva,
                    'ieq_snapshot' => $pauta?->ieq,
                    'pauta_snapshot_at' => $this->now,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]
            );

            $mercadoriaId = (int) DB::table('mercadorias')
                ->where('Fk_Importacao', $processoId)
                ->where('Descricao', $description)
                ->where('licenciamento_id', $licenciamentoId)
                ->value('id');

            DB::table('processo_licenciamento_mercadoria')->updateOrInsert(
                [
                    'processo_id' => $processoId,
                    'licenciamento_id' => $licenciamentoId,
                    'mercadoria_id' => $mercadoriaId,
                ],
                [
                    'quantidade' => 3 + ($index % 8),
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]
            );
        }
    }

    private function ids(string $table): array
    {
        $ids = DB::table($table)->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($ids === []) {
            throw new \RuntimeException("A tabela {$table} precisa de dados base antes de executar este seeder.");
        }

        return $ids;
    }
}
