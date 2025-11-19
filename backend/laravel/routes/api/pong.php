<?php

use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong from Laravel API',
    ]);
});

Route::get('/pong', function () {
    return response()->json([
        'message' => 'pong from Laravel API',
    ]);
});
