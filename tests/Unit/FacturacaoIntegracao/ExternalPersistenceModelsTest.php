<?php

namespace Tests\Unit\FacturacaoIntegracao;

use App\Models\ExternalCustomerMapping;
use App\Models\ExternalInvoice;
use App\Models\ExternalInvoiceLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExternalPersistenceModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_tracking_tables_are_created(): void
    {
        $this->assertTrue(Schema::hasTable('external_customer_mappings'));
        $this->assertTrue(Schema::hasTable('external_invoices'));
        $this->assertTrue(Schema::hasTable('external_invoice_lines'));
        $this->assertTrue(Schema::hasTable('external_api_logs'));
    }

    public function test_external_invoice_has_many_lines(): void
    {
        $invoice = ExternalInvoice::query()->create([
            'customer_id' => 123,
            'status' => ExternalInvoice::STATUS_DRAFT,
            'gross_total' => 1000,
        ]);

        $line = $invoice->lines()->create([
            'type' => 'service',
            'description' => 'Servico aduaneiro',
            'quantity' => 1,
            'unit_price' => 1000,
            'line_total' => 1000,
        ]);

        $this->assertTrue($invoice->lines->contains($line));
        $this->assertInstanceOf(ExternalInvoiceLine::class, $invoice->lines->first());
    }

    public function test_external_invoice_status_helpers_and_mutators(): void
    {
        $invoice = ExternalInvoice::query()->create([
            'customer_id' => 123,
            'status' => ExternalInvoice::STATUS_DRAFT,
        ]);

        $invoice->markAsIssued(987, 'FT 2026/1', ['estado' => 'ok']);

        $this->assertTrue($invoice->fresh()->isIssued());
        $this->assertSame(987, $invoice->fresh()->external_invoice_id);
        $this->assertSame('FT 2026/1', $invoice->fresh()->external_invoice_number);
        $this->assertSame(['estado' => 'ok'], $invoice->fresh()->response_payload);

        $invoice->markAsFailed('Erro externo');

        $this->assertTrue($invoice->fresh()->isFailed());
        $this->assertSame('Erro externo', $invoice->fresh()->last_error);
    }

    public function test_external_customer_mapping_casts_json_payloads(): void
    {
        $mapping = ExternalCustomerMapping::query()->create([
            'customer_id' => 123,
            'external_customer_id' => 456,
            'external_payload' => ['id' => 456],
            'request_payload' => ['nif' => '5000000000'],
            'response_payload' => ['estado' => 'ok'],
        ]);

        $mapping = $mapping->fresh();

        $this->assertSame(['id' => 456], $mapping->external_payload);
        $this->assertSame(['nif' => '5000000000'], $mapping->request_payload);
        $this->assertSame(['estado' => 'ok'], $mapping->response_payload);
    }
}
