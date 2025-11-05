<?php

use App\Models\Category;
use App\Models\Service;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;

Route::get('/', function () {
    return redirect('/digimon/login');
});

Route::get('test', function() {

    $test = Service::find(144);
    dd($test->next(), auth()->id());
//    return $hangupActivities->sum(function($activity){
//        $duration = $activity->properties['duration'] ?? '00:00:00';
//
//        if (preg_match('/(\d{2}):(\d{2}):(\d{2})/', $duration, $matches)) {
//            return ($matches[1] * 3600) + ($matches[2] * 60) + $matches[3];
//        }
//        return 0;
//    });
});
