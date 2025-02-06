<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\CompetencesController;
use App\Http\Controllers\KidsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
});

// Google Auth
Route::get('/auth/google/callback', function () {
    $user = Socialite::driver('google')->user();

    $existingUser = User::where('email', $user->getEmail())->first();

    if (!$existingUser) {
        return redirect()->route('login')->withErrors(['email' => 'Falha na autenticação.']);
    }

    $data = [
        'provider_id' => $user->getId(),
        'provider_email' => $user->getEmail(),
        'provider_avatar' => $user->getAvatar(),
    ];

    $existingUser->update($data);

    Auth::loginUsingId($existingUser->id);

    return redirect()->route('home.index');
});

// Login
Route::post('/autenticacao', function (HttpRequest $request) {

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        return redirect()->route('home.index');
    }

    return redirect()->route('login-novo')->withErrors(['email' => 'Credenciais inválidas']);

})->name('autenticacao');

// Login View
Route::get('/login-novo', function () {
    return view('layouts.guest2');
})->name('login-novo');

// Home
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login-novo');
    } else {
        return redirect()->route('home.index');
    }
});

// Logout
Route::post('/sair', function () {
    Auth::logout();
    return redirect()->route('login-novo');
})->name('sair');

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

//Route::get('/teste',  [KidsController::class, 'teste'])->name('kids.teste');
//Route::get('/teste/{kidId}/level/{levelId}', [KidsController::class, 'showRadarChart'])->name('kids.radarChart');
Route::get('/analysis/{kidId}/level/{levelId}/{checklist?}', [KidsController::class, 'showRadarChart2'])->name('kids.radarChart2');
Route::get('/{kidId}/level/{levelId}/domain/{domainId}/checklist/{checklistId?}', [KidsController::class, 'showDomainDetails'])->name('kids.domainDetails');
// routes/web.php
Route::post('/kids/{kidId}/overview/generate-pdf', [KidsController::class, 'generatePdf'])->name('kids.generatePdf');
