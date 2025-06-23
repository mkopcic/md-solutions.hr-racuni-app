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
        Schema::create('tax_brackets', function (Blueprint $table) {
            $table->id();
            $table->decimal('from_amount', 12, 2);
            $table->decimal('to_amount', 12, 2);
            $table->decimal('yearly_base', 12, 2);
            $table->decimal('yearly_tax', 12, 2);
            $table->decimal('monthly_tax', 12, 2);
            $table->decimal('city_tax', 12, 2);
            $table->decimal('quarterly_amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_brackets');
    }
};
