<?php

use App\Http\Controllers\Api\ChartController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\ChecklistRegisterController;
use App\Http\Controllers\Api\CompetenceController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\KidController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\PlaneController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth'], function () {});

// levels
Route::apiResource('levels', LevelController::class);

// domains
Route::get('domains/initials/{level_id}', [DomainController::class, 'getInitials'])->name('api.domains.initials');
Route::apiResource('domains', DomainController::class);

// competences
Route::apiResource('competences', CompetenceController::class);

// checklists
Route::apiResource('checklists', ChecklistController::class);

// checklists
Route::get('kids/byuser/{user_id}', [KidController::class, 'byuser'])->name('api.byuser');
Route::apiResource('kids', KidController::class);

// planes
Route::get('planes/newplane', [PlaneController::class, 'newPlane'])->name('api.planes.newplane');
Route::get('planes/deleteplane', [PlaneController::class, 'deletePlane'])->name('api.planes.deleteplane');
Route::get('planes/storeplane', [PlaneController::class, 'storePlane'])->name('api.planes.storeplane');
Route::get('planes/showcompetences/{plane_id}', [PlaneController::class, 'showCompetences'])->name('api.planes.showcompetences');
Route::get('planes/showbykids/{kid_id}/{checklist_id}', [PlaneController::class, 'showByKids'])->name('api.planes.showbykids');
Route::apiResource('planes', PlaneController::class);

// checklistregisters
Route::post('/checklistregisters/single', [ChecklistRegisterController::class, 'storeSingle']);
Route::get('checklistregisters/progressbar/{checklist_id}/{level_id}', [ChecklistRegisterController::class, 'progressbar'])->name('api.checklistregisters.progressbar');
Route::apiResource('checklistregisters', ChecklistRegisterController::class);

// charts
Route::get('charts/percentage', [ChartController::class, 'percentage'])->name('api.charts.percentage');
