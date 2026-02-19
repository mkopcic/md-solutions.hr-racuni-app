<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Business extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name', 'address', 'oib', 'in_vat_system', 'business_space_label', 'cash_register_label',
        'iban', 'email', 'phone', 'location', 'months_active', 'logo_path',
    ];

    protected $casts = [
        'in_vat_system' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'address', 'oib', 'in_vat_system', 'business_space_label', 'cash_register_label',
                'iban', 'email', 'phone', 'location', 'months_active', 'logo_path'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('business');
    }
}
