<?php

namespace App\Http\Services;

use App\Repositories\SplashScreenRepository;
use Illuminate\Support\Facades\Storage;

class SplashScreenService
{
    public function __construct(private readonly SplashScreenRepository $splashScreenRepository)
    {
    }

    public function getAll($limit)
    {
        return $this->splashScreenRepository->paginate($limit);
    }

    public function create($data)
    {
        return $this->splashScreenRepository->create($data);
    }

    public function update($data, $splashScreen)
    {
        if (isset($data['image'])) {
            $imagePath = str_replace([url('/storage/'), 'storage/'], '', $splashScreen->image_url);
            Storage::disk('public')->delete($imagePath);

            $imagePath = request()->file('image')->store('splash_images', 'public');
            $imageUrl = asset('storage/' . $imagePath);
            $data['image_url'] = $imageUrl;
        }

        return $this->splashScreenRepository->update($data, $splashScreen->id);
    }

    public function delete($id)
    {
        return $this->splashScreenRepository->delete($id);
    }
}
