<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/splash_screens', [App\Http\Controllers\Api\V1\SplashScreenController::class, 'create']);
Route::get('/splash_screens', [App\Http\Controllers\Api\V1\SplashScreenController::class, 'index']);
Route::get('/splash_screens/{splashScreen}', [App\Http\Controllers\Api\V1\SplashScreenController::class, 'show']);
