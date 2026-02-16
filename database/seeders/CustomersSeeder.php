<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomersSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Sport Plus Osijek',
                'address' => 'Ivana Gundulića 62',
                'city' => '31000, Osijek',
                'oib' => '96557881558',
            ],
            [
                'name' => 'Auto Moto Klub Slavonac',
                'address' => 'Ivana Gundulića 24',
                'city' => '31000, Osijek',
                'oib' => '50240101934',
            ],
            [
                'name' => 'Fitnes Centar Zagreb',
                'address' => 'Avenija Dubrovnik 15',
                'city' => '10000, Zagreb',
                'oib' => '80452155517',
            ],
            [
                'name' => 'Sport for life Osijek',
                'address' => 'Ivana Gundulića 62',
                'city' => '31000, Osijek',
                'oib' => '35042486125',
            ],
            [
                'name' => 'Willdienstrad GmbH',
                'address' => 'Carl-Appelstrasse 5/2505',
                'city' => 'A-1100, Wien',
                'oib' => 'ATU76825018',
            ],
            [
                'name' => 'ALL4CON GmbH',
                'address' => 'Nibelungengasse 1-3/Top 50',
                'city' => '1010, Wien',
                'oib' => 'ATU75777857',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
