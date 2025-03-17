<?php

use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([SetLocale::class])->namespace('App\Http\Controllers\API\V1')->group(function () {
    Route::prefix('splash-screens')->group(function () {
        Route::get('/', 'SplashScreenController@index');
        Route::get('{splashScreen}', 'SplashScreenController@show');
        Route::post('/', 'SplashScreenController@create');
        Route::post('update/{splashScreen}', 'SplashScreenController@update');
        Route::delete('{splashScreen}', 'SplashScreenController@delete');
    });

    Route::prefix('intro-messages')->group(function () {
        Route::get('/', 'IntroMessageController@index');
        Route::get('{introMessage}', 'IntroMessageController@show');
        Route::post('/', 'IntroMessageController@create');
        Route::post('update/{introMessage}', 'IntroMessageController@update');
        Route::delete('{introMessage}', 'IntroMessageController@delete');
    });


    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');

    // Route::middleware('auth:sanctum')->group(function () {
    //     Route::get('/user', function (Request $request) {
    //         return $request->user();
    //     });
    // });

    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::post('verify-otp', 'AuthController@verifyOtp');
        Route::get('user', 'AuthController@me');
        Route::post('logout', 'AuthController@logout');
    });
});
