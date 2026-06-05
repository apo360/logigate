<?php

namespace Tests\Unit\Billing;

use App\Domains\Billing\Enums\BillingCycle;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Domains\Billing\ValueObjects\PhoneNumberAO;
use PHPUnit\Framework\TestCase;

class BillingDomainTest extends TestCase
{
    public function test_merchant_transaction_id_accepts_only_appypay_safe_values(): void
    {
        $this->assertSame('GPO260605120000', (string) new MerchantTransactionId('GPO260605120000'));

        foreach (['GPO-260605', 'GPO_260605', '1234567890123456'] as $invalid) {
            try {
                new MerchantTransactionId($invalid);
                $this->fail('Invalid merchant transaction id was accepted.');
            } catch (\InvalidArgumentException $exception) {
                $this->assertStringContainsString('merchantTransactionId', $exception->getMessage());
            }
        }
    }

    public function test_phone_number_ao_normalizes_to_international_format(): void
    {
        $phone = new PhoneNumberAO('923456789');

        $this->assertSame('923456789', $phone->local());
        $this->assertSame('244923456789', $phone->international());
        $this->assertSame('244923456789', (new PhoneNumberAO('244923456789'))->international());
    }

    public function test_phone_number_ao_rejects_invalid_numbers(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PhoneNumberAO('823456789');
    }

    public function test_billing_cycle_accepts_legacy_aliases(): void
    {
        $this->assertSame(BillingCycle::Monthly, BillingCycle::fromInput('mensal'));
        $this->assertSame(BillingCycle::Annual, BillingCycle::fromInput('anual'));
        $this->assertSame(BillingCycle::Semestral, BillingCycle::fromInput('semestral'));
    }

    public function test_payment_status_maps_gateway_values(): void
    {
        $this->assertSame(PaymentStatus::Paid, PaymentStatus::fromGateway('Success'));
        $this->assertSame(PaymentStatus::Pending, PaymentStatus::fromGateway('Pending'));
        $this->assertSame(PaymentStatus::Failed, PaymentStatus::fromGateway('Failed'));
        $this->assertSame(PaymentStatus::Expired, PaymentStatus::fromGateway('Expired'));
    }
}
