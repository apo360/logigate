<?php

namespace App\Livewire\Usuario;

use Illuminate\Validation\Rule;
use Livewire\Component;

class MeuPerfil extends Component
{
    public array $form = [
        'name' => '',
        'email' => '',
    ];

    public function mount(): void
    {
        $this->form = auth()->user()->only(['name', 'email']);
    }

    public function save(): void
    {
        $user = auth()->user();

        $this->validate([
            'form.name' => ['required', 'string', 'max:255'],
            'form.email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->forceFill($this->form)->save();

        $this->dispatch('toast', type: 'success', message: 'Perfil atualizado.');
    }

    public function render()
    {
        return view('livewire.usuario.meu-perfil');
    }
}
