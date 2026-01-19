<?php

namespace App\Livewire\Processos;

use Livewire\Component;
use App\Models\Processo;
use App\Models\EmolumentoTarifa;

class Despesas extends Component
{
    public Processo $processo;
    public EmolumentoTarifa $tarifa;
    public array $schemaDespesas = [];
    public array $form = [];
    public array $totais = []; // ADICIONE ESTA LINHA
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
        ];
    }

    public function mount(Processo $processo): void
    {
        $this->processo = $processo;
        
        $this->tarifa = EmolumentoTarifa::firstOrNew([
            'processo_id' => $processo->id
        ]);

        $this->buildSchemaDespesas();
        $this->loadFormData();
        $this->recalcular();
    }

    protected function buildSchemaDespesas(): void
    {
        $this->schemaDespesas = [
            // Taxas Portuárias
            'porto' => [
                'type' => 'money', 
                'label' => 'Taxa de Porto', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6 lg:col-span-4',
                'category' => 'portuarias'
            ],
            'terminal' => [
                'type' => 'money', 
                'label' => 'Taxa de Terminal', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6 lg:col-span-4',
                'category' => 'portuarias'
            ],
            'carga_descarga' => [
                'type' => 'money', 
                'label' => 'Carga e Descarga', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6 lg:col-span-4',
                'category' => 'portuarias'
            ],
            
            // Transporte
            'frete' => [
                'type' => 'money', 
                'label' => 'Frete Internacional', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6',
                'category' => 'transporte'
            ],
            'navegacao' => [
                'type' => 'money', 
                'label' => 'Taxa de Navegação', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6',
                'category' => 'transporte'
            ],
            'deslocacao' => [
                'type' => 'money', 
                'label' => 'Deslocação', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6',
                'category' => 'transporte'
            ],
            
            // Taxas Aduaneiras
            'direitos' => [
                'type' => 'money', 
                'label' => 'Direitos Aduaneiros', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6 lg:col-span-4',
                'category' => 'aduaneiras'
            ],
            'iec' => [
                'type' => 'money', 
                'label' => 'IEC (Import Entry Charge)', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6 lg:col-span-4',
                'category' => 'aduaneiras'
            ],
            'selos' => [
                'type' => 'money', 
                'label' => 'Selos', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6 lg:col-span-4',
                'category' => 'aduaneiras'
            ],
            
            // Inspeções e Certificações
            'lmc' => [
                'type' => 'money', 
                'label' => 'LMC (Licença Marítima e Consular)', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6',
                'category' => 'inspecoes'
            ],
            
            // Serviços Profissionais
            'honorario' => [
                'type' => 'money', 
                'label' => 'Honorário do Despachante', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6',
                'category' => 'servicos'
            ],
            'inerentes' => [
                'type' => 'money', 
                'label' => 'Despesas Inerentes', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6',
                'category' => 'servicos'
            ],
            'caucao' => [
                'type' => 'money', 
                'label' => 'Caução', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6',
                'category' => 'servicos'
            ],
            
            // Impostos Calculados (readonly)
            'iva_aduaneiro' => [
                'type' => 'money', 
                'label' => 'IVA Aduaneiro (14%)', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6',
                'category' => 'impostos',
                'readonly' => true
            ],
            'impostoEstatistico' => [
                'type' => 'money', 
                'label' => 'Imposto Estatístico (10%)', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6',
                'category' => 'impostos',
                'readonly' => true
            ],
            'honorario_iva' => [
                'type' => 'money', 
                'label' => 'IVA sobre Honorário (14%)', 
                'placeholder' => '0,00', 
                'col' => 'col-span-12 md:col-span-6',
                'category' => 'impostos',
                'readonly' => true
            ],
        ];
    }

    protected function loadFormData(): void
    {
        // Inicializar o array de totais
        $this->totais = [
            'portuarias' => 0,
            'transporte' => 0,
            'aduaneiras' => 0,
            'inspecoes' => 0,
            'servicos' => 0,
            'impostos' => 0,
        ];
        
        $this->form = $this->tarifa->exists 
            ? $this->tarifa->toArray()
            : array_fill_keys(array_keys($this->schemaDespesas), 0);
    }

    public function updatedForm($value, $field): void
    {
        // Garantir que o valor seja numérico
        $this->form[$field] = is_numeric($value) ? floatval($value) : 0;
        
        // Recalcular valores derivados
        $this->recalcular();
    }

    public function recalcular(): void
    {
        $valorAduaneiro = floatval($this->processo->ValorAduaneiro ?? 0);
        
        // Calcular impostos automáticos
        $this->form['iva_aduaneiro'] = $valorAduaneiro * 0.14;
        $this->form['impostoEstatistico'] = $valorAduaneiro * 0.10;
        $this->form['honorario_iva'] = (floatval($this->form['honorario'] ?? 0)) * 0.14;
        
        // Calcular totais por categoria
        $this->calculateTotals();
    }

    protected function calculateTotals(): void
    {
        // Definir categorias e seus campos
        $categories = [
            'portuarias' => ['porto', 'terminal', 'carga_descarga'],
            'transporte' => ['frete', 'navegacao', 'deslocacao'],
            'aduaneiras' => ['direitos', 'iec', 'selos'],
            'inspecoes' => ['lmc'],
            'servicos' => ['honorario', 'inerentes', 'caucao'],
            'impostos' => ['iva_aduaneiro', 'impostoEstatistico', 'honorario_iva'],
        ];
        
        // Calcular totais por categoria
        foreach ($categories as $category => $fields) {
            $this->totais[$category] = 0;
            foreach ($fields as $field) {
                $this->totais[$category] += floatval($this->form[$field] ?? 0);
            }
        }
        
        // Calcular total geral
        $this->totalGeral = 0;
        foreach ($this->form as $value) {
            if (is_numeric($value)) {
                $this->totalGeral += floatval($value);
            }
        }
        
        $this->form['TOTALGERAL'] = $this->totalGeral;
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
        $this->validate();
        
        try {
            // Remover TOTALGERAL antes de salvar
            $dataToSave = $this->form;
            unset($dataToSave['TOTALGERAL']);
            
            $this->tarifa->fill($dataToSave);
            $this->tarifa->processo_id = $this->processo->id;
            $this->tarifa->save();
            
            $this->dispatch('despesas-atualizadas');
            
            $this->dispatch('toast',
                type: 'success',
                message: 'Despesas atualizadas com sucesso!'
            );
            
        } catch (\Exception $e) {
            $this->dispatch('toast',
                type: 'error',
                message: 'Erro ao salvar despesas: ' . $e->getMessage()
            );
        }
    }

    public function resetToDefaults(): void
    {
        $this->form = array_fill_keys(array_keys($this->schemaDespesas), 0);
        $this->recalcular();
        
        $this->dispatch('toast',
            type: 'info',
            message: 'Formulário resetado para valores padrão'
        );
    }

    public function render()
    {
        return view('livewire.processos.despesas', [
            'tarifa' => $this->tarifa,
            'totalGeral' => $this->totalGeral,
            'totaisPorCategoria' => $this->totaisPorCategoria,
        ]);
    }
}