<?php

namespace App\Application\Customer\Actions;

use App\Application\Customer\Services\CustomerS3PathService;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CriarPastaCustomerS3Action
{
    public function __construct(
        private readonly CustomerS3PathService $paths,
    ) {
    }

    public function execute(Customer $customer, int $empresaId): bool
    {
        try {
            foreach ($this->paths->folders($customer, $empresaId) as $folder) {
                Storage::disk(config('filesystems.default'))->put($folder . '.keep', '');
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Não foi possível criar pasta S3 do cliente.', [
                'customer_id' => $customer->id,
                'empresa_id' => $empresaId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}