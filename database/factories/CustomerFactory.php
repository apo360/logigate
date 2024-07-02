<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'CustomerID' => $this->faker->unique()->numerify('CUST#####'),
            'AccountID' => $this->faker->unique()->numerify('ACC#####'),
            'CustomerTaxID' => $this->faker->numerify('TAX#####'),
            'CompanyName' => $this->faker->company,
            'Telephone' => $this->faker->phoneNumber,
            'Email' => $this->faker->safeEmail,
            'Website' => $this->faker->url,
            'SelfBillingIndicator' => $this->faker->boolean,
            'user_id' => \App\Models\User::factory(),
            'empresa_id' => $this->faker->numberBetween(1, 2),
        ];
    }
}
