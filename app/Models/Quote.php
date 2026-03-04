<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Quote extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'customer_id', 'quote_number', 'quote_year', 'quote_type',
        'issue_date', 'delivery_date', 'valid_until', 'note', 'internal_notes',
        'total_amount', 'subtotal', 'tax_total', 'payment_method',
        'status', 'converted_to_invoice_id', 'converted_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'delivery_date' => 'date',
        'valid_until' => 'date',
        'converted_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'quote_year' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function convertedToInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_to_invoice_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->valid_until && now()->greaterThan($this->valid_until) && ! in_array($this->status, ['accepted', 'rejected']));
    }

    public function isConverted(): bool
    {
        return $this->converted_to_invoice_id !== null;
    }

    public function getFullQuoteNumberAttribute(): string
    {
        if (! $this->quote_number || ! $this->quote_year) {
            return 'PON-'.$this->id;
        }

        return "PON-{$this->quote_number}/{$this->quote_year}";
    }

    public function formatDate($date): string
    {
        if (! $date) {
            return '-';
        }

        return $date->format('d.m.Y');
    }

    public function convertToInvoice(): Invoice
    {
        if ($this->isConverted()) {
            throw new \Exception('Ponuda je već konvertirana u račun.');
        }

        // Kreiraj račun iz ponude
        $invoice = Invoice::create([
            'customer_id' => $this->customer_id,
            'invoice_type' => $this->quote_type,
            'issue_date' => now(),
            'delivery_date' => $this->delivery_date ?? now(),
            'due_date' => now()->addDays(15),
            'note' => $this->note,
            'advance_note' => $this->internal_notes,
            'payment_method' => $this->payment_method,
            'total_amount' => $this->total_amount,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'status' => 'unpaid',
        ]);

        // Kopiraj stavke
        foreach ($this->items as $quoteItem) {
            $invoice->items()->create([
                'service_id' => $quoteItem->service_id,
                'name' => $quoteItem->name,
                'quantity' => $quoteItem->quantity,
                'unit' => $quoteItem->unit,
                'price' => $quoteItem->price,
                'discount' => $quoteItem->discount,
                'total' => $quoteItem->total,
                'tax_rate' => $quoteItem->tax_rate,
                'tax_amount' => $quoteItem->tax_amount,
            ]);
        }

        // Označi ponudu kao konvertiranu
        $this->update([
            'converted_to_invoice_id' => $invoice->id,
            'converted_at' => now(),
            'status' => 'accepted',
        ]);

        return $invoice;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['customer_id', 'quote_number', 'quote_year', 'quote_type', 'issue_date', 'delivery_date', 'valid_until', 'note', 'internal_notes', 'total_amount', 'subtotal', 'tax_total', 'payment_method', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('quotes');
    }
}
