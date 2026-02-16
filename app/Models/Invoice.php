<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'customer_id', 'invoice_number', 'invoice_year', 'invoice_type',
        'issue_date', 'delivery_date', 'due_date', 'note', 'advance_note',
        'total_amount', 'subtotal', 'tax_total', 'paid_cash', 'paid_transfer', 'payment_date', 'payment_method',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'issue_date' => 'date',
        'delivery_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'paid_cash' => 'decimal:2',
        'paid_transfer' => 'decimal:2',
        'invoice_year' => 'integer',
    ];

    /**
     * Get the customer that owns the invoice
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the invoice items for the invoice
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the KPR entry for the invoice
     */
    public function kprEntry(): HasOne
    {
        return $this->hasOne(KprEntry::class);
    }

    /**
     * Check if invoice is fully paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return in_array($this->status, ['unpaid', 'partial']) && $this->due_date && now()->greaterThan($this->due_date);
    }

    /**
     * Get the remaining amount to be paid
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->total_amount - ($this->paid_cash + $this->paid_transfer));
    }

    /**
     * Get the full invoice number (e.g. 1-1-1)
     */
    public function getFullInvoiceNumberAttribute(): string
    {
        if (! $this->invoice_number || ! $this->invoice_year) {
            return (string) $this->id;
        }

        return "{$this->invoice_number}-1-1";
    }

    /**
     * Format a date in local format
     */
    public function formatDate($date): string
    {
        if (! $date) {
            return '-';
        }

        return $date->format('d.m.Y');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['customer_id', 'invoice_number', 'invoice_year', 'invoice_type', 'issue_date', 'delivery_date', 'due_date', 'note', 'advance_note', 'total_amount', 'subtotal', 'tax_total', 'paid_cash', 'paid_transfer', 'payment_date', 'payment_method'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('invoices');
    }
}
