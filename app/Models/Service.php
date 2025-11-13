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
    protected $appends = ['title', 'first_reminder', 'second_reminder', 'third_reminder'];

    public function getFirstReminderAttribute(){
//       return $this->reminders[0]->load('category') ?? null;
        if($this->reminders->count() == 1){
            return $this->reminders[0]->load('category') ?? null;
        }
        return null;
    }
    public function getSecondReminderAttribute(){
        if($this->reminders->count() == 2){
            return $this->reminders[1]->load('category') ?? null;
        }
        return null;
    }
//
    public function getThirdReminderAttribute(){
        if($this->reminders->count() == 3){
            return $this->reminders[2]->load('category') ?? null;
        }
        return null;
    }

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
        return $this->load('customer')->customer->customer_name;
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
