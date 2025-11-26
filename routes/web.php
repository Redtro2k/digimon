<?php

use App\Models\Dealer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect('/digimon/login');
});


Route::get('test', function() {
//    $mras = User::role('mras')->whereHas('dealer', function($query) {
//        $query->where('dealers.id', 2);
//    })->get();
    $mras = auth()->user()->dealer->first()->id;
    dd($mras);
});


Route::get('clear-logs', function(){
    return File::put(storage_path('logs/laravel.log'), '');
});
