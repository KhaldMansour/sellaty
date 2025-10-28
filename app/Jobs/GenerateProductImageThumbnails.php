<?php

namespace App\Jobs;

use App\Models\ProductImage;
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

class GenerateProductImageThumbnails implements ShouldQueue
{
    use Dispatchable;

    use Queueable;
    use InteractsWithQueue;
    use SerializesModels;

    protected ProductImage $productImage;

    public function __construct(ProductImage $productImage)
    {
        $this->productImage = $productImage;
    }

    public function handle(): void
    {
        $url = $this->productImage->image_url;
        $path = str_replace(config('app.url') . '/storage/', '', $url);

        if (!Storage::disk('public')->exists($path)) {
            Log::warning("File not found for thumbnail generation: {$path}");

            return;
        }

        $filename = uniqid() . '.jpg';

        $origFullPath = Storage::disk('public')->path($path);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($origFullPath);


        $image->orient()->resize(300, 300, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $encodedImage = $image->encode(new JpegEncoder(quality: 70));

        $mainPath = "products/{$filename}";

        Storage::disk('public')->put($mainPath, (string) $encodedImage);

        $this->productImage->update([
            'thumbnail_path' => $mainPath
        ]);
    }

    public function failed(\Throwable $exception): void
    {
    }
}
