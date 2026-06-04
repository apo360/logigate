<?php

namespace App\Domains\Licenciamento\Services;

use App\Domains\Licenciamento\ValueObjects\ValorMonetario;

final readonly class CalcularCifLicenciamentoService
{
    public function calcular(ValorMonetario|float|int $fobTotal, ValorMonetario|float|int $frete, ValorMonetario|float|int $seguro): ValorMonetario
    {
        return new ValorMonetario(
            $this->valor($fobTotal) + $this->valor($frete) + $this->valor($seguro)
        );
    }

    private function valor(ValorMonetario|float|int $valor): float
    {
        return $valor instanceof ValorMonetario ? $valor->getValor() : (float) $valor;
    }
}
