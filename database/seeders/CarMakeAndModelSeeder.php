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
        $carMakesImages = require database_path('seeders/seeded_data/cars/CarMakes.php');
        $imageFolder = database_path('seeders/seeded_data/categories');

        $imageUrl = $this->handleCategoryImage($imageFolder, 'cars');

        $carCategory = Category::firstOrCreate(
            ['name->en' => 'Cars'],
            [
                'name->ar' => 'سيارات',
                'image_url' => $imageUrl,
            ]
        );

        $response = Http::accept('text/plain')
            ->post(config('carapi.api_url') . 'auth/login', [
                'api_token' => config('carapi.api_key'),
                'api_secret' => config('carapi.api_secret'),
            ]);

        if ($response->successful()) {
            $token = $response->body();
        } else {
            $this->command->error('Failed to authenticate.');

            return;
        };

        $models = $this->getCarModels($token);


        $makeField = $carCategory->customFields()->firstOrCreate(
            ['name' => 'make'],
            [
                'type' => 'select',
                'required' => true,
            ]
        );

        $modelField = $carCategory->customFields()->firstOrCreate(
            ['name' => 'model'],
            [
                'type' => 'select',
                'required' => true,
            ]
        );

        $yearField = $carCategory->customFields()->firstOrCreate(
            ['name' => 'year'],
            [
                'type' => 'year',
                'required' => true,
            ]
        );

        $carCategory->customFields()->each(function ($field) {
            $field->options()->delete();
        });

        $grouped = $models->groupBy('make');

        foreach ($grouped as $make => $modelsList) {
            $makeOption = CustomFieldOption::firstOrCreate([
                'custom_field_id' => $makeField->id,
                'value' => $make,
            ], [
                'image_url' => $carMakesImages[strtoupper($make)] ?? null,
            ]);

            $modelNames = $modelsList->pluck('name')->unique()->values();

            $modelNames->chunk(100)->each(function ($chunk) use ($modelField, $makeOption) {
                $insertData = [];

                foreach ($chunk as $modelName) {
                    $insertData[] = [
                        'custom_field_id' => $modelField->id,
                        'value' => $modelName,
                        'parent_option_id' => $makeOption->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                CustomFieldOption::insert($insertData);
            });
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

    public function getCarModels($token)
    {
        $models = collect();

        $initialResponse = Http::withToken($token)
            ->get(config('carapi.api_url') . "models/v2?limit=150&page=1");

        if (!$initialResponse->ok()) {
            $this->command->error('Failed to fetch initial data');

            return;
        }

        $json = $initialResponse->json();
        $models = $models->merge($json['data']);
        $totalPages = $json['collection']['pages'] ?? 1;

        for ($page = 2; $page <= $totalPages; $page++) {
            $response = Http::withToken($token)
                ->get(config('carapi.api_url') . "models/v2?limit=150&page=$page");

            if (!$response->ok()) {
                $this->command->error("Failed to fetch page $page");
                break;
            }

            $json = $response->json();
            $models = $models->merge($json['data']);

            $this->command->info("Fetched {$models->count()} models so far (page $page)...");
            usleep(500000);
        }

        return $models;
    }
}
