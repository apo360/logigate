<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ScheduledTask;

class CalendarTaskView extends Component
{
    public $events = [];

    protected $listeners = [
        'taskMoved' => 'updateTaskDate',
    ];

    public function mount()
    {
        $this->loadEvents();
    }

    public function loadEvents()
    {
        $this->events = ScheduledTask::select('id', 'title', 'schedule_date', 'status')
            ->get()
            ->map(function ($task) {
                return [
                    'id'    => $task->id,
                    'title' => $task->title,
                    'start' => $task->schedule_date->format('Y-m-d'),
                ];
            })->toArray();
    }

    public function updateTaskDate($data)
    {
        $task = ScheduledTask::find($data['id']);
        if (!$task) return;

        $task->schedule_date = $data['date'];
        $task->save();

        $this->loadEvents();
        $this->dispatch('refreshCalendar');
    }

    public function render()
    {
        return view('livewire.calendar-task-view');
    }
}
