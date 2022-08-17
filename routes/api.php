<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => 'auth:sanctum'], function() {
    Route::get('competences', [\App\Http\Controllers\Api\CompetenceController::class, 'index'])
    ->name('api.competences');
});
