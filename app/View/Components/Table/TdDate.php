<?php

namespace App\View\Components\Table;

use Illuminate\View\Component;
use Carbon\Carbon;

class TdDate extends Component
{
    public string $date;

    public function __construct(string $date)
    {
        $this->date = $date;
    }

    public function render()
    {
        return view('components.table.td-date', [
            'formatted' => Carbon::parse($this->date)->format('d/m/Y'),
            'ago' => Carbon::parse($this->date)->diffForHumans(),
        ]);
    }
}
