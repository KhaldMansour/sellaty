<?php

namespace App\Jobs;

use App\Factories\NotificationPayloadFactory;
use App\Models\Product;
use App\Models\Category;
use App\Services\RekognitionService;
use App\Services\FirebaseNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ValidateProductImagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $data;
    protected Product $product;

    public function __construct()
    {
    }

    public function handle(
        RekognitionService $rekognitionService,
        FirebaseNotificationService $firebaseNotificationService
    ): void {
        Log::info("🚀 Dispatching ValidateProductImagesJob at " . now());

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
    }
}
