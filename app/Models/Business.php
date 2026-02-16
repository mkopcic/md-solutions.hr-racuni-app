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
        'name', 'address', 'oib', 'iban', 'email', 'phone', 'location', 'months_active', 'logo_path',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'address', 'oib', 'iban', 'email', 'phone', 'location', 'months_active', 'logo_path'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('business');
    }
}
