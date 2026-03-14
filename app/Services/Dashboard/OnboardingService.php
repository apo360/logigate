<?php

namespace App\Services\Dashboard;

use App\Models\Empresa;
use Illuminate\Support\Arr;

class OnboardingService extends BaseDashboardService
{
    public function __construct(Empresa $empresa)
    {
        parent::__construct($empresa);
    }

    public function getChecklist(): array
    {
        $empresa = $this->empresa->only([
            'Empresa',
            'NIF',
            'Endereco_completo',
            'Email',
            'Contacto_movel',
        ]);

        $companyProfileComplete = collect($empresa)
            ->every(fn ($value) => filled($value));

        $usersConfigured = $this->empresa->users()->count() > 1;
        $operationsStarted = $this->empresa->processos()->exists() || $this->empresa->licenciaments()->exists();
        $storageConfigured = config('filesystems.default') === 's3';

        return [
            'company_profile_complete' => $companyProfileComplete,
            'users_configured' => $usersConfigured,
            'operations_started' => $operationsStarted,
            'storage_configured' => $storageConfigured,
            'completed_steps' => collect([
                $companyProfileComplete,
                $usersConfigured,
                $operationsStarted,
                $storageConfigured,
            ])->filter()->count(),
            'total_steps' => 4,
        ];
    }

    public function getWarnings(): array
    {
        $checklist = $this->getChecklist();

        return array_values(array_filter([
            $checklist['company_profile_complete'] ? null : 'Complete company data',
            $checklist['users_configured'] ? null : 'Add system users',
            $checklist['operations_started'] ? null : 'Create first process or licensing',
            $checklist['storage_configured'] ? null : 'Configure file storage',
        ]));
    }

    public function isCompleted(): bool
    {
        $checklist = $this->getChecklist();

        return $checklist['completed_steps'] === $checklist['total_steps'];
    }
}
