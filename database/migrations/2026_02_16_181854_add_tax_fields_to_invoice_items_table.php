<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('unit', 20)->default('kom')->after('quantity'); // kom, sat, dan, m2, kg...
            $table->decimal('tax_rate', 5, 2)->default(25.00)->after('total'); // 0.00, 5.00, 13.00, 25.00
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate'); // izračunati PDV iznos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['unit', 'tax_rate', 'tax_amount']);
        });
    }
};
