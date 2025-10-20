<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\ValidateProductImagesJob;

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
    public function handle(): int
    {
        Log::info("🚀 Dispatching ValidateProductImagesJob at " . now());

        ValidateProductImagesJob::dispatch()->onConnection('database');

        return Command::SUCCESS;
    }
}
