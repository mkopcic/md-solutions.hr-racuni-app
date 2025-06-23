<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'address' => $this->faker->address,
            'oib' => $this->faker->numerify('###########'),
            'iban' => 'HR' . $this->faker->numerify('#######################'),
            'email' => $this->faker->companyEmail,
            'phone' => $this->faker->phoneNumber,
            'location' => $this->faker->city,
            'months_active' => 12,
        ];
    }
}
