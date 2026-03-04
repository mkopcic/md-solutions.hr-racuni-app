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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->nullable();
            $table->year('quote_year')->nullable();
            $table->string('quote_type', 10)->default('R');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('issue_date');
            $table->date('delivery_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->text('note')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('payment_method', 50)->default('virman');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_total', 10, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('converted_to_invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
