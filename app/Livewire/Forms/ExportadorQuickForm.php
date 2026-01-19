<?php

namespace App\Livewire\Forms;

use Livewire\Component;
use App\Models\Exportador;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Pais;

class ExportadorQuickForm extends Component
{
    public $form = [
        'Exportador' => '',
        'ExportadorTaxID' => '',
        'Telefone' => '',
        'Email' => '',
        'Pais' => '',
        'Website' => '',
    ];
    
    public $modalType;
    
    protected $rules = [
        'form.Exportador' => 'required|min:3',
        'form.ExportadorTaxID' => 'nullable|string|max:20',
        'form.Telefone' => 'nullable|string|max:20',
        'form.Email' => 'nullable|email',
        'form.Pais' => 'nullable|string|max:100',
    ];

    public function save()
    {
        $this->validate();
        
        try {
            // Verificar se já existe exportador com mesmo NIF e Empresa ou User
            $existing = Exportador::where('ExportadorTaxID', $this->form['ExportadorTaxID'])
                    ->where('user_id', Auth()->id())
                    ->first();
            
            if ($existing) {
                session()->flash('error', 'Já existe um exportador com este nif.');
                return;
            }
            
            $exportador = Exportador::create([
                'Exportador' => $this->form['Exportador'],
                'ExportadorTaxID' => $this->form['ExportadorTaxID'],
                'Telefone' => $this->form['Telefone'],
                'Email' => $this->form['Email'],
                'user_id' => Auth()->id(),
                'Pais' => Pais::getByField('pais', 'Angola', 'id'),
            ]);

            // Emitir evento para fechar modal
            $this->dispatch('closeQuickModal');
            
            // Emitir evento para atualizar selects
            $this->dispatch('exportadorCreated', [
                'id' => $exportador->id,
                'name' => $exportador->Exportador,
                'nif' => $exportador->NIF,
            ]);

            // Limpar formulário
            $this->reset('form');
            
            session()->flash('success', 'Exportador criado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao criar exportador:', ['error' => $e->getMessage()]);
            session()->flash('error', 'Erro ao criar exportador: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        $this->dispatch('closeQuickModal');
    }

    public function render()
    {
        return view('livewire.forms.exportador-quick-form');
    }
}