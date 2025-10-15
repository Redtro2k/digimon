<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(Service::class)]
class Service extends Model
{
    //
    protected $casts = [
        'has_fpm' => 'boolean',
        'forecast_date' => 'datetime',
    ];
    protected $guarded = [];
    protected $appends = ['title'];
    public function vehicle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\HasOneThrough|Service
    {
        return $this->hasOneThrough(
            Customer::class,
            Vehicle::class,
            'id',
            'id',
            'vehicle_id',
            'customer_id'
        );
    }

    public function getTitleAttribute(): string
    {
        return $this->customer->customer_name;
    }
    public function assignedMras(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_mras_id');
    }
    public function reminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reminder::class);
    }
    public function latestReminder()
    {
        return $this->hasOne(Reminder::class)->ofMany('attempt', 'max');
    } // get the latest reminder
}
