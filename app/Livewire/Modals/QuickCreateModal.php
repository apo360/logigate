<?php

namespace App\Livewire\Modals;

use Livewire\Component;
use Livewire\Attributes\On;

class QuickCreateModal extends Component
{
    public $isOpen = false;
    public $type = null;
    
    protected $listeners = [
        'openQuickModal' => 'openModal',
        'closeQuickModal' => 'close' // Adicionar este ouvinte
    ];

    public function openModal($type)
    {
        // Se for array, extrair o tipo
        if (is_array($type) && isset($type['type'])) {
            $this->type = $type['type'];
        } else {
            $this->type = $type;
        }
        
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
        $this->type = null;
    }


    public function render()
    {
        return view('livewire.modals.quick-create-modal');
    }
}
