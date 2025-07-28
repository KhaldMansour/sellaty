<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Gd\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class ProcessProductImages implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $imagePath;
    protected $product;

    public function __construct(string $tempPath, Product $product)
    {
        $this->imagePath = $tempPath;
        $this->product = $product;
    }

    public function handle()
    {
        $filename = uniqid() . '.jpg';

        $manager = new ImageManager(new Driver());

        $fullPath = $this->imagePath;

        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/public/' . ltrim($this->imagePath, '/'));
        }

        if (!file_exists($fullPath)) {
            throw new \Exception("File not found at path: {$fullPath}");
        }

        $image = $manager->read($fullPath)
            ->resize(1500, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

        $encodedImage = $image->encode(new JpegEncoder(quality: 50));

        $mainPath = "products/{$filename}";

        Storage::disk('public')->put($mainPath, (string) $encodedImage);

        ProductImage::create([
            'image_url' => config('app.url') .  Storage::url($mainPath),
            'product_id' => $this->product->id
        ]);

        unlink($fullPath);
    }
}
