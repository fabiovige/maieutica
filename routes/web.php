<?php

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

    // kids
    Route::resource('kids', KidsController::class);

    // roles
    Route::resource('roles', RoleController::class);
    Route::get('roles/{role}/resources', [RoleController::class, 'syncResources'])->name('roles.resources');
    Route::put('roles/{role}/resources', [RoleController::class, 'updateSyncResources'])->name('roles.resources.update');
    Route::get('roles/delete/{id}', [RoleController::class, 'delete'])->name('delete');

    // users
    Route::resource('users', UserController::class);
    Route::get('users/{id}/pdf', [UserController::class, 'pdf'])->name('users.pdf');
});

// Data Table Ajax
Route::get('kids/datatable/index', [KidsController::class, 'index_data'])->name('kids.index_data')->middleware(['auth']);
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
