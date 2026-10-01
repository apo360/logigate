<?php

namespace Tests\Unit\Customer;

use App\Application\Customer\Actions\CreateCustomerAction;
use App\Application\Customer\Actions\DeleteCustomerAction;
use App\Application\Customer\Actions\ToggleCustomerStatusAction;
use App\Application\Customer\DTOs\CreateCustomerDTO;
use App\Domains\Customers\Data\CustomerFormData;
use App\Domains\Customers\Services\CustomerService;
use App\Http\Controllers\CustomerController;
use App\Models\Customer;
use App\Models\Empresa;
use App\Models\User;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class CustomerResolutionTest extends TestCase
{
    public function test_customer_actions_and_service_resolve_with_the_application_autoloader(): void
    {
        foreach ([CreateCustomerAction::class, DeleteCustomerAction::class,
            ToggleCustomerStatusAction::class, CustomerService::class] as $class) {
            self::assertTrue(class_exists($class));
            self::assertInstanceOf($class, app($class));
        }

        $parameter = (new ReflectionMethod(CustomerController::class, 'destroy'))->getParameters()[1];
        self::assertSame(DeleteCustomerAction::class, $parameter->getType()->getName());
    }

    public function test_legacy_service_adapts_form_data_to_the_application_contract(): void
    {
        $actor = new User();
        $actor->id = 17;
        $this->actingAs($actor);
        $empresa = new Empresa();
        $empresa->id = 23;
        $customer = new Customer();
        $form = CustomerFormData::fromArray([
            'CustomerTaxID' => '1234567890', 'CompanyName' => 'Cliente Teste',
            'CustomerType' => 'Empresa', 'is_active' => false,
            'Telephone' => '900000000', 'City' => 'Luanda', 'AddressDetail' => 'Rua Teste',
            'doc_num' => 'DOC-1', 'moeda_operacao' => 'AOA',
        ]);
        $action = Mockery::mock(CreateCustomerAction::class);
        $action->shouldReceive('execute')->once()->with(Mockery::on(function ($dto): bool {
            return $dto instanceof CreateCustomerDTO
                && $dto->empresa_id === 23 && $dto->user_id === 17
                && $dto->CustomerTaxID === '1234567890'
                && $dto->CompanyName === 'Cliente Teste'
                && $dto->CustomerType === 'Empresa'
                && $dto->Telephone === '900000000'
                && $dto->is_active === false && $dto->doc_num === 'DOC-1'
                && $dto->moeda_operacao === 'AOA'
                && $dto->endereco['City'] === 'Luanda'
                && $dto->endereco['AddressDetail'] === 'Rua Teste';
        }))->andReturn($customer);

        self::assertSame($customer, (new CustomerService($action))->create($form, $empresa));
    }
}
