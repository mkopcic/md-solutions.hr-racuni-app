<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TaxBracket extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'from_amount', 'to_amount', 'yearly_base', 'yearly_tax', 'monthly_tax', 'city_tax', 'quarterly_amount',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['from_amount', 'to_amount', 'yearly_base', 'yearly_tax', 'monthly_tax', 'city_tax', 'quarterly_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('tax_brackets');
    }

    public static function findForAmount($amount)
    {
        return self::where('from_amount', '<=', $amount)
            ->where('to_amount', '>=', $amount)
            ->first();
    }
}
