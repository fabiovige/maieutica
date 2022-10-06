<?php

use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\LevelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompetenceController;
use App\Http\Controllers\Api\ChecklistRegisterController;
use App\Http\Controllers\Api\ChartController;
use App\Http\Controllers\Api\PlaneController;

Route::group(['middleware' => 'auth'], function() {

});

// levels
Route::apiResource('levels', LevelController::class);

// domains
Route::get('domains/initials/{level_id}', [DomainController::class, 'getInitials'])->name('api.domains.initials');
Route::apiResource('domains', DomainController::class);

// competences
Route::apiResource('competences', CompetenceController::class);

// checklists
Route::apiResource('checklists', ChecklistController::class);

// planes
Route::get('planes/storeplane', [PlaneController::class, 'storePlane'])->name('api.planes.storeplane');
Route::get('planes/showcompetences/{plane_id}', [PlaneController::class, 'showCompetences'])->name('api.planes.showcompetences');
Route::get('planes/showbykids/{kid_id}', [PlaneController::class, 'showByKids'])->name('api.planes.showbykids');
Route::apiResource('planes', PlaneController::class);

// checklistregisters
Route::get('checklistregisters/progressbar/{checklist_id}/{level_id}', [ChecklistRegisterController::class, 'progressbar'])->name('api.checklistregisters.progressbar');
Route::apiResource('checklistregisters', ChecklistRegisterController::class);

// charts
Route::get('charts/percentage', [ChartController::class, 'percentage'])->name('api.charts.percentage');


