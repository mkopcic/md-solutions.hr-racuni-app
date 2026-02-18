<?php

namespace App\Models;

use App\Enums\EracunStatus;
use App\Enums\FinaStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EracunLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'incoming_invoice_id',
        'direction',
        'message_id',
        'fina_invoice_id',
        'ubl_xml',
        'request_xml',
        'response_xml',
        'status',
        'fina_status',
        'error_message',
        'retry_count',
        'sent_at',
        'retried_at',
        'status_checked_at',
    ];

    protected $casts = [
        'status' => EracunStatus::class,
        'fina_status' => FinaStatus::class,
        'retry_count' => 'integer',
        'sent_at' => 'datetime',
        'retried_at' => 'datetime',
        'status_checked_at' => 'datetime',
    ];

    /**
     * Relacija prema izlaznom računu (Invoice)
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relacija prema ulaznom računu (IncomingInvoice)
     */
    public function incomingInvoice(): BelongsTo
    {
        return $this->belongsTo(IncomingInvoice::class);
    }

    /**
     * Scope za izlazne račune
     */
    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'outgoing');
    }

    /**
     * Scope za ulazne račune
     */
    public function scopeIncoming($query)
    {
        return $query->where('direction', 'incoming');
    }

    /**
     * Scope za neuspješne logove
     */
    public function scopeFailed($query)
    {
        return $query->where('status', EracunStatus::FAILED);
    }

    /**
     * Scope za logove koji čekaju retry
     */
    public function scopePendingRetry($query)
    {
        return $query->where('status', EracunStatus::FAILED)
            ->where('retry_count', '<', 3);
    }

    /**
     * Je li log za izlazni račun
     */
    public function isOutgoing(): bool
    {
        return $this->direction === 'outgoing';
    }

    /**
     * Je li log za ulazni račun
     */
    public function isIncoming(): bool
    {
        return $this->direction === 'incoming';
    }

    /**
     * Može li se pokušati ponovno slanje
     */
    public function canRetry(): bool
    {
        return $this->status === EracunStatus::FAILED && $this->retry_count < 3;
    }
}
