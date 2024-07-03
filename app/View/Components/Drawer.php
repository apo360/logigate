<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Drawer extends Component
{
    /**
     * Create a new component instance.
     */
    public $right;

    public function __construct($right = false)
    {
        $this->right = $right;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.drawer');
    }
}
