<?php

namespace App\Livewire\Usuario;

use Livewire\Component;

class SessaoSeguranca extends Component
{
    public function render()
    {
        return view('livewire.usuario.sessao-seguranca', [
            'user' => auth()->user(),
        ]);
    }
}
