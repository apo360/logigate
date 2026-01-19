<?php

namespace App\View\Components\Table;

use Illuminate\View\Component;

class TdBadge extends Component
{

    public function __construct()
    {
        //
    }

    public function render()
    {
        return view('components.table.td-badge');
    }
}
