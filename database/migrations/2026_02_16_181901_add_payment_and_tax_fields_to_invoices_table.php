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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('total_amount'); // osnovica bez PDV-a
            $table->decimal('tax_total', 10, 2)->default(0)->after('subtotal'); // ukupan PDV
            $table->string('payment_method', 50)->default('virman')->after('note'); // gotovina, virman, kartica
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax_total', 'payment_method']);
        });
    }
};
