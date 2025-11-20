<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;

Route::middleware('auth:sanctum')->prefix('tasks/{task}/comments')->controller(CommentController::class)->group(function () {
    Route::post('/', 'store');
    Route::get('/', 'index');
});