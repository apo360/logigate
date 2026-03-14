<?php

namespace App\Livewire\Onboarding;

use App\Services\Dashboard\OnboardingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class OnboardingWizard extends Component
{
    public array $checklist = [];
    public array $warnings = [];
    public bool $isCompleted = false;

    public function mount(): void
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            return;
        }

        $payload = Cache::remember("dashboard.widgets.onboarding.{$empresa->id}", 60, function () use ($empresa) {
            $service = new OnboardingService($empresa);

            return [
                'checklist' => $service->getChecklist(),
                'warnings' => $service->getWarnings(),
                'isCompleted' => $service->isCompleted(),
            ];
        });

        $this->checklist = $payload['checklist'];
        $this->warnings = $payload['warnings'];
        $this->isCompleted = $payload['isCompleted'];
    }

    public function render()
    {
        return view('livewire.onboarding.onboarding-wizard');
    }
}
