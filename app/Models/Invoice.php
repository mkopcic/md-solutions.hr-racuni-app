<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'customer_id', 'issue_date', 'delivery_date', 'due_date', 'note', 'advance_note',
        'total_amount', 'paid_cash', 'paid_transfer', 'payment_date',
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
        'paid_cash' => 'decimal:2',
        'paid_transfer' => 'decimal:2',
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
        return ($this->paid_cash + $this->paid_transfer) >= $this->total_amount;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return !$this->isPaid() && $this->due_date && now()->greaterThan($this->due_date);
    }

    /**
     * Get the remaining amount to be paid
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->total_amount - ($this->paid_cash + $this->paid_transfer));
    }

    /**
     * Get the status as a string
     */
    public function getStatus(): string
    {
        if ($this->isPaid()) {
            return 'paid';
        } elseif ($this->isOverdue()) {
            return 'overdue';
        } else {
            return 'unpaid';
        }
    }

    /**
     * Format a date in local format
     */
    public function formatDate($date): string
    {
        if (!$date) {
            return '-';
        }

        return $date->format('d.m.Y');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['customer_id', 'issue_date', 'delivery_date', 'due_date', 'note', 'advance_note', 'total_amount', 'paid_cash', 'paid_transfer', 'payment_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('invoices');
    }
}
