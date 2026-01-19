<?php

namespace App\Livewire\Forms;

use Livewire\Component;
use App\Models\ProcessosDraft;
use Illuminate\Support\Facades\Auth;

class ProcessoRascunho extends Component
{
    public $drafts = [];

    protected $listeners = ['draftSaved' => 'refreshList'];

    public function mount()
    {
        $this->refreshList();
    }

    public function refreshList()
    {
        $this->drafts = ProcessosDraft::where('user_id', Auth::id())->orderByDesc('created_at')->get();
    }

    public function load($id)
    {
        $this->emitUp('loadDraft', $id);
    }

    public function delete($id)
    {
        ProcessosDraft::find($id)?->delete();
        $this->refreshList();
        $this->dispatchBrowserEvent('toast', ['type'=>'success','message'=>'Rascunho apagado']);
    }

    public function render()
    {
        return view('livewire.forms.processo-rascunho');
    }
}
