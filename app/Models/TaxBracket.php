<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxBracket extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_amount', 'to_amount', 'yearly_base', 'yearly_tax', 'monthly_tax', 'city_tax', 'quarterly_amount',
    ];

    public static function findForAmount($amount)
    {
        return self::where('from_amount', '<=', $amount)
            ->where('to_amount', '>=', $amount)
            ->first();
    }
}
