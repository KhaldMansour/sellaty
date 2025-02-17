<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSplashScreenRequest;
use App\Models\SplashScreen;

class SplashScreenController extends Controller
{
    public function index()
    {
        $splashScreens = SplashScreen::all();
        return $this->success($splashScreens, 'Splash screens retrieved successfully');
    }

    public function create(StoreSplashScreenRequest $request)
    {
        $imagePath = $request->file('image')->store('splash_images', 'public');
        $imageUrl = asset('storage/' . $imagePath);
        $validatedData = $request->validated();
        $validatedData['image_url'] = $imageUrl;

        $splashScreen = SplashScreen::create($validatedData);

        return $this->success($splashScreen , 'Splash screen created successfully', 201);
    }


    public function show(SplashScreen $splashScreen){
        return $this->success($splashScreen, 'Splash screen retrieved successfully');
    }
}
