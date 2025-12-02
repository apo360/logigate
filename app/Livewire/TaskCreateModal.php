<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ScheduledTask;
use Illuminate\Support\Facades\Auth;

class TaskCreateModal extends Component
{
    public $open = false;
    public $title, $type, $schedule_date, $recurrence, $payload = [];

    protected $listeners = [
        'openCreateTask' => 'openModal'
    ];

    public function openModal()
    {
        $this->resetExcept('open');
        $this->open = true;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string',
            'type' => 'required|string',
            'schedule_date' => 'required|date',
            'recurrence' => 'nullable|string',
        ]);

        ScheduledTask::create([
            'title' => $this->title,
            'type' => $this->type,
            'schedule_date' => $this->schedule_date,
            'recurrence' => $this->recurrence ?? 'none',
            'executor_type' => 'user',
            'created_by' => Auth::id(),
            'payload' => $this->payload,
            'approved' => true,
        ]);

        $this->open = false;

        $this->dispatch('task-created');
    }

    public function render()
    {
        return view('livewire.task-create-modal');
    }
}
