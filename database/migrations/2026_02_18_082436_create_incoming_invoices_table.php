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
        Schema::create('incoming_invoices', function (Blueprint $table) {
            $table->id();

            // Supplier (dobavljač koji ti šalje)
            $table->string('supplier_oib', 11);
            $table->string('supplier_name');
            $table->string('supplier_address')->nullable();
            $table->string('supplier_city')->nullable();
            $table->string('supplier_postal_code', 10)->nullable();
            $table->string('supplier_iban')->nullable();

            // Podatci o računu
            $table->string('invoice_number');
            $table->string('fina_invoice_id')->unique();
            $table->date('issue_date');
            $table->date('due_date');
            $table->enum('payment_method', ['virman', 'gotovina', 'kartica'])->default('virman');

            // Iznosi
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_total', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');

            // UBL original
            $table->longText('ubl_xml');

            // Status workflow
            $table->enum('status', ['received', 'pending_review', 'approved', 'rejected', 'paid', 'archived'])->default('received');

            // Dodatni podaci
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Timestampovi
            $table->timestamp('received_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            // Indeksi
            $table->index('supplier_oib');
            $table->index('status');
            $table->index('issue_date');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_invoices');
    }
};
