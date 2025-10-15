<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Services\RekognitionService;
use App\Services\FirebaseNotificationService;
use App\Factories\NotificationPayloadFactory;
use App\Models\WantedProduct;

class ValidateWantedProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wanted-products:validate-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate pending wanted product images using Rekognition and update status';

    /**
     * Execute the console command.
     */
    public function handle(
        RekognitionService $rekognitionService,
        FirebaseNotificationService $firebaseNotificationService
    ): int {
        Log::info("🔍 ValidateProductImages started at " . now());

        $wantedProductRejected = false;

        $wantedProducts = WantedProduct::where('status', WantedProduct::STATUS_PENDING)
            ->with(['images', 'categories', 'buyer'])
            ->get();

        foreach ($wantedProducts as $wantedProduct) {
            $unverifiedImages = $wantedProduct->images()
                ->where(function ($query) {
                    $query->where('is_nsfw', true)
                        ->orWhere('scanned', false);
                })
                ->get();

            if ($unverifiedImages->contains('is_nsfw', true)) {
                Log::info("⏩ Product #{$wantedProduct->id} skipped (already has NSFW image)");
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
                $image->update(['scanned' => true]);
            }

            if ($wantedProductRejected) {
                continue;
            }

            $wantedProduct->update(['status' => Product::STATUS_ACTIVE]);
            Log::info("✅ Wanted Product #{$wantedProduct->id} approved and activated");
        }

        Log::info("✅ ValidateProductImages finished at " . now());

        return Command::SUCCESS;
    }
}
