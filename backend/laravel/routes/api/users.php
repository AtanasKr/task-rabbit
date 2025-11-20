<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware(['auth:sanctum', 'role:admin'])->get('/users', [UserController::class, 'index']);
