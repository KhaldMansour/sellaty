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

class ValidateProductImagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected array $data;
    protected Product $product;

    public function __construct(array $data, Product $product)
    {
        $this->data = $data;
        $this->product = $product;
    }

    public function handle(RekognitionService $rekognitionService, FirebaseNotificationService $firebaseNotificationService)
    {
        $hasCar = false;

        foreach ($this->data['image_paths'] as $image) {
            if (! $rekognitionService->isSafe($image)) {
                $this->product->update(['status' => Product::STATUS_REJECTED]);

                $notification = NotificationPayloadFactory::productRejected(
                    $this->product,
                    "Ad rejected: one or more images appear to contain inappropriate content, which isn't allowed. Please remove those images and resubmit. If you believe your photos are appropriate, please send them to our Admin Team for review"
                );


                $firebaseNotificationService->sendNotification(
                    $this->product->seller,
                    $notification
                );

                return;
            }

            if ($rekognitionService->containsCar($image)) {
                $hasCar = true;
            }
        }

        $carCategoryId = Category::where('name_en', 'Vehicles')->first()->id;

        if ($hasCar && ! in_array($carCategoryId, $this->data['category_ids'])) {
            $this->product->update(['status' => Product::STATUS_REJECTED]);

            $notification = NotificationPayloadFactory::productRejected(
                $this->product,
                "Ad rejected: your photos appear to show Vehicles, but the selected category isn't 'Vehicles'. Please move your ad to 'Vehicle' or change the photos."
            );

            $firebaseNotificationService->sendNotification(
                $this->product->seller,
                $notification
            );

            return;
        }

        $this->product->update(['status' => Product::STATUS_ACTIVE]);

        return;
    }
}
