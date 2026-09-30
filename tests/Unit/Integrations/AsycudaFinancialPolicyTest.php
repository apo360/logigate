<?php

declare(strict_types=1);

namespace Tests\Unit\Integrations;

use App\Domains\Declaracao\Services\AsycudaFinancialPolicy;
use PHPUnit\Framework\TestCase;

final class AsycudaFinancialPolicyTest extends TestCase
{
    public function test_external_invoice_and_currency_do_not_populate_internal_totals_or_exchange_rate(): void
    {
        $summary = (new AsycudaFinancialPolicy())->describe([
            'valuation' => ['totalInvoice' => [
                'amount' => 21488.70,
                'currencyRate' => ['currencyCode' => 'EUR'],
            ]],
        ], []);

        self::assertSame(21488.70, $summary['external_invoice_total']['amount']);
        self::assertSame('EUR', $summary['external_invoice_total']['currencyRate']['currencyCode']);
        self::assertSame([
            'fob_total' => null,
            'frete' => null,
            'seguro' => null,
            'cif' => null,
            'ValorTotal' => null,
            'ValorAduaneiro' => null,
            'Cambio' => null,
        ], $summary['logigate']);
    }

    public function test_root_freight_and_insurance_remain_identifiable_with_mode_currency_and_origin(): void
    {
        $summary = (new AsycudaFinancialPolicy())->describe([
            'adjustments' => [
                ['code' => 'FRETE', 'apportionmentMode' => 'BY_WEIGHT', 'line' => ['amount' => 3510, 'currencyRate' => ['currencyCode' => 'EUR']]],
                ['code' => 'SEGURO', 'apportionmentMode' => 'BY_VALUE', 'line' => ['amount' => 50, 'currencyRate' => ['currencyCode' => 'EUR']]],
                ['code' => 'OTHER', 'line' => ['amount' => 17, 'currencyRate' => ['currencyCode' => 'EUR']]],
            ],
        ], []);

        self::assertSame(3510, $summary['freight_candidates'][0]['value']);
        self::assertSame('BY_WEIGHT', $summary['freight_candidates'][0]['mode']);
        self::assertSame('EUR', $summary['freight_candidates'][0]['currency']);
        self::assertSame('declaration.adjustments.0', $summary['freight_candidates'][0]['source']);
        self::assertSame(50, $summary['insurance_candidates'][0]['value']);
        self::assertSame('BY_VALUE', $summary['insurance_candidates'][0]['mode']);
        self::assertSame('EUR', $summary['insurance_candidates'][0]['currency']);
        self::assertSame('OTHER', $summary['root_adjustments'][2]['code']);
        self::assertNull($summary['logigate']['frete']);
        self::assertNull($summary['logigate']['seguro']);
    }

    public function test_cif_incoterm_and_item_price_stay_external_candidates(): void
    {
        $summary = (new AsycudaFinancialPolicy())->describe([], [[
            'id' => 'item-1',
            'itemNumber' => 1,
            'valuationDetails' => ['itemPrice' => [
                'amount' => 21488.70,
                'currencyRate' => ['currencyCode' => 'EUR'],
            ]],
            'transactionTerm' => ['incoterms' => ['code' => 'CIF']],
        ]]);

        self::assertSame(21488.70, $summary['item_prices'][0]['value']);
        self::assertSame('EUR', $summary['item_prices'][0]['currency']);
        self::assertSame(['incoterms' => ['code' => 'CIF']], $summary['item_transaction_terms'][0]['value']);
        self::assertNull($summary['logigate']['cif']);
        self::assertNull($summary['logigate']['fob_total']);
    }
}
