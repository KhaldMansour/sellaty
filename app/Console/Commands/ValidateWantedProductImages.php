<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\RekognitionService;
use App\Services\FirebaseNotificationService;
use App\Jobs\ValidateWantedProductImagesJob;

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
        Log::info("🚀 Dispatching ValidateWantedProductImagesJob at " . now());

        ValidateWantedProductImagesJob::dispatch()->onConnection('database');

        return Command::SUCCESS;
    }
}
