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
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('in_vat_system')->nullable()->after('oib');
            $table->string('business_space_label', 10)->nullable()->after('in_vat_system');
            $table->string('cash_register_label', 10)->nullable()->after('business_space_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['in_vat_system', 'business_space_label', 'cash_register_label']);
        });
    }
};
