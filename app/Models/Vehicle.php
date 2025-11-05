<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    //
    protected $guarded = [];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function services(): \Illuminate\Database\Eloquent\Relations\HasMany|Vehicle
    {
        return $this->hasMany(Service::class, 'vehicle_id', 'id');
    }
}
