<?php

return [
    'vat_rate' => (float) env('BILLING_VAT_RATE', 0.14),
    'currency' => 'AOA',
    'management_roles' => ['Administrador', 'Gestor Financeiro'],
    // Review-only until the commercial module mapping is approved.
    'allow_empty_plans' => true,
    'renewal_notice_days' => 7,
    'process_quota_period' => 'review',
];
