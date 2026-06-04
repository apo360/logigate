<?php

namespace App\Application\Mercadoria\Actions;

use Illuminate\Support\Facades\DB;

final class ReagruparMercadoriasAction
{
    public function execute(int $licenciamentoId): void
    {
        DB::unprepared('CALL AgruparMercadorias(?)', [$licenciamentoId]);
    }
}
