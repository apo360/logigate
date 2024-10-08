<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class breadcrumb extends Component
{
    public $items;
    public $separator;
    /**
     * Create a new component instance.
     */
    public function __construct($items = [], $separator = '>')
    {
        $this->items = $items;
        $this->separator = $separator;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.breadcrumb');
    }
}
