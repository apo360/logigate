<?php

namespace App\View\Components\Table;

use Illuminate\View\Component;

class ThSort extends Component
{
    public string $field;
    public ?string $sortField;
    public ?string $sortDirection = 'desc';

    public function __construct(
        string $field,
        ?string $sortField = null,
        ?string $sortDirection = null
    ) {
        $this->field = $field;
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        return view('components.table.th-sort');
    }
}
