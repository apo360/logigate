<?php

namespace App\Livewire\Processo;

use Livewire\Component;
use App\Models\Processo;
use App\Models\EmolumentoTarifa;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;

class Despesas extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public int $processoId;
    public Processo $processo;
    public EmolumentoTarifa $tarifa;
    public array $form = [];
    public array $totais = [];
    public float $totalGeral = 0;
    public ?string $feedback = null;
    public bool $hasUnsavedChanges = false;

    protected function rules(): array
    {
        return [
            'form.porto' => 'nullable|numeric|min:0',
            'form.terminal' => 'nullable|numeric|min:0',
            'form.lmc' => 'nullable|numeric|min:0',
            'form.navegacao' => 'nullable|numeric|min:0',
            'form.frete' => 'nullable|numeric|min:0',
            'form.inerentes' => 'nullable|numeric|min:0',
            'form.direitos' => 'nullable|numeric|min:0',
            'form.iec' => 'nullable|numeric|min:0',
            'form.deslocacao' => 'nullable|numeric|min:0',
            'form.carga_descarga' => 'nullable|numeric|min:0',
            'form.caucao' => 'nullable|numeric|min:0',
            'form.selos' => 'nullable|numeric|min:0',
            'form.honorario' => 'nullable|numeric|min:0',
            'form.iva_aduaneiro' => 'nullable|numeric|min:0',
            'form.impostoEstatistico' => 'nullable|numeric|min:0',
            'form.honorario_iva' => 'nullable|numeric|min:0',
            'form.emolumentos' => 'nullable|numeric|min:0',
            'form.juros_mora' => 'nullable|numeric|min:0',
            'form.multas' => 'nullable|numeric|min:0',
            'form.orgaos_ofiais' => 'nullable|numeric|min:0',
        ];
    }

    public function mount(Processo $processo): void
    {
        $this->processoId = (int) $processo->id;
        $this->processo = $this->authorizedProcesso();
        
        $this->tarifa = EmolumentoTarifa::firstOrNew([
            'processo_id' => $this->processoId
        ]);

        $this->loadFormData();
        $this->recalcular();
    }

    private function fields(): array
    {
        return [
            'porto',
            'terminal',
            'carga_descarga',
            'frete',
            'navegacao',
            'deslocacao',
            'direitos',
            'iec',
            'selos',
            'lmc',
            'honorario',
            'inerentes',
            'caucao',
            'iva_aduaneiro',
            'impostoEstatistico',
            'honorario_iva',
            'emolumentos',
            'juros_mora',
            'multas',
            'orgaos_ofiais',
        ];
    }

    protected function loadFormData(): void
    {
        $this->totais = [
            'portuarias' => 0.0,
            'transporte' => 0.0,
            'aduaneiras' => 0.0,
            'inspecoes' => 0.0,
            'servicos' => 0.0,
            'impostos' => 0.0,
        ];

        $this->form = [];

        foreach ($this->fields() as $field) {
            $this->form[$field] = $this->tarifa->exists
                ? (float) ($this->tarifa->{$field} ?? 0)
                : 0.0;
        }
    }

    private function normalizeMoney(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        if (! is_string($value)) {
            return $value;
        }

        $normalized = str_replace([' ', "\u{00A0}", "\u{202F}"], '', trim($value));
        if (! preg_match('/^[+-]?(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d{1,2})?$/', $normalized)) {
            return $value;
        }

        return round((float) str_replace(',', '.', str_replace('.', '', $normalized)), 2);
    }

    public function updatedForm($value, $field): void
    {
        if (! in_array($field, $this->fields(), true)) {
            return;
        }

        $this->form[$field] = $this->normalizeMoney($value);
        $this->feedback = null;
        $this->hasUnsavedChanges = true;
        $this->resetValidation('form.' . $field);

        $this->calculateTotals();
    }

    private function normalizeFormMoneyValues(): void
    {
        foreach ($this->fields() as $field) {
            $this->form[$field] = $this->normalizeMoney($this->form[$field] ?? 0);
        }
    }

    public function recalcular(): void
    {
        $this->calculateTotals();
    }

    protected function calculateTotals(): void
    {
        $categories = [
            'portuarias' => ['porto', 'terminal', 'carga_descarga'],
            'transporte' => ['frete', 'navegacao', 'deslocacao'],
            'aduaneiras' => ['direitos', 'iec', 'selos', 'emolumentos', 'juros_mora', 'multas'],
            'inspecoes' => ['lmc'],
            'servicos' => ['honorario', 'inerentes', 'caucao', 'orgaos_ofiais'],
            'impostos' => ['iva_aduaneiro', 'impostoEstatistico', 'honorario_iva'],
        ];
        
        foreach ($categories as $category => $fields) {
            $this->totais[$category] = 0.0;

            foreach ($fields as $field) {
                if (in_array($field, $this->fields(), true)) {
                    $this->totais[$category] += is_numeric($this->form[$field] ?? null) ? (float) $this->form[$field] : 0;
                }
            }
        }
        
        $this->totalGeral = 0.0;

        foreach ($this->fields() as $field) {
            $this->totalGeral += is_numeric($this->form[$field] ?? null) ? (float) $this->form[$field] : 0;
        }
        $this->totais = array_map(fn ($total) => round($total, 2), $this->totais);
        $this->totalGeral = round($this->totalGeral, 2);
    }

    public function getTotaisPorCategoriaProperty(): array
    {
        $categories = [
            'portuarias' => 'Taxas Portuárias',
            'transporte' => 'Transporte e Logística',
            'aduaneiras' => 'Taxas Aduaneiras',
            'inspecoes' => 'Inspeções e Certificações',
            'servicos' => 'Serviços Profissionais',
            'impostos' => 'Impostos e Taxas',
        ];
        
        $result = [];
        foreach ($categories as $key => $label) {
            $valor = $this->totais[$key] ?? 0;
            $result[$key] = [
                'label' => $label,
                'valor' => $valor,
                'percent' => $this->totalGeral > 0 
                    ? round($valor / $this->totalGeral * 100, 1)
                    : 0,
            ];
        }
        
        return $result;
    }

    public function getChartColor(int $index): string
    {
        $colors = [
            '#3b82f6', // Azul
            '#10b981', // Verde
            '#f59e0b', // Amarelo
            '#ef4444', // Vermelho
            '#8b5cf6', // Violeta
            '#ec4899', // Rosa
        ];
        
        return $colors[$index % count($colors)];
    }

    public function save(): void
    {
        $this->processo = $this->authorizedProcesso();
        $this->resetErrorBag('save');
        $this->feedback = null;
        $this->normalizeFormMoneyValues();
        $this->calculateTotals();
        $validated = $this->validate();
        
        try {
            $dataToSave = collect($validated['form'])
                ->only($this->fields())
                ->toArray();
            
            $this->tarifa = DB::transaction(function () use ($dataToSave) {
                Processo::query()->whereKey($this->processoId)->lockForUpdate()->firstOrFail();
                $tarifa = EmolumentoTarifa::firstOrNew(['processo_id' => $this->processoId]);
                $tarifa->fill($dataToSave);
                $tarifa->save();

                return $tarifa->refresh();
            });
            $this->feedback = 'Despesas gravadas com sucesso.';
            $this->hasUnsavedChanges = false;
            
            $this->dispatch('despesas-atualizadas');
            
            $this->dispatch('toast',
                type: 'success',
                message: $this->feedback
            );
            
        } catch (\Throwable $e) {
            report($e);
            $this->addError('save', 'Não foi possível gravar as despesas. Os valores preenchidos foram mantidos. Tente novamente.');

            $this->dispatch('toast',
                type: 'error',
                message: 'Não foi possível gravar as despesas.'
            );
        }
    }

    public function resetToDefaults(): void
    {
        $this->authorizedProcesso();
        $this->resetValidation();
        $this->feedback = 'Valores limpos no formulário. Grave as despesas para aplicar a alteração.';
        $this->hasUnsavedChanges = true;
        foreach ($this->fields() as $field) {
            $this->form[$field] = 0.0;
        }

        $this->calculateTotals();
        
        $this->dispatch('toast',
            type: 'info',
            message: $this->feedback
        );
    }

    public function render()
    {
        return view('livewire.processo.despesas', [
            'tarifa' => $this->tarifa,
            'totalGeral' => $this->totalGeral,
            'totaisPorCategoria' => $this->totaisPorCategoria,
        ]);
    }

    public function messages(): array
    {
        return [
            'numeric' => 'O campo :attribute deve conter um valor válido em Kz.',
            'min' => 'O campo :attribute não pode ser negativo.',
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'form.porto' => 'Taxa de Porto', 'form.terminal' => 'Taxa de Terminal',
            'form.carga_descarga' => 'Carga e Descarga', 'form.frete' => 'Frete Internacional',
            'form.navegacao' => 'Taxa de Navegação', 'form.deslocacao' => 'Deslocação',
            'form.direitos' => 'Direitos Aduaneiros', 'form.iec' => 'IEC', 'form.selos' => 'Selos',
            'form.emolumentos' => 'Emolumentos', 'form.juros_mora' => 'Juros de Mora', 'form.multas' => 'Multas',
            'form.lmc' => 'LMC', 'form.honorario' => 'Honorário do Despachante',
            'form.inerentes' => 'Despesas Inerentes', 'form.caucao' => 'Caução',
            'form.orgaos_ofiais' => 'Órgãos Oficiais', 'form.iva_aduaneiro' => 'IVA Aduaneiro',
            'form.impostoEstatistico' => 'Imposto Estatístico', 'form.honorario_iva' => 'IVA sobre Honorário',
        ];
    }

    private function authorizedProcesso(): Processo
    {
        $user = Auth::user();
        abort_unless($user, 403);
        $processo = app(ProcessoTenantAccessService::class)->findForUserOrFail($user, $this->processoId);
        $this->authorize('update', $processo);

        return $processo;
    }
}
