<?php

use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\SetLocale;
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

    Route::prefix('chats')->group(function () {
        Route::get('/chat', 'ChatController@index');
        Route::get('/chat-test', 'ChatController@indexTest');
        Route::post('/messages-test', 'ChatMessageController@sendTest');
    });

    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('resend-otp', 'AuthController@resendOtp');


    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::post('verify-otp', 'AuthController@verifyOtp');
        Route::post('/products', 'ProductController@create');
        Route::post('/wanted-products', 'WantedProductController@create');

        Route::prefix('users')->group(function () {
            Route::get('profile/wanted-products', 'UserController@myWantedProducts');
            Route::get('profile/products', 'UserController@myProducts');
            Route::get('profile/wanted-products', 'UserController@myWantedProducts');
            Route::get('profile/recent-search', 'UserController@myRecentSearches');
            Route::get('/profile', 'UserController@profile');
            Route::post('/profile/update', 'UserController@updateProfile');
        });

        Route::prefix('chats')->group(function () {
            Route::post('products/{product}', 'ChatController@getOrCreate');
            Route::get('buyer', 'ChatController@buyerChats');
            Route::get('seller', 'ChatController@sellerChats');
            Route::get('my-chats', 'ChatController@myChats');
            Route::get('messages/{chatMessage}/media', 'ChatMessageController@getMedia')->name('chat-uploads');
            Route::post('{chat}/messages', 'ChatMessageController@send');
            Route::get('{chat}/messages', 'ChatMessageController@messages');
            Route::post('{chat}/seen', 'ChatMessageController@markAsSeen');
        });

        Route::prefix('offers')->group(function () {
            Route::post('products/{product}', 'OfferController@create');
            Route::put('{offer}/status', 'OfferController@updateStatus');
        });

        Route::prefix('likes')->group(function () {
            Route::post('toggle-like', 'LikeController@toggle');
            Route::get('liked-users', 'LikeController@getLikedUsers');
            Route::get('liked-products', 'LikeController@getLikedProducts');
        });

        Route::get('user', 'AuthController@me');
        Route::post('logout', 'AuthController@logout');
    });

    Route::prefix('users')->group(function () {
        Route::get('{user}', 'UserController@getUserData');
        Route::get('{user}/products', 'ProductController@sellerActiveProducts');
        Route::get('{user}/wanted-products', 'WantedProductController@buyerActiveWantedProducts');
    });
});
