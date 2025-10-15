<?php

use App\Http\Controllers\API\V1\PageController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([SetLocale::class])->namespace('App\Http\Controllers\API\V1')->group(function () {
    Route::middleware([JwtMiddleware::class])->group(function () {
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
            Route::post('{category}/custom-fields', 'CategoryController@addCustomField');
        });

        Route::prefix('custom-fields')->group(function () {
            Route::get('/{customField}', 'CustomFieldController@optionsByField');
            Route::get('/custom-field-options/{customFieldOptionId}', 'CustomFieldController@childrenByOption');
            Route::get('/{customField}/options-with-product-count', 'CustomFieldController@fieldWithProductCounts');
        });

        Route::prefix('options')->group(function () {
            Route::post('/search', 'CustomFieldController@search');
            Route::post('/search-makes', 'CustomFieldController@searchCarMakes');
            Route::get('/{optionValue}/product-count', 'CustomFieldController@getOptionWithProductCount');
            Route::get('/{optionValue}/products', 'CustomFieldController@getProductsByOptionValue');
        });

        Route::prefix('products')->group(function () {
            Route::post('{product}', 'ProductController@update');
            Route::delete('{product}', 'ProductController@destroy');
            Route::put('{product}/toggle-featured', 'ProductController@toggleFeatured');
            Route::post('{product}/categories/attach', 'ProductController@attachCategories');
            Route::post('{product}/categories/detach', 'ProductController@detachCategories');
            Route::post('filter', 'ProductController@filter');
            Route::delete('/images/{image}', 'ProductController@deleteImage');
        });

        Route::prefix('wanted-products')->group(function () {
            Route::get('/', 'WantedProductController@index');
            Route::get('/{wantedProduct}', 'WantedProductController@show');
            Route::delete('{wantedProduct}', 'WantedProductController@destroy');
            Route::post('{wantedProduct}', 'WantedProductController@update');
        });

        Route::prefix('chats')->group(function () {
            Route::get('/chat', 'ChatController@index');
            Route::get('/chat-test', 'ChatController@indexTest');
            Route::post('/messages-test', 'ChatMessageController@sendTest');
        });

        Route::post('/products', 'ProductController@create');
        Route::post('/wanted-products', 'WantedProductController@create');

        Route::prefix('users')->group(function () {
            Route::get('profile/wanted-products', 'UserController@myWantedProducts');
            Route::get('profile/products', 'UserController@myProducts');
            Route::get('profile/recent-search', 'UserController@myRecentSearches');
            Route::get('/profile', 'UserController@profile');
            Route::post('/profile/update', 'UserController@updateProfile');
            Route::get('/profile/my-followers', 'UserController@myFollowers');
            Route::get('/profile/my-followings', 'UserController@myFollowings');
            Route::post('/profile/update-fcm-token', 'UserController@updateFcmToken');
            Route::delete('/profile/delete', 'UserController@delete');
            Route::put('/profile/locale', 'UserController@updateLocale');
        });

        Route::prefix('notifications')->group(function () {
            Route::get('', 'NotificationController@index');
        });

        Route::prefix('chats')->group(function () {
            Route::post('products/{id}', 'ChatController@getOrCreate');
            Route::get('buyer', 'ChatController@buyerChats');
            Route::get('seller', 'ChatController@sellerChats');
            Route::get('my-chats', 'ChatController@myChats');
            Route::get('messages/{chatMessage}/media', 'ChatMessageController@getMedia')->name('chat-uploads');
            Route::post('{chat}/messages', 'ChatMessageController@send');
            Route::get('{chat}/messages', 'ChatMessageController@messages');
            Route::get('{chat}', 'ChatController@show');
            Route::post('{chat}/mark-as-seen', 'ChatController@markAsSeen');
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

        Route::prefix('pages')->controller(PageController::class)->group(function () {
            Route::get('/', 'index')->name('pages.index');
            Route::post('/', 'store')->name('pages.store');
            Route::get('/create', 'create')->name('pages.create');
            Route::delete('/{page}', 'destroy')->name('pages.destroy');
            Route::get('/{page}/edit', 'edit')->name('pages.edit');
            Route::put('/{page}', 'update')->name('pages.update');
        });

        Route::get('user', 'AuthController@me');
        Route::post('logout', 'AuthController@logout');

        Route::prefix('users')->group(function () {
            Route::get('{user}', 'UserController@getUserData');
            Route::get('{user}/products', 'ProductController@sellerActiveProducts');
            Route::get('{user}/wanted-products', 'WantedProductController@buyerActiveWantedProducts');
            Route::get('{user}/followings', 'LikeController@getUserFollowing');
            Route::get('{user}/followers', 'LikeController@getUserFollowers');
            Route::put('/profile/locale', 'UserController@updateLocale');
        });
    });

    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('resend-otp', 'AuthController@resendOtp');
    Route::post('verify-otp', 'AuthController@verifyOtp');

    Route::get('/pages/{page}', 'PageController@show')->name('pages.show');

    Route::prefix('products')->group(function () {
        Route::get('/', 'ProductController@index');
        Route::get('search', 'ProductController@searchByName');
        Route::get('{product}', 'ProductController@show');
    });
});
