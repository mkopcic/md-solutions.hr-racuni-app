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
            $table->string('invoice_number')->nullable()->after('id'); // 1, 2, 3...
            $table->year('invoice_year')->nullable()->after('invoice_number'); // 2026
            $table->string('invoice_type', 10)->nullable()->after('invoice_year'); // SPO, AMK, FCZ, SFL, WDR
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'invoice_year', 'invoice_type']);
        });
    }
};
