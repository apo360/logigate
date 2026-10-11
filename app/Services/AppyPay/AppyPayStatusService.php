<?php

namespace App\Services\AppyPay;

class AppyPayStatusService
{
    public function check(array $data): array
    {
        throw new \LogicException('A consulta simulada foi retirada. Utilize os estados persistidos por callbacks assinados e billing:maintain.');
    }
}
