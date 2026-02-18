<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'incoming_invoice_id',
        'name',
        'description',
        'quantity',
        'unit',
        'price',
        'total',
        'tax_rate',
        'kpd_code',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    /**
     * Relacija prema ulaznom računu
     */
    public function incomingInvoice(): BelongsTo
    {
        return $this->belongsTo(IncomingInvoice::class);
    }

    /**
     * Izračunaj PDV iznos za stavku
     */
    public function getTaxAmountAttribute(): float
    {
        return $this->total * ($this->tax_rate / 100);
    }

    /**
     * Ukupno s PDV-om
     */
    public function getTotalWithTaxAttribute(): float
    {
        return $this->total + $this->tax_amount;
    }
}
