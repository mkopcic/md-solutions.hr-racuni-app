<?php

namespace App\Models;

use App\Enums\IncomingInvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IncomingInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_oib',
        'supplier_name',
        'supplier_address',
        'supplier_city',
        'supplier_postal_code',
        'supplier_iban',
        'invoice_number',
        'fina_invoice_id',
        'issue_date',
        'due_date',
        'payment_method',
        'subtotal',
        'tax_total',
        'total_amount',
        'currency',
        'ubl_xml',
        'status',
        'notes',
        'rejection_reason',
        'received_at',
        'reviewed_at',
        'reviewed_by',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'paid_at',
        'archived_at',
    ];

    protected $casts = [
        'status' => IncomingInvoiceStatus::class,
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'received_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * Relacija prema stavkama
     */
    public function items(): HasMany
    {
        return $this->hasMany(IncomingInvoiceItem::class);
    }

    /**
     * Relacija prema e-račun logu
     */
    public function eracunLog(): HasOne
    {
        return $this->hasOne(EracunLog::class);
    }

    /**
     * Korisnik koji je pregledao
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Korisnik koji je odobrio
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Korisnik koji je odbio
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Scope za račune koji čekaju pregled
     */
    public function scopePendingReview($query)
    {
        return $query->where('status', IncomingInvoiceStatus::PENDING_REVIEW);
    }

    /**
     * Scope za odobrene račune
     */
    public function scopeApproved($query)
    {
        return $query->where('status', IncomingInvoiceStatus::APPROVED);
    }

    /**
     * Scope za odbijene račune
     */
    public function scopeRejected($query)
    {
        return $query->where('status', IncomingInvoiceStatus::REJECTED);
    }

    /**
     * Scope za plaćene račune
     */
    public function scopePaid($query)
    {
        return $query->where('status', IncomingInvoiceStatus::PAID);
    }

    /**
     * Scope za arhivirane račune
     */
    public function scopeArchived($query)
    {
        return $query->where('status', IncomingInvoiceStatus::ARCHIVED);
    }

    /**
     * Odobri račun
     */
    public function approve(int $userId): bool
    {
        if (! $this->status->canTransitionTo(IncomingInvoiceStatus::APPROVED)) {
            return false;
        }

        $this->update([
            'status' => IncomingInvoiceStatus::APPROVED,
            'approved_at' => now(),
            'approved_by' => $userId,
        ]);

        return true;
    }

    /**
     * Odbij račun
     */
    public function reject(int $userId, string $reason): bool
    {
        if (! $this->status->canTransitionTo(IncomingInvoiceStatus::REJECTED)) {
            return false;
        }

        $this->update([
            'status' => IncomingInvoiceStatus::REJECTED,
            'rejected_at' => now(),
            'rejected_by' => $userId,
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Označi kao plaćeno
     */
    public function markAsPaid(): bool
    {
        if (! $this->status->canTransitionTo(IncomingInvoiceStatus::PAID)) {
            return false;
        }

        $this->update([
            'status' => IncomingInvoiceStatus::PAID,
            'paid_at' => now(),
        ]);

        return true;
    }

    /**
     * Arhiviraj račun
     */
    public function archive(): bool
    {
        if (! $this->status->canTransitionTo(IncomingInvoiceStatus::ARCHIVED)) {
            return false;
        }

        $this->update([
            'status' => IncomingInvoiceStatus::ARCHIVED,
            'archived_at' => now(),
        ]);

        return true;
    }

    /**
     * Je li račun istekao (prošao due_date)
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() &&
               ! in_array($this->status, [IncomingInvoiceStatus::PAID, IncomingInvoiceStatus::ARCHIVED]);
    }

    /**
     * Dani do dospijeća
     */
    public function daysUntilDue(): int
    {
        return now()->diffInDays($this->due_date, false);
    }
}
