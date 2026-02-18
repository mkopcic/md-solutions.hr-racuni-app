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
        Schema::create('eracun_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('incoming_invoice_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('direction', ['outgoing', 'incoming']);

            // FINA identifikatori
            $table->string('message_id')->unique();
            $table->string('fina_invoice_id')->nullable();

            // XML podaci
            $table->longText('ubl_xml')->nullable();
            $table->longText('request_xml')->nullable();
            $table->longText('response_xml')->nullable();

            // Statusi
            $table->enum('status', ['pending', 'sending', 'sent', 'accepted', 'rejected', 'failed'])->default('pending');
            $table->enum('fina_status', ['RECEIVED', 'RECEIVING_CONFIRMED', 'APPROVED', 'REJECTED', 'PAYMENT_RECEIVED'])->nullable();

            // Greške i retry
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);

            // Timestampovi
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('retried_at')->nullable();
            $table->timestamp('status_checked_at')->nullable();
            $table->timestamps();

            // Indeksi
            $table->index('direction');
            $table->index('status');
            $table->index('fina_status');
            $table->index(['invoice_id', 'direction']);
            $table->index(['incoming_invoice_id', 'direction']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eracun_logs');
    }
};
