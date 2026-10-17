<?php

return [
    // Presentation switches never disable commercial or permission checks.
    'grouped' => env('NAVIGATION_GROUPED', true),
    'legacy_until' => env('MODULE_LEGACY_UNTIL', '2026-11-09 23:59:59'),
    'groups' => [
        'operations' => 'Operação aduaneira',
        'clients' => 'Clientes e gestão',
        'finance' => 'Financeiro',
        'administration' => 'Administração e auditoria',
    ],
    // Names describe the existing catalogue; IDs are resolved from the database.
    'module_groups' => [
        'Gestão Aduaneira' => 'operations', 'Licenciamento' => 'operations',
        'Processos' => 'operations', 'Declarações' => 'operations', 'Pauta' => 'operations',
        'Clientes' => 'clients', 'Facturação' => 'finance', 'Contabilidade' => 'finance',
        'Licenciamentos' => 'operations', 'Declarações Aduaneiras' => 'operations',
        'Pauta Aduaneira' => 'operations', 'Faturação Aduaneira' => 'finance',
        'Contabilidade Aduaneira' => 'finance', 'Gestão de Clientes' => 'clients',
    ],
    'module_codes' => [
        'processos' => ['Processos'], 'licenciamentos' => ['Licenciamentos', 'Licenciamento'],
        'declaracoes' => ['Declarações Aduaneiras', 'Declarações'], 'pauta' => ['Pauta Aduaneira', 'Pauta'],
        'facturacao' => ['Faturação Aduaneira', 'Facturação'],
        'contabilidade' => ['Contabilidade Aduaneira', 'Contabilidade'], 'clientes' => ['Gestão de Clientes', 'Clientes'],
    ],
    'route_modules' => [
        'processos.du' => 'declaracoes', 'licenciamentos.pice' => 'declaracoes',
        'processes.*' => 'processos', 'processos.*' => 'processos',
        'licences.*' => 'licenciamentos', 'licenciamentos.*' => 'licenciamentos',
        'contabilidade.*' => 'contabilidade',
        'pauta.index' => 'pauta', 'pauta.consultar' => 'pauta',
        'pauta.import_view' => 'pauta', 'pauta.simulador' => 'pauta', 'pauta.show' => 'pauta',
        'produtos.*' => 'facturacao', 'exportadors.*' => 'clientes',
        'customers.index' => 'clientes', 'customers.create' => 'clientes',
        'customers.store' => 'clientes', 'customers.show' => 'clientes',
        'customers.edit' => 'clientes', 'customers.update' => 'clientes',
        'customers.destroy' => 'clientes', 'customers.listagem_cc' => 'clientes',
    ],
    // Only permissions confirmed on the destination. Unknown destinations remain
    // subject to their existing backend authorization, and are reported separately.
    'permissions' => [
        'processes.index' => 'processos.view', 'processes.create' => 'processos.create',
        'processos.index' => 'processos.view', 'processos.create' => 'processos.create',
        'processos.exportacao' => 'processos.create', 'processos.exportacao-crude.create' => 'processos.create',
        'processos.tarifa' => 'processos.view', 'licences.index' => 'licenciamentos.view',
        'licences.create' => 'licenciamentos.create', 'licenciamentos.pice' => 'licenciamentos.view',
        'licenciamentos.index' => 'licenciamentos.view', 'licenciamentos.create' => 'licenciamentos.create',
        'produtos.index' => 'produtos.view', 'customers.index' => 'customers.view',
        'customers.listagem_cc' => 'customers.view', 'exportadors.index' => 'exportadores.view',
        'arquivos.index' => 'documents.view',
    ],
    'priorities' => [
        'Gestor Financeiro' => ['finance', 'operations', 'clients', 'administration'],
        'Gestor Auditor' => ['administration', 'finance', 'operations', 'clients'],
        'Customer Manager' => ['clients', 'operations', 'finance', 'administration'],
        'customer-manager' => ['clients', 'operations', 'finance', 'administration'],
    ],
];
