<?php

namespace App\Models;

use App\Enums\ReminderAttempt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
    }

    // new implements
    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->where('has_completed', false);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('has_completed', true);
    }

    public function scopeAssignedToMras(Builder $query, int $mrasId): Builder
    {
        return $query->where('assigned_mras_id', $mrasId);
    }

    public function scopeWithoutReminders(Builder $query): Builder
    {
        return $query->whereDoesntHave('reminders');
    }

    public function getNameWithDealerAttribute(): string
    {
        return $this->name . '(' . $this->dealer?->acronym . ')';
    }

    public function next(): ?Service
    {
        $next = static::where('assigned_mras_id', auth()->id())
            ->where('id', '>', $this->id)
            ->orderBy('id', 'asc')
            ->first();

        if (!$next) {
            $next = static::where('assigned_mras_id', auth()->id())
                ->orderBy('id', 'asc')
                ->first();
        }
        return $next;
    }
}
