<?php

use App\Models\Dealer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect('/digimon/login');
});


Route::get('test', function() {
    return Dealer::with(['users' => function ($query) {
        $query->role('mras')
            ->withCount(['serviceReminders' => function ($q) {
                $q->whereDate('created_at', '>=', '2025-10-11')
                    ->whereDate('created_at', '<=', '2025-10-11');
            }]);
    }])->get();
});


Route::get('clear-logs', function(){
    return File::put(storage_path('logs/laravel.log'), '');
});
