<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LGPD Module API Routes
|--------------------------------------------------------------------------
|
| Rotas API do módulo LGPD. Endpoints JSON consumidos pelos
| componentes Vue montados nas views Blade.
|
*/

Route::prefix('api/lgpd')->middleware(['web', 'auth'])->group(function () {
    // Rotas API serão adicionadas nas tasks subsequentes
});
