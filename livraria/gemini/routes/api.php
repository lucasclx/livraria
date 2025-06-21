<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->get('/example', function (Request $request) {
    return ['message' => 'API Example'];
});
