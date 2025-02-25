<?php

use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/splash_screens', [App\Http\Controllers\API\V1\SplashScreenController::class, 'index']);
Route::get('/splash_screens/{splashScreen}', [App\Http\Controllers\API\V1\SplashScreenController::class, 'show']);
Route::post('/splash_screens', [App\Http\Controllers\API\V1\SplashScreenController::class, 'create']);
Route::post('splash_screens/update/{splashScreen}', [App\Http\Controllers\API\V1\SplashScreenController::class, 'update']);
Route::delete('/splash_screens/{splashScreen}', [App\Http\Controllers\API\V1\SplashScreenController::class, 'delete']);


Route::post('register', [App\Http\Controllers\API\V1\AuthController::class, 'register']);
Route::post('login', [App\Http\Controllers\API\V1\AuthController::class, 'login']);

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('user', [App\Http\Controllers\API\V1\AuthController::class, 'me']);
    Route::post('logout', [App\Http\Controllers\API\V1\AuthController::class, 'logout']);
});
