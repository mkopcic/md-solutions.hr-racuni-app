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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('issue_date');
            $table->date('delivery_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('note')->nullable();
            $table->text('advance_note')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_cash', 10, 2)->default(0);
            $table->decimal('paid_transfer', 10, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
