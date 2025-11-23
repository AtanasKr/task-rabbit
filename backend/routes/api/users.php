<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::delete('{user}', 'destroy');
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('/', 'index');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
