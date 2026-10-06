<?php

return [
    // Explicit review of the catalogue's identity/version is required before projection.
    'catalogue_revision' => env('MARKETPLACE_CATALOGUE_REVISION'),
    'window_months' => 12,
    'summary_max_age_hours' => 24,
];
