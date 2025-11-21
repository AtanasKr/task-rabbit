<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::prefix('projects')->controller(ProjectController::class)->group(function () {
        Route::post('/', 'store');
        Route::post('{project}/add-user', 'addUser');
        Route::put('{project}', 'update');
        Route::delete('{project}', 'destroy');
        Route::post('{project}/members', [ProjectController::class, 'addMembers']);
        Route::delete('{project}/members', [ProjectController::class, 'removeMembers']);
    });
});

// User routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('projects')->controller(ProjectController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('{project}', 'show');
    });
});
