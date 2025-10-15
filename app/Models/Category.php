<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $casts = [
        'status' => StatusEnum::class,
    ];
    protected $guarded = [];
}
