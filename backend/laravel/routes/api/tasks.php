<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('tasks')->controller(TaskController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('{task}', 'show');
        Route::put('{task}', 'update');
        Route::post('/assign', 'assignTask');
        Route::patch('{task}/complete', [TaskController::class, 'markComplete']);
        Route::patch('{task}/close', [TaskController::class, 'close']);
    });
});
