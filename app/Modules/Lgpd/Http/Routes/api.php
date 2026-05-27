<?php

use App\Modules\Lgpd\Http\Controllers\Api\KidSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LGPD Module API Routes
|--------------------------------------------------------------------------
|
| Rotas API do módulo LGPD. Endpoints JSON consumidos pelos
| componentes Vue montados nas views Blade.
|
| O ConsentForm.vue utiliza Select2 AJAX para buscar titulares (kids).
| O endpoint /api/lgpd/kids/search aceita o parâmetro ?q= para filtrar
| por nome, retornando resultados no formato esperado pelo Select2.
|
*/

Route::prefix('api/lgpd')->middleware(['web', 'auth'])->group(function () {
    // ─── Busca de titulares (kids) para Select2 AJAX ─────────────────
    Route::get('/kids/search', [KidSearchController::class, 'search'])
        ->middleware('can:lgpd-consent-manage')
        ->name('api.lgpd.kids.search');
});
