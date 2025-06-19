<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\CompetencesController;
use App\Http\Controllers\KidsController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Rotas de Autenticação
Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);

    // Rotas de Reset de Senha
    Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');

    Route::post('password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    Route::get('password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Redirecionar raiz para login ou home
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home.index')
        : redirect()->route('login');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->middleware(['auth'])->name('home.index');

Route::middleware(['auth'])->group(function () {

    // checklists
    Route::get('checklists/{id}/chart', [ChecklistController::class, 'chart'])->name('checklists.chart');
    Route::get('checklists/{id}/fill', [ChecklistController::class, 'fill'])->name('checklists.fill');
    Route::get('checklists/register', [ChecklistController::class, 'register'])->name('checklists.register');
    Route::get('checklists/{id}/clonar', [ChecklistController::class, 'clonarChecklist'])->name('checklists.clonar');
    Route::resource('checklists', ChecklistController::class);

    // kids
    Route::get('kids/{kidId}/overview', [KidsController::class, 'overview'])->name('kids.overview');
    Route::get('kids/{kidId}/level/{levelId}/overview', [KidsController::class, 'overview'])->name('kids.overview.level');

    Route::get('kids/{id}/pdfplane', [KidsController::class, 'pdfPlane'])->name('kids.pdfplane');
    Route::get('kids/pdfplaneauto/{id}/{checklistId}/{note}', [KidsController::class, 'pdfPlaneAuto'])->name('kids.pdfplaneauto');
    Route::get('kids/pdfplaneautoview/{id}/{checklistId}/{planeId}', [KidsController::class, 'pdfPlaneAutoView'])->name('kids.pdfplaneautoview');
    Route::get('kids/{kid}/show-plane{checklistId?}', [KidsController::class, 'showPlane'])->name('kids.showPlane');
    Route::get('kids/{kid}/eye', [KidsController::class, 'eyeKid'])->name('kids.eye');
    Route::post('kids/{kid}/upload-photo', [KidsController::class, 'uploadPhoto'])->name('kids.upload.photo');
    Route::resource('kids', KidsController::class);

    // roles
    Route::resource('roles', RoleController::class);

    // users
    Route::get('users/{id}/pdf', [UserController::class, 'pdf'])->name('users.pdf');
    Route::resource('users', UserController::class);

    // competences
    Route::get('/competences/domains-by-level/{level_id}', [CompetencesController::class, 'getDomainsByLevel'])->name('competences.domainsByLevel');
    Route::get('/competences/clear-filters', [CompetencesController::class, 'clearFilters'])->name('competences.clearFilters');
    Route::resource('competences', CompetencesController::class);

    // plane automatic
    Route::get('kid/plane-automatic/{kidId}/{checklistId}', [App\Http\Controllers\PlaneAutomaticController::class, 'index'])->name('kid.plane-automatic');

    // profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // professionals
    Route::resource('professionals', ProfessionalController::class);
    Route::patch('professionals/{professional}/deactivate', [ProfessionalController::class, 'deactivate'])->name('professionals.deactivate');
    Route::patch('professionals/{professional}/activate', [ProfessionalController::class, 'activate'])->name('professionals.activate');
    Route::put('professionals/{professional}', [ProfessionalController::class, 'update'])
        ->name('professionals.update')
        ->middleware('auth');
});

// Data Table Ajax
Route::get('checklists/datatable/index', [checklistController::class, 'index_data'])->name('checklists.index_data')->middleware(['auth']);
Route::get('kids/datatable/index', [KidsController::class, 'index_data'])->name('kids.index_data')->middleware(['auth']);
Route::get('roles/datatable/index', [RoleController::class, 'index_data'])->name('roles.index_data')->middleware(['auth']);
Route::get('users/datatable/index', [UserController::class, 'index_data'])->name('users.index_data')->middleware(['auth']);

Route::get('logs', function () {
    $message = 'This is a sample message for Test.';
    Log::emergency($message);
    Log::alert($message);
    Log::critical($message);
    Log::error($message);
    Log::warning($message);
    Log::notice($message);
    Log::info($message);
    Log::debug($message);
});

// Route::get('/teste',  [KidsController::class, 'teste'])->name('kids.teste');
// Route::get('/teste/{kidId}/level/{levelId}', [KidsController::class, 'showRadarChart'])->name('kids.radarChart');
Route::get('/analysis/{kidId}/level/{levelId}/{firstChecklistId?}/{secondChecklistId?}', [KidsController::class, 'showRadarChart2'])->name('kids.radarChart2');
Route::get('/{kidId}/level/{levelId}/domain/{domainId}/checklist/{checklistId?}', [KidsController::class, 'showDomainDetails'])->name('kids.domainDetails');
// routes/web.php
Route::post('/kids/{kidId}/overview/generate-pdf', [KidsController::class, 'generatePdf'])->name('kids.generatePdf');
