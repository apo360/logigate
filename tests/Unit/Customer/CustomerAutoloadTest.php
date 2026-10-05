<?php

namespace Tests\Unit\Customer;

use App\Application\Customer\Actions\CreateCustomerAction;
use App\Application\Customer\Actions\DeleteCustomerAction;
use App\Application\Customer\Actions\ToggleCustomerStatusAction;
use App\Domains\Customers\Services\CustomerService;
use App\Http\Controllers\CustomerController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class CustomerAutoloadTest extends TestCase
{
    public function test_customer_classes_load_from_their_psr4_paths(): void
    {
        foreach ([
            CreateCustomerAction::class => 'app/Application/Customer/Actions/CreateCustomerAction.php',
            DeleteCustomerAction::class => 'app/Application/Customer/Actions/DeleteCustomerAction.php',
            ToggleCustomerStatusAction::class => 'app/Application/Customer/Actions/ToggleCustomerStatusAction.php',
            CustomerService::class => 'app/Domains/Customers/Services/CustomerService.php',
        ] as $class => $path) {
            self::assertTrue(class_exists($class), $class);
            self::assertSame(realpath(dirname(__DIR__, 3) . '/' . $path), (new ReflectionClass($class))->getFileName());
        }

        $constructor = new ReflectionMethod(CustomerService::class, '__construct');
        self::assertSame(CreateCustomerAction::class, $constructor->getParameters()[0]->getType()->getName());
        $destroy = new ReflectionMethod(CustomerController::class, 'destroy');
        self::assertSame(DeleteCustomerAction::class, $destroy->getParameters()[1]->getType()->getName());
    }
}
