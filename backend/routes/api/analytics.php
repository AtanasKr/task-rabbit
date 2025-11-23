<?php
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::prefix('analytics')->controller(AnalyticsController::class)->group(function () {
        Route::get('/', 'stats');
    });
});
