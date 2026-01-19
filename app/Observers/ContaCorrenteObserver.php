<?php

namespace App\Observers;

use App\Models\ContaCorrente;
use Illuminate\Support\Facades\Auth;

class ContaCorrenteObserver
{
    /**
     * Handle the ContaCorrente "created" event.
     */
    public function creating(ContaCorrente $movimento): void
    {
        if (Auth::check() && empty($movimento->created_by)) {
            $movimento->created_by = Auth::id();
        }
    }


    /**
     * Handle the ContaCorrente "updated" event.
     */
    public function updated(ContaCorrente $contaCorrente): void
    {
        //
    }

    /**
     * Handle the ContaCorrente "deleted" event.
     */
    public function deleted(ContaCorrente $contaCorrente): void
    {
        //
    }

    /**
     * Handle the ContaCorrente "restored" event.
     */
    public function restored(ContaCorrente $contaCorrente): void
    {
        //
    }

    /**
     * Handle the ContaCorrente "force deleted" event.
     */
    public function forceDeleted(ContaCorrente $contaCorrente): void
    {
        //
    }
}
