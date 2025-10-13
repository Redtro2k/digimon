<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Service extends Model
{
    //
    protected $casts = [
        'has_fpm' => 'boolean',
        'forecast_date' => 'datetime',
    ];

    public $fillable = [
        'personal_provider_number',
        'company_provider_number'
    ];
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

    public function assignedMras(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_mras_id');
    }

    public function reminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reminder::class);
    }
}
