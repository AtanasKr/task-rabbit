<?php

use Illuminate\Support\Facades\Route;

// v1
$router->group(['prefix' => 'api'], function () use ($router) {
    require 'api/pong.php';
});
