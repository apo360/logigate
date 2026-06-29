<?php

namespace App\Livewire\Processo;

use Livewire\Component;
use App\Models\Processo;
use App\Models\EmolumentoTarifa;

class Despesas extends Component
{
    public Processo $processo;
    public EmolumentoTarifa $tarifa;
    public array $form = [];
    public array $totais = [];
    public float $totalGeral = 0;

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
        $this->processo = $processo;
        
        $this->tarifa = EmolumentoTarifa::firstOrNew([
            'processo_id' => $processo->id
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

    private function normalizeMoney(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        $value = str_replace(' ', '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : 0.0;
    }

    public function updatedForm($value, $field): void
    {
        if (! in_array($field, $this->fields(), true)) {
            return;
        }

        $this->form[$field] = $this->normalizeMoney($value);

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
                    $this->totais[$category] += (float) ($this->form[$field] ?? 0);
                }
            }
        }
        
        $this->totalGeral = 0.0;

        foreach ($this->fields() as $field) {
            $this->totalGeral += (float) ($this->form[$field] ?? 0);
        }
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
        $this->normalizeFormMoneyValues();
        $this->calculateTotals();
        $this->validate();
        
        try {
            $dataToSave = collect($this->form)
                ->only($this->fields())
                ->toArray();
            
            $this->tarifa->fill($dataToSave);
            $this->tarifa->processo_id = $this->processo->id;
            $this->tarifa->save();
            
            $this->dispatch('despesas-atualizadas');
            
            $this->dispatch('toast',
                type: 'success',
                message: 'Despesas atualizadas com sucesso!'
            );
            
        } catch (\Throwable $e) {
            report($e);

            $this->dispatch('toast',
                type: 'error',
                message: 'Erro ao salvar despesas.'
            );
        }
    }

    public function resetToDefaults(): void
    {
        foreach ($this->fields() as $field) {
            $this->form[$field] = 0.0;
        }

        $this->calculateTotals();
        
        $this->dispatch('toast',
            type: 'info',
            message: 'Formulário resetado para valores padrão'
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
}
