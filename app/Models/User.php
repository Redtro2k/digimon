<?php

namespace App\Models;

use App\Enums\Gender;
use Filament\Facades\Filament;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasAvatar
{
    use HasFactory, Notifiable, HasRoles;

    protected $casts = ['profile' => 'array', 'gender' => Gender::class];
    protected $fillable = [
        'username',
        'name',
        'email',
        'gender',
        'password',
        'service_id',
        'status',
        'started_at',
        'ended_at',
        'duration',
        'current_attempt',
        'profile',
        'last_login_dt',
        'logged_dt',
        'has_customer_attend'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function dealer(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Dealer::class, 'users_dealers');
    }
    public function queued()
    {
        return $this->hasOne(Queued::class);
    }

    public function serviceReminders(){
        return $this->hasMany(Reminder::class, 'assigned_to');
    }

    protected function userAvatar(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile ?? $this->gender->avatar() . str($this->name) ->headline()
                ->replace(' ', '+')
        );
    }
    public function getFilamentAvatarUrl(): ?string
    {
       return $this->profile;
    }
}
