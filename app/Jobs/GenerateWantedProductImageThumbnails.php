<?php

namespace App\Jobs;

use App\Models\WantedProductImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Gd\Encoders\JpegEncoder;

class GenerateWantedProductImageThumbnails implements ShouldQueue
{
    use Dispatchable;

    use Queueable;
    use InteractsWithQueue;
    use SerializesModels;

    protected ?WantedProductImage $wantedProductImage = null;

    public function __construct(WantedProductImage $wantedProductImage)
    {
        $this->wantedProductImage = $wantedProductImage;
    }

    public function handle(): void
    {
        $url = $this->wantedProductImage->image_url;
        $path = str_replace(config('app.url') . '/storage/', '', $url);
        if (!Storage::disk('public')->exists($path)) {
            Log::warning("File not found for thumbnail generation: {$path}");

            return;
        }

        $filename = uniqid() . '.jpg';

        $origFullPath = Storage::disk('public')->path($path);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($origFullPath);


        $encodedImage = $image->orient()->scaleDown(300, 300)
        ->encode(new JpegEncoder(quality: 80));

        $mainPath = "wanted-products/thumbnails/{$filename}";

        Storage::disk('public')->put($mainPath, (string) $encodedImage);

        $this->wantedProductImage->update([
            'thumbnail_path' => $mainPath
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::warning("Error happened in generating wanted product thumbnail: {$exception->getMessage()}");
    }
}
