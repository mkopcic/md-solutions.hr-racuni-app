<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class KprEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'month', 'amount', 'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Vraća mjesec kao naziv
     */
    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Siječanj', 2 => 'Veljača', 3 => 'Ožujak', 4 => 'Travanj',
            5 => 'Svibanj', 6 => 'Lipanj', 7 => 'Srpanj', 8 => 'Kolovoz',
            9 => 'Rujan', 10 => 'Listopad', 11 => 'Studeni', 12 => 'Prosinac'
        ];

        return $months[$this->month] ?? '';
    }
}
