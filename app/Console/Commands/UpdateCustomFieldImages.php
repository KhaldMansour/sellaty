<?php

namespace App\Console\Commands;

use App\Models\CustomFieldOption;
use Illuminate\Console\Command;

class UpdateCustomFieldImages extends Command
{

    protected $signature = 'customfield:update-images';
    protected $description = 'Update CustomFieldOption image_url where parent_option_id and image_url are null';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $images = require database_path('seeders/seeded_data/cars/CarMakes.php');

        $updatedCount = 0;

        foreach ($images as $value => $imageUrl) {
            $affected = CustomFieldOption::whereNull('parent_option_id')
                ->whereNull('image_url')
                ->whereRaw('LOWER(value) = ?', [strtolower($value)])
                ->update(['image_url' => $imageUrl]);

            $updatedCount += $affected;
        }

        echo "Updated $updatedCount CustomFieldOption entries.\n";
    }
}
