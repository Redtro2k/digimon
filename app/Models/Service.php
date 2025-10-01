<?php

namespace App\Models;

use App\NavigationGroup;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    //
    protected $guarded = [];
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
}
