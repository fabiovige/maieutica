<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\CompetenceController;
use App\Http\Controllers\CompetenceItemController;
use App\Http\Controllers\KidController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Auth::routes();


Route::view('/dashboard','dashboard')->name('dashboard');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth'])
    ->name('home.index');

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware(['auth'])
    ->name('home.index');

Route::middleware(['auth', 'acl'])->group(function () {

    // kids
    Route::resource('kids', KidController::class);

    // roles
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/resources', [RoleController::class, 'syncResources'])->name('roles.resources');
    Route::put('roles/{role}/resources', [RoleController::class, 'updateSyncResources'])->name('roles.resources.update');
    Route::get('roles/delete/{id}', [RoleController::class, 'delete'])->name('delete');

    // users
    Route::resource('users', UserController::class);
    Route::get('users/{id}/pdf', [UserController::class, 'pdf'])->name('users.pdf');

    // compotences
    Route::resource('competences', CompetenceController::class);

    // competence_items
    Route::resource('competenceItems', CompetenceItemController::class);

    // checklists
    Route::resource('checklists', ChecklistController::class);
    Route::get('checklists/create/checklist/{kid}', [ChecklistController::class, 'createChecklist'])->name('checklists.createChecklist');

    // levels
    //Route::resource('competences', UserController::class);

    // competence_items
    //Route::resource('competence_items', UserController::class);
});

// Data Table
Route::get('kids/datatable/index', [KidController::class, 'index_data'])->name('kids.index_data')->middleware(['auth']);
Route::get('roles/datatable/index', [RoleController::class, 'index_data'])->name('roles.index_data')->middleware(['auth']);
Route::get('users/datatable/index', [UserController::class, 'index_data'])->name('users.index_data')->middleware(['auth']);

//Route::get('rota', function () {
//    foreach (Route::getRoutes()->getRoutes() as $rota) {
//        echo $rota->getName().'<hr/>';
//    }
//});

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
