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

    Route::prefix('categories')->group(function () {
        Route::post('/', 'CategoryController@create');
        Route::get('/', 'CategoryController@index');
        Route::get('/popular-categories', 'CategoryController@popularCategories');
        Route::get('/names', 'CategoryController@getNames');
        Route::get('{category}', 'CategoryController@show');
        Route::get('{category}/products', 'CategoryController@getProducts');
        Route::post('/update/{category}', 'CategoryController@update');
        Route::delete('{category}', 'CategoryController@destroy');
        Route::get('{category}/products/stock', 'CategoryController@countStockByCategory');
    });

    Route::prefix('products')->group(function () {
        Route::get('/', 'ProductController@index');
        Route::get('{product}', 'ProductController@show');
        Route::put('{product}', 'ProductController@update');
        Route::delete('{product}', 'ProductController@destroy');
        Route::put('{product}/toggle-featured', 'ProductController@toggleFeatured');
        Route::post('{product}/categories/attach', 'ProductController@attachCategories');
        Route::post('{product}/categories/detach', 'ProductController@detachCategories');
    });

    Route::prefix('wanted-products')->group(function () {
        Route::get('/', 'WantedProductController@index');
        Route::get('/{wantedProduct}', 'WantedProductController@show');
    });

    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('resend-otp', 'AuthController@resendOtp');

    // Route::middleware('auth:sanctum')->group(function () {
    //     Route::get('/user', function (Request $request) {
    //         return $request->user();
    //     });
    // });

    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::post('verify-otp', 'AuthController@verifyOtp');
        Route::post('/products', 'ProductController@create');
        Route::post('/wanted-products', 'WantedProductController@create');

        Route::get('users/profile/wanted-products', 'UserController@myWantedProducts');
        Route::get('users/profile/products', 'UserController@myProducts');
        Route::get('users/profile/recent-search', 'UserController@myRecentSearches');

        Route::get('user', 'AuthController@me');
        Route::post('logout', 'AuthController@logout');
    });
});
