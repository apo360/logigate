<?php

namespace App\Domains\Licenciamento\ValueObjects;

use InvalidArgumentException;

class ValorMonetario
{
    private float $valor;
    private string $moeda_base_AOA = 'AOA';
    private string $moeda_base_INTERNACIONAL = 'USD';
    // Outas moedas serão preenchidas pela base de dados
    private array $moeda_outras_INTERNACIONAL = [];

    public function __construct(float $valor)
    {
        if ($valor < 0) {
            throw new \InvalidArgumentException("O valor monetário não pode ser negativo.");
        }
        $this->valor = $valor;
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function __toString(): string
    {
        return number_format($this->valor, 2, ',', '.');
    }

}