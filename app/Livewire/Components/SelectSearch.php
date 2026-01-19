<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SelectSearch extends Component
{
    public string $model;
    public string $displayField;
    public ?string $extraField = null;
    public string $searchField;
    public array $where = [];

    public $search = '';
    public $selected = null;
    public $open = false;
    public $results = [];
    public $highlightIndex = 0;
    public string $field;
    public $selectedLabel = '';
    public $selectedExtra = '';
    public $selectedId = null;
    
    // Listener para atualizações externas
    protected $listeners = [
        'selectSearchUpdated' => 'handleExternalUpdate'
    ];

    public function mount(
        string $model,
        string $displayField,
        string $searchField,
        string $field,
        ?string $extraField = null,
        array $where = []
    ) {
        // Carregar resultados iniciais se houver um ID selecionado
        if ($this->selectedId) {
            $this->loadSelectedItem();
        }
    }

    public function loadResults()
    {
        try {
            $query = app($this->model)->query();
            
            // Aplicar condições where
            if (!empty($this->where)) {
                foreach ($this->where as $condition) {
                    if (is_array($condition) && count($condition) >= 3) {
                        $query->where(...$condition);
                    }
                }
            }
            
            // Aplicar busca
            if ($this->search) {
                if (str_contains($this->searchField, '.')) {
                    // Relação: 'customer.CompanyName'
                    [$relation, $field] = explode('.', $this->searchField, 2);
                    $query->whereHas($relation, function ($q) use ($field) {
                        $q->where($field, 'like', '%' . $this->search . '%');
                    });
                } else {
                    $query->where($this->searchField, 'like', '%' . $this->search . '%');
                }
            }
            
            // Limitar resultados
            $this->results = $query->limit(15)->get();
            
        } catch (\Exception $e) {
            Log::error('Error in SelectSearch:', [
                'model' => $this->model,
                'error' => $e->getMessage()
            ]);
            $this->results = collect();
        }
    }

    public function selectItem($id, $label, $extra = '')
    {
        $this->selectedId = $id;
        $this->selectedLabel = $label;
        $this->selectedExtra = $extra;
        $this->open = false;
        $this->search = $label; // Mostrar o item selecionado no input
        
        // Emitir evento para o componente pai
        $this->dispatch('selectSearchUpdated', [
            'field' => $this->field,
            'value' => $id,
            'label' => $label,
            'extra' => $extra
        ]);
    }

    public function moveHighlightDown()
    {
        if ($this->highlightIndex < count($this->results) - 1) {
            $this->highlightIndex++;
        }
    }

    public function moveHighlightUp()
    {
        if ($this->highlightIndex > 0) {
            $this->highlightIndex--;
        }
    }

    public function selectHighlighted()
    {
        if (!isset($this->results[$this->highlightIndex])) return;

        $item = $this->results[$this->highlightIndex];

        $label = data_get($item, $this->displayField);

        $this->selectItem($item->id, $label);
    }

    public function handleExternalUpdate($data)
    {
        if ($this->field === ($data['field'] ?? null)) {
            $this->selectedId = $data['value'];
            $this->selectedLabel = $data['label'];
            $this->selectedExtra = $data['extra'] ?? '';
            $this->search = $data['label'];
            $this->open = false;
        }
    }
    
    public function loadSelectedItem()
    {
        if ($this->selectedId) {
            try {
                $item = app($this->model)->find($this->selectedId);
                if ($item) {
                    $this->selectedLabel = data_get($item, $this->displayField);
                    $this->selectedExtra = $this->extraField ? data_get($item, $this->extraField) : '';
                    $this->search = $this->selectedLabel;
                }
            } catch (\Exception $e) {
                Log::error('Error loading selected item:', ['error' => $e->getMessage()]);
            }
        }
    }

    public function updatedSearch()
    {
        $this->loadResults();
        $this->open = true;
    }

    public function render()
    {
        return view('livewire.components.select-search');
    }
}
