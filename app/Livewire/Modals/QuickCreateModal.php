<?php

namespace App\Livewire\Modals;

use Livewire\Component;

class QuickCreateModal extends Component
{
    public bool $isOpen = false;
    public ?string $entity = null;

    protected $listeners = [
        'open-quick-create-modal' => 'openModal',
        'quick-entity-selected' => 'close',
    ];

    public function openModal($payload = null): void
    {
        $this->entity = is_array($payload)
            ? ($payload['entity'] ?? null)
            : null;

        $this->isOpen = in_array($this->entity, ['customer', 'exportador'], true);
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->entity = null;
    }

    public function render()
    {
        return view('livewire.modals.quick-create-modal');
    }
}
