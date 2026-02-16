<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        Business::create([
            'name' => 'MD Solutions, obrt za računalne djelatnosti',
            'address' => 'Kardinala Franje Šepera 29, 31431 Čepin',
            'oib' => '86058362621',
            'iban' => 'HR9023400091160578001',
            'email' => 'info@mdsolutions.hr',
            'phone' => '+385 31 123 456',
            'location' => 'Čepin',
            'months_active' => 12,
        ]);
    }
}
