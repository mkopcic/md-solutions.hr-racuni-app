<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Izrada web stranice',
                'SEO optimizacija',
                'Grafički dizajn',
                'Consultiranje',
                'Održavanje sustava',
                'Izrada aplikacije',
                'Dizajn logotipa',
                'Hosting usluga'
            ]),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 50, 5000),
            'unit' => $this->faker->randomElement(['kom', 'sat', 'm2', 'dan']),
            'active' => true,
        ];
    }
}
