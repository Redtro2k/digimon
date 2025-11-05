<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queued extends Model
{
    //
    protected $table = 'queued';
    protected $guarded = [];

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
