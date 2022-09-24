<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\KidsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth'])
    ->name('home.index');

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth'])
    ->name('home.index');

Route::middleware(['auth', 'acl'])->group(function () {

    // checklists
    Route::get('checklists/{id}/chart', [ChecklistController::class, 'chart'])->name('checklists.chart');
    Route::get('checklists/{id}/fill', [ChecklistController::class, 'fill'])->name('checklists.fill');
    Route::get('checklists/register', [ChecklistController::class, 'register'])->name('checklists.register');
    Route::resource('checklists', ChecklistController::class);

    // kids
    Route::resource('kids', KidsController::class);

    // roles
    Route::resource('roles', RoleController::class);

    // users
    Route::get('users/{id}/pdf', [UserController::class, 'pdf'])->name('users.pdf');
    Route::resource('users', UserController::class);
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
