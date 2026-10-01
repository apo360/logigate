<?php

return [
    // Existing business role names; independent of unassigned legacy definitions.
    'roles' => [
        'Administrador', 'Gestor', 'Gestor Financeiro', 'Gestor Auditor',
        'Gestor Despachante', 'Transitário', 'Praticante', 'Operador',
        'Customer Manager', 'customer-manager',
    ],
    'permission_modules' => [
        'users', 'empresas', 'customers', 'processos', 'licenciamentos',
        'mercadorias', 'documents', 'arquivo', 'invoices', 'payments',
        'receipts', 'reports', 'audit', 'exportadores', 'produtos',
    ],
];
