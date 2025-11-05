<?php

namespace App\Models;

use App\Enums\MobileNumberEnum;
use App\Observers\CustomerObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(CustomerObserver::class)]
class Customer extends Model
{
    protected $guarded = [];

    protected $casts = [
      'provider' => MobileNumberEnum::class,
    ];

    public function vehicles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
