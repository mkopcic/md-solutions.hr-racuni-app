<?php

namespace Database\Seeders;

use App\Models\TaxBracket;
use Illuminate\Database\Seeder;

class TaxBracketsSeeder extends Seeder
{
    public function run(): void
    {
        // Paušalne razine za 2026 godinu (prema PODACI sheetu iz Excel-a)
        $brackets = [
            // Razina I (do 50.000 EUR)
            [
                'from_amount' => 0,
                'to_amount' => 50000,
                'yearly_base' => 13000.00,
                'yearly_tax' => 3380.00,
                'monthly_tax' => 281.67,
                'city_tax' => 239.42,
                'quarterly_amount' => 905.00,
            ],
        ];

        foreach ($brackets as $bracket) {
            TaxBracket::create($bracket);
        }
    }
}
