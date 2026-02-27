<?php

// app/Services/AppyPay/AppyPayService.php

namespace App\Services\AppyPay;

class AppyPayService
{
    public function gpo(array $data): array
    {
        return app(AppyPayGpoService::class)->create($data);
    }

    public function ref(array $data): array
    {
        return app(AppyPayRefService::class)->create($data);
    }

    public function transfer(array $data): array
    {
        return app(AppyPayTransferService::class)->create($data);
    }

    public function checkStatus(array $data): array
    {
        return app(AppyPayStatusService::class)->check($data);
    }
}
