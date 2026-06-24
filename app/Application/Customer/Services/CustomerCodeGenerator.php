<?php

namespace App\Application\Customer\Services;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final readonly class CustomerCodeGenerator
{
    public function generate(int $empresaId, string $customerTaxId): string
    {
        return DB::transaction(function () use ($empresaId, $customerTaxId): string {
            $ultimoCliente = Customer::query()
                ->where('empresa_id', $empresaId)
                ->whereNotNull('CustomerID')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $sequencial = 1;

            if ($ultimoCliente?->CustomerID) {
                $partes = explode('/', (string) $ultimoCliente->CustomerID);
                preg_match('/\d+$/', $partes[0] ?? '', $match);
                $sequencial = isset($match[0]) ? ((int) $match[0] + 1) : 1;
            }

            return 'cli' . $empresaId . $customerTaxId . '-' . str_pad((string) $sequencial, 4, '0', STR_PAD_LEFT) . '/' . Carbon::now()->format('y');
        });
    }
}
