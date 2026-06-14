<?php

namespace App\Livewire\Integracoes;

use App\Livewire\Empresa\EmpresaIntegracoes;

class IntegracoesEmpresa extends EmpresaIntegracoes
{
    public function render()
    {
        return view('livewire.integracoes.integracoes-empresa', [
            'cards' => $this->cards(),
            'integrations' => $this->integrations(),
        ]);
    }
}
