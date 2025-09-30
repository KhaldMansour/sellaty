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
                    __('messages.product_rejected_inappropriate')
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
                __('messages.product_rejected_wrong_category')
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
