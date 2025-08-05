<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CustomFieldOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CarMakeAndModelSeeder extends Seeder
{
    public function run(): void
    {
        $carMakes = require database_path('seeders/seeded_data/cars/CarMakes.php');
        $imageFolder = database_path('seeders/seeded_data/categories');

        $imageUrl = $this->handleCategoryImage($imageFolder, 'cars');

        $carCategory = Category::firstOrCreate(
            ['name->en' => 'Cars'],
            [
                'name->ar' => 'سيارات',
                'image_url' => $imageUrl,
            ]
        );

        $response = Http::get('https://carapi.app/api/models/v2');

        if (!$response->ok()) {
            $this->command->error('Failed to fetch car data.');

            return;
        }

        $models = collect($response->json('data'));

        $makeField = $carCategory->customFields()->create([
            'name' => 'make',
            'type' => 'select',
            'required' => true,
        ]);

        $modelField = $carCategory->customFields()->create([
            'name' => 'model',
            'type' => 'select',
            'required' => true,
        ]);

        $carCategory->customFields()->create([
            'name' => 'year',
            'type' => 'year',
            'required' => true,
        ]);

        $grouped = $models->groupBy('make');

        foreach ($grouped as $make => $modelsList) {
            $makeOption = CustomFieldOption::firstOrCreate([
                'custom_field_id' => $makeField->id,
                'value' => $make,
                'image_url' => $carMakes[strtoupper($make)] ?? null,
            ]);

            foreach ($modelsList->pluck('name')->unique() as $modelName) {
                CustomFieldOption::firstOrCreate([
                    'custom_field_id' => $modelField->id,
                    'value' => $modelName,
                    'parent_option_id' => $makeOption->id,
                ]);
            }
        }

        $this->command->info('Car Makes and Model fields and options seeded successfully.');
    }

    protected function handleCategoryImage($imageFolder, $name)
    {
        $imageName = 'category_' . $name . '.png';
        $imagePath = $imageFolder . '/' . $imageName;

        if (file_exists($imagePath)) {
            $imageStoragePath = 'categories/' . uniqid() . '_' . $imageName;
            Storage::disk('public')->put($imageStoragePath, file_get_contents($imagePath));

            return $imageStoragePath;
        }

        $files = glob($imageFolder . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);

        if (!empty($files)) {
            $randomImagePath = $files[array_rand($files)];
            $randomImageName = basename($randomImagePath);
            $imageStoragePath = 'categories/' . uniqid() . '_' . $randomImageName;
            Storage::disk('public')->put($imageStoragePath, file_get_contents($randomImagePath));

            return $imageStoragePath;
        }

        return null;
    }
}
