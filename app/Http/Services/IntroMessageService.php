<?php

namespace App\Http\Services;

use App\Repositories\IntroMessageRepository;
use Illuminate\Support\Facades\Storage;

class IntroMessageService
{
    public function __construct(private readonly IntroMessageRepository $introMessageRepository)
    {
    }

    public function getAll($limit)
    {
        return $this->introMessageRepository->paginate($limit);
    }

    public function create($data)
    {
        return $this->introMessageRepository->create($data);
    }

    public function update($data, $splashScreen)
    {
        if (isset($data['image'])) {
            $imagePath = str_replace([url('/storage/'), 'storage/'], '', $splashScreen->image_url);
            Storage::disk('public')->delete($imagePath);

            $imagePath = request()->file('image')->store('Intro_messages__images', 'public');
            $imageUrl = asset('storage/' . $imagePath);
            $data['image_url'] = $imageUrl;
        }

        return $this->introMessageRepository->update($data, $splashScreen->id);
    }

    public function delete($id)
    {
        return $this->introMessageRepository->delete($id);
    }
}
