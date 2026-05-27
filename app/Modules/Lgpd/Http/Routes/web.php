<?php

use App\Modules\Lgpd\Http\Controllers\AccessLogController;
use App\Modules\Lgpd\Http\Controllers\ComplianceReportController;
use App\Modules\Lgpd\Http\Controllers\ConsentController;
use App\Modules\Lgpd\Http\Controllers\DataRequestController;
use App\Modules\Lgpd\Http\Controllers\RetentionPolicyController;
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
    // ─── Consentimentos ──────────────────────────────────────────────
    Route::middleware(['can:lgpd-consent-list'])->group(function () {
        Route::get('/consents', [ConsentController::class, 'index'])->name('lgpd.consents.index');
        Route::get('/consents/datatable', [ConsentController::class, 'datatable'])->name('lgpd.consents.datatable');
        Route::get('/consents/{id}', [ConsentController::class, 'show'])
            ->middleware('can:lgpd-consent-show')
            ->name('lgpd.consents.show');
        Route::post('/consents', [ConsentController::class, 'store'])
            ->middleware('can:lgpd-consent-manage')
            ->name('lgpd.consents.store');
        Route::post('/consents/{id}/revoke', [ConsentController::class, 'revoke'])
            ->middleware('can:lgpd-consent-manage')
            ->name('lgpd.consents.revoke');
    });

    // ─── Access Logs ─────────────────────────────────────────────────
    Route::middleware(['can:lgpd-access-log-view'])->group(function () {
        Route::get('/access-logs', [AccessLogController::class, 'index'])->name('lgpd.access-logs.index');
        Route::get('/access-logs/datatable', [AccessLogController::class, 'datatable'])->name('lgpd.access-logs.datatable');
    });

    // ─── Data Requests (Requisições de Direitos) ─────────────────────
    Route::middleware(['can:lgpd-request-list'])->group(function () {
        Route::get('/requests', [DataRequestController::class, 'index'])->name('lgpd.requests.index');
        Route::get('/requests/datatable', [DataRequestController::class, 'datatable'])->name('lgpd.requests.datatable');
        Route::get('/requests/{id}', [DataRequestController::class, 'show'])
            ->middleware('can:lgpd-request-show')
            ->name('lgpd.requests.show');
        Route::post('/requests', [DataRequestController::class, 'store'])
            ->middleware('can:lgpd-request-manage')
            ->name('lgpd.requests.store');
        Route::post('/requests/{id}/assign', [DataRequestController::class, 'assign'])
            ->middleware('can:lgpd-request-manage')
            ->name('lgpd.requests.assign');
        Route::post('/requests/{id}/complete', [DataRequestController::class, 'complete'])
            ->middleware('can:lgpd-request-manage')
            ->name('lgpd.requests.complete');
    });

    // ─── Retention Policies ──────────────────────────────────────────
    Route::middleware(['can:lgpd-retention-list'])->group(function () {
        Route::get('/retention-policies', [RetentionPolicyController::class, 'index'])->name('lgpd.retention.index');
        Route::post('/retention-policies', [RetentionPolicyController::class, 'store'])
            ->middleware('can:lgpd-retention-manage')
            ->name('lgpd.retention.store');
        Route::put('/retention-policies/{id}', [RetentionPolicyController::class, 'update'])
            ->middleware('can:lgpd-retention-manage')
            ->name('lgpd.retention.update');
    });

    // ─── Reports (Relatório de Conformidade) ─────────────────────────
    Route::middleware(['can:lgpd-report-generate'])->group(function () {
        Route::get('/reports/compliance', [ComplianceReportController::class, 'form'])->name('lgpd.reports.form');
        Route::post('/reports/compliance', [ComplianceReportController::class, 'generate'])->name('lgpd.reports.generate');
    });
});
