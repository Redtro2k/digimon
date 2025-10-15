<?php

namespace App\Models;

use App\Enums\ReminderAttempt;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    //
    protected $guarded = [];

    protected $appends = ['category_title'];
    protected $casts = [
        'attempt' => ReminderAttempt::class,
        'sub_result' => StatusEnum::class,
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getCategoryTitleAttribute(): string
    {
        return $this->load('category')->category->name;
    }
}
