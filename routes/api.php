<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompetenceController;
use App\Http\Controllers\Api\ChecklistRegisterController;

// Route::group(['middleware' => 'auth:sanctum'], function() {

// });

Route::get('competences', [CompetenceController::class, 'index'])->name('api.competences');
Route::get('competence/descriptions', [CompetenceController::class, 'competenceDescriptions'])->name('api.competence.descriptions');
Route::get('competence/checklistRegister', [CompetenceController::class, 'checklistRegister'])->name('api.competence.checklistRegister');

Route::apiResource('checklistregisters', ChecklistRegisterController::class);
