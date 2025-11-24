<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::prefix('tasks')->controller(TaskController::class)->group(function () {
        Route::patch('{task}/close', [TaskController::class, 'close']);
        Route::put('{task}', 'update');
        Route::delete('{task}', 'destroy');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('tasks')->controller(TaskController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('{task}', 'show');
        Route::post('/assign', 'assignTask');
        Route::patch('{task}/complete', [TaskController::class, 'markComplete']);
    });
});
