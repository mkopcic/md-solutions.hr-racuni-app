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
        Schema::create('incoming_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_invoice_id')->constrained()->onDelete('cascade');

            // Podaci stavke
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 20)->default('kom');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(25.00);

            // KPD 2025 kod
            $table->string('kpd_code', 10)->nullable();

            $table->timestamps();

            // Indeks
            $table->index('incoming_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_invoice_items');
    }
};
