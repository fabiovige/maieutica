<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LGPD Module Web Routes
|--------------------------------------------------------------------------
|
| Rotas web do módulo LGPD. Todas as rotas possuem prefixo /lgpd
| e requerem autenticação + permissões específicas.
|
*/

Route::prefix('lgpd')->middleware(['web', 'auth'])->group(function () {
    // Rotas serão adicionadas nas tasks subsequentes
});
