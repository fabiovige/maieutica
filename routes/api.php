<?php

use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\LevelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompetenceController;
use App\Http\Controllers\Api\ChecklistRegisterController;

Route::group(['middleware' => 'auth'], function() {

});

Route::apiResource('levels', LevelController::class);

Route::get('domains/initials/{level_id}', [DomainController::class, 'getInitials'])->name('api.domains.initials');
Route::apiResource('domains', DomainController::class);
Route::apiResource('competences', CompetenceController::class);

Route::get('checklistregisters/progressbar/{checklist_id}/{level_id}', [ChecklistRegisterController::class, 'progressbar'])->name('api.checklistregisters.progressbar');
Route::apiResource('checklistregisters', ChecklistRegisterController::class);


