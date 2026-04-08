<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\RealisationController;

/*
|--------------------------------------------------------------------------
| API Routes - Tricycle App
|--------------------------------------------------------------------------
| Public API endpoints for the OKAMI website to consume.
| All endpoints are prefixed with /api/v1/
|
| Base URL (staging):    https://tricycle.newtechnologyhub.org/api/v1
| Base URL (production): https://tricycle.okamisarl.org/api/v1
|
| ┌──────────────────────────────────┬────────┬──────────────────────┐
| │ ENDPOINT                         │ METHOD │ DESCRIPTION          │
| ├──────────────────────────────────┼────────┼──────────────────────┤
| │ /realisations                    │ GET    │ Liste paginée        │
| │ /realisations/categories         │ GET    │ Catégories dispo     │
| │ /realisations/latest             │ GET    │ Dernières réal.      │
| │ /realisations/{id}               │ GET    │ Détail réalisation   │
| └──────────────────────────────────┴────────┴──────────────────────┘
*/

Route::prefix('v1')->group(function () {

    // ─── Réalisations (public, read-only) ───
    Route::prefix('realisations')->group(function () {
        Route::get('/', [RealisationController::class, 'index']);
        Route::get('/categories', [RealisationController::class, 'categories']);
        Route::get('/latest', [RealisationController::class, 'latest']);
        Route::get('/{id}', [RealisationController::class, 'show']);
    });


});

