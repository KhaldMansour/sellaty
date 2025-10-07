<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Category;
use App\Services\RekognitionService;
use App\Services\FirebaseNotificationService;
use App\Factories\NotificationPayloadFactory;

class ValidateProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:validate-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate pending product images using Rekognition and update status';

    /**
     * Execute the console command.
     */
    public function handle(
        RekognitionService $rekognitionService,
        FirebaseNotificationService $firebaseNotificationService
    ): int {
        Log::info("🔍 ValidateProductImages started at " . now());

        $productRejected = false;

        $products = Product::where('status', Product::STATUS_PENDING)
            ->with(['images', 'categories', 'seller'])
            ->get();

        $carCategoryId = Category::where('name_en', 'Vehicles')->first()->id;

        foreach ($products as $product) {
            $unverifiedImages = $product->images()
                ->where(function ($query) {
                    $query->where('is_nsfw', true)
                        ->orWhere('scanned', false);
                })
                ->get();

            if ($unverifiedImages->contains('is_nsfw', true)) {
                Log::info("⏩ Product #{$product->id} skipped (already has NSFW image)");
                $product->update(['status' => Product::STATUS_REJECTED]);
                continue;
            };

            foreach ($unverifiedImages as $image) {
                Log::info("Checking image {$image->image_url} for product #{$product->id}");

                $results = $rekognitionService->analyzeImage($image->image_url);

                if (! $results['is_safe']) {
                    $product->update(['status' => Product::STATUS_REJECTED]);

                    $notification = NotificationPayloadFactory::productRejected(
                        $product,
                        [
                            'en' => __('messages.product_rejected_inappropriate', [], 'en'),
                            'ar' => __('messages.product_rejected_inappropriate', [], 'ar'),
                        ]
                    );

                    $firebaseNotificationService->sendNotification($product->seller, $notification);

                    Log::warning("❌ Product #{$product->id} rejected (unsafe image)");

                    $productRejected = true;
                    $image->update(['scanned' => true, 'is_nsfw' => true]);
                    break;
                }
                if ($results['contains_car'] && ! $product->categories->pluck('id')->contains($carCategoryId)) {
                    $product->update(['status' => Product::STATUS_REJECTED]);

                    $notification = NotificationPayloadFactory::productRejected(
                        $product,
                        [
                            'en' => __('messages.product_rejected_wrong_category', [], 'en'),
                            'ar' => __('messages.product_rejected_wrong_category', [], 'ar'),
                        ]
                    );

                    $firebaseNotificationService->sendNotification(
                        $product->seller,
                        $notification
                    );

                    Log::warning("🚫 Product #{$product->id} rejected (car in wrong category)");

                    $productRejected = true;
                    $image->update(['scanned' => true]);

                    break;
                }
                $image->update(['scanned' => true]);
            }

            if ($productRejected) {
                continue;
            }

            $product->update(['status' => Product::STATUS_ACTIVE]);
            Log::info("✅ Product #{$product->id} approved and activated");
        }

        Log::info("✅ ValidateProductImages finished at " . now());

        return Command::SUCCESS;
    }
}
