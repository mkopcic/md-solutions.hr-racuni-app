<?php

namespace Database\Factories;

use App\Models\TaxBracket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxBracketFactory extends Factory
{
    protected $model = TaxBracket::class;

    public function definition(): array
    {
        return [
            'from_amount' => 0,
            'to_amount' => 40000,
            'yearly_base' => 0,
            'yearly_tax' => 1912.50,
            'monthly_tax' => 159.38,
            'city_tax' => 0,
            'quarterly_amount' => 0,
        ];
    }
}
