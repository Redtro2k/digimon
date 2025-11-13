<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    //
    protected $fillable = ['acronym', 'name', 'slug'];

    public function users(){
        return $this->belongsToMany(User::class, 'users_dealers', 'dealer_id', 'user_id');
    }
}
