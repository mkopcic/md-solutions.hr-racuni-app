<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InvoiceItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'invoice_id', 'service_id', 'name', 'quantity', 'price', 'discount', 'total',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['invoice_id', 'service_id', 'name', 'quantity', 'price', 'discount', 'total'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('invoice_items');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
