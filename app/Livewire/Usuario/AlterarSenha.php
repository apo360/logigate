<?php

namespace App\Livewire\Usuario;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class AlterarSenha extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function save(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->forceFill([
            'password' => Hash::make($this->password),
            'password_changed' => true,
            'last_change_password' => now()->toDateString(),
        ])->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->dispatch('toast', type: 'success', message: 'Senha alterada.');
    }

    public function render()
    {
        return view('livewire.usuario.alterar-senha');
    }
}
