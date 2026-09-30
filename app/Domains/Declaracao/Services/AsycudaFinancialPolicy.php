<?php

declare(strict_types=1);

namespace App\Domains\Declaracao\Services;

/** Keeps ASYCUDA amounts as external evidence; it never derives LogiGate amounts. */
final class AsycudaFinancialPolicy
{
    public function describe(array $declaration, array $items): array
    {
        $adjustments = [];
        $itemAdjustments = [];
        $freight = [];
        $insurance = [];

        foreach ($declaration['adjustments'] ?? [] as $index => $adjustment) {
            if (! is_array($adjustment)) continue;
            $entry = $this->adjustment($adjustment, 'declaration.adjustments.' . $index);
            $adjustments[] = $entry;
            $code = strtoupper((string) ($entry['code'] ?? ''));
            if (in_array($code, ['FRETE', 'FREIGHT'], true)) $freight[] = $entry;
            if (in_array($code, ['SEGURO', 'INSURANCE'], true)) $insurance[] = $entry;
        }

        $itemPrices = [];
        $itemTerms = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) continue;
            $price = data_get($item, 'valuationDetails.itemPrice');
            if (is_array($price)) {
                $itemPrices[] = [
                    'item_id' => $item['id'] ?? null,
                    'item_number' => $item['itemNumber'] ?? $index + 1,
                    'value' => $price['amount'] ?? null,
                    'currency' => data_get($price, 'currencyRate.currencyCode'),
                    'source' => 'items.' . $index . '.valuationDetails.itemPrice',
                    'raw' => $price,
                ];
            }
            $term = $item['transactionTerm'] ?? null;
            if (is_array($term) && $term !== []) {
                $itemTerms[] = [
                    'item_id' => $item['id'] ?? null,
                    'item_number' => $item['itemNumber'] ?? $index + 1,
                    'value' => $term,
                    'source' => 'items.' . $index . '.transactionTerm',
                ];
            }
            foreach ($item['adjustments'] ?? [] as $adjustmentIndex => $itemAdjustment) {
                if (is_array($itemAdjustment)) {
                    $itemAdjustments[] = $this->adjustment($itemAdjustment, 'items.' . $index . '.adjustments.' . $adjustmentIndex);
                }
            }
        }

        return [
            'external_invoice_total' => data_get($declaration, 'valuation.totalInvoice'),
            'root_adjustments' => $adjustments,
            'item_adjustments' => $itemAdjustments,
            'freight_candidates' => $freight,
            'insurance_candidates' => $insurance,
            'item_prices' => $itemPrices,
            'transaction_term' => $declaration['transactionTerm'] ?? null,
            'item_transaction_terms' => $itemTerms,
            // Deliberately empty: external amounts, terms and currency never fill these fields.
            'logigate' => [
                'fob_total' => null,
                'frete' => null,
                'seguro' => null,
                'cif' => null,
                'ValorTotal' => null,
                'ValorAduaneiro' => null,
                'Cambio' => null,
            ],
        ];
    }

    private function adjustment(array $adjustment, string $source): array
    {
        $line = $adjustment['line'] ?? [];

        return [
            'code' => $adjustment['code'] ?? null,
            'value' => is_array($line) ? ($line['amount'] ?? $adjustment['amount'] ?? null) : ($adjustment['amount'] ?? null),
            'mode' => $adjustment['apportionmentMode'] ?? $adjustment['mode'] ?? null,
            'currency' => is_array($line) ? data_get($line, 'currencyRate.currencyCode') : data_get($adjustment, 'currencyRate.currencyCode'),
            'name' => $adjustment['name'] ?? null,
            'source' => $source,
            'raw' => $adjustment,
        ];
    }
}
