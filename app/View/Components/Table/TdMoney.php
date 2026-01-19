<?php

namespace App\View\Components\Table;

use Illuminate\View\Component;

class TdMoney extends Component
{
    public float $value;
    public string $currency;
    public int $decimals;

    public function __construct(
        ?float $value = 0,
        string $currency = 'KZ',
        int $decimals = 2
    ) {
        // fallback seguro
        $this->value = is_numeric($value) ? floatval($value) : 0.00;
        $this->currency = $currency;
        $this->decimals = $decimals;
    }

    public function formatted(): string
    {
        return number_format(
            $this->value,
            $this->decimals,
            ',',
            '.'
        );
    }

    public function render()
    {
        return view('components.table.td-money');
    }
}
