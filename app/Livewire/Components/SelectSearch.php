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
    public string $field;

    public string $search = '';
    public bool $open = false;
    public $results = [];
    public int $highlightIndex = 0;
    public string $selectedLabel = '';
    public string $selectedExtra = '';
    public $selectedId = null;

    protected $listeners = [
        'select-search-updated' => 'handleExternalUpdate',
    ];

    public function mount(
        string $model,
        string $displayField,
        string $searchField,
        string $field,
        $selectedId = null,
        ?string $extraField = null,
        array $where = []
    ): void {
        $this->model = $model;
        $this->displayField = $displayField;
        $this->searchField = $searchField;
        $this->field = $field;
        $this->selectedId = $selectedId;
        $this->extraField = $extraField;
        $this->where = $where;

        if ($this->selectedId) {
            $this->loadSelectedItem();
        }
    }

    public function loadResults(): void
    {
        try {
            $query = app($this->model)->query();

            foreach ($this->where as $condition) {
                if (is_array($condition) && count($condition) >= 3) {
                    $query->where(...$condition);
                }
            }

            if ($this->search !== '') {
                if (str_contains($this->searchField, '.')) {
                    [$relation, $field] = explode('.', $this->searchField, 2);
                    $query->whereHas($relation, function ($q) use ($field) {
                        $q->where($field, 'like', '%' . $this->search . '%');
                    });
                } else {
                    $query->where($this->searchField, 'like', '%' . $this->search . '%');
                }
            }

            $this->results = $query->limit(15)->get();
        } catch (\Throwable $e) {
            Log::error('Error in SelectSearch', [
                'model' => $this->model,
                'field' => $this->field,
                'error' => $e->getMessage(),
            ]);
            $this->results = collect();
        }
    }

    public function selectItem($id, $label, $extra = ''): void
    {
        $this->selectedId = $id;
        $this->selectedLabel = (string) $label;
        $this->selectedExtra = (string) $extra;
        $this->search = (string) $label;
        $this->open = false;

        $this->dispatch('select-search-updated', field: $this->field, value: $id, label: $label);
    }

    public function handleExternalUpdate($field, $value, $label = ''): void
    {
        if ($this->field !== $field) {
            return;
        }

        $this->selectedId = $value;
        $this->selectedLabel = (string) $label;
        $this->selectedExtra = '';
        $this->search = (string) $label;
        $this->open = false;
    }

    public function loadSelectedItem(): void
    {
        if (! $this->selectedId) {
            return;
        }

        try {
            $query = app($this->model)->query();
            $item = $this->field !== 'id'
                ? $query->where($this->field, $this->selectedId)->first()
                : $query->find($this->selectedId);

            if ($item) {
                $this->selectedLabel = (string) data_get($item, $this->displayField, '');
                $this->selectedExtra = $this->extraField ? (string) data_get($item, $this->extraField, '') : '';
                $this->search = $this->selectedLabel;
            }
        } catch (\Throwable $e) {
            Log::error('Error loading selected item', [
                'field' => $this->field,
                'selected_id' => $this->selectedId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updatedSearch(): void
    {
        $this->loadResults();
        $this->open = true;
    }

    public function render()
    {
        return view('livewire.components.select-search');
    }
}
