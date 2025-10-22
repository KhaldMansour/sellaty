<?php

namespace App\Jobs;

use App\Factories\NotificationPayloadFactory;
use App\Models\Product;
use App\Models\Category;
use App\Models\WantedProduct;
use App\Services\RekognitionService;
use App\Services\FirebaseNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ValidateWantedProductImagesJob implements ShouldQueue
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
        Log::info("🔍 ValidateWantedProductImages started at " . now());

        $wantedProductRejected = false;

        $wantedProducts = WantedProduct::where('status', WantedProduct::STATUS_PENDING)
            ->with(['images', 'categories', 'buyer'])
            ->get();

        $carCategoryId = Category::where('name_en', 'Vehicles')->first()->id;

        $wantedProductRejected = false;

        foreach ($wantedProducts as $wantedProduct) {
            $unverifiedImages = $wantedProduct->images()
                ->where(function ($query) {
                    $query->where('is_nsfw', true)
                        ->orWhere('scanned', false);
                })
                ->get();

            if ($unverifiedImages->contains('is_nsfw', true)) {
                Log::info("⏩ Wanted Product #{$wantedProduct->id} skipped (already has NSFW image)");
                $wantedProduct->update(['status' => WantedProduct::STATUS_REJECTED]);
                continue;
            };
            foreach ($unverifiedImages as $image) {
                Log::info("Checking image {$image->image_url} for wanted product #{$wantedProduct->id}");

                $results = $rekognitionService->analyzeImage($image->image_url);

                if (! $results['is_safe']) {
                    $wantedProduct->update(['status' => Product::STATUS_REJECTED]);

                    $notification = NotificationPayloadFactory::productRejected(
                        $wantedProduct,
                        [
                            'en' => __('messages.product_rejected_inappropriate', [], 'en'),
                            'ar' => __('messages.product_rejected_inappropriate', [], 'ar'),
                        ]
                    );

                    $firebaseNotificationService->sendNotification($wantedProduct->buyer, $notification);

                    Log::warning("❌ Product #{$wantedProduct->id} rejected (unsafe image)");

                    $wantedProductRejected = true;
                    $image->update(['scanned' => true, 'is_nsfw' => true]);
                    break;
                }

                if ($results['contains_car'] && ! $wantedProduct->categories->pluck('id')->contains($carCategoryId)) {
                    $wantedProduct->update(['status' => Product::STATUS_REJECTED]);

                    $notification = NotificationPayloadFactory::productRejected(
                        $wantedProduct,
                        [
                            'en' => __('messages.product_rejected_wrong_category', [], 'en'),
                            'ar' => __('messages.product_rejected_wrong_category', [], 'ar'),
                        ]
                    );

                    $firebaseNotificationService->sendNotification(
                        $wantedProduct->buyer,
                        $notification
                    );

                    Log::warning("🚫 Wanted Product #{$wantedProduct->id} rejected (car in wrong category)");

                    $wantedProductRejected = true;
                    $image->update(['scanned' => true]);

                    break;
                }

                $image->update(['scanned' => true]);
            }

            if ($wantedProductRejected) {
                continue;
            }

            $wantedProduct->update(['status' => WantedProduct::STATUS_ACTIVE]);
            Log::info("✅ Wanted Product #{$wantedProduct->id} approved and activated");
        }

        Log::info("✅ ValidateWantedProductImages finished at " . now());
    }
}
