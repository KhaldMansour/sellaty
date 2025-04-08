<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    public function run()
    {
        if (Category::count() > 0) {
            return;
        }

        $faker = Faker::create();

        $imageFolder = database_path('seeders/seeded_data/categories');

        if (!Storage::disk('public')->exists('categories')) {
            Storage::disk('public')->makeDirectory('categories');
        }

        $files = Storage::disk('public')->files('categories');

        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }

        $categoryCount = 10;

        foreach (range(1, $categoryCount) as $index) {
            $imageUrl = $this->handleCategoryImage($imageFolder, $index);

            if ($imageUrl) {
                $categoryData = [
                    'name' => json_encode([
                        'en' => $faker->word,
                        'ar' => $faker->word,
                    ]),
                    'description' => json_encode([
                        'en' => $faker->sentence,
                        'ar' => $faker->sentence,
                    ]),
                    'image_url' => $imageUrl,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ];

                DB::table('categories')->insert($categoryData);
            }
        }
    }

    protected function handleCategoryImage($imageFolder, $index)
    {
        $imageName = 'category_' . $index . '.png';

        $imagePath = $imageFolder . '/' . $imageName;

        if (file_exists($imagePath)) {
            $imageStoragePath = 'categories/'. uniqid() . $imageName;
            Storage::disk('public')->put($imageStoragePath, file_get_contents($imagePath));

            return asset('storage/' . $imageStoragePath);
        }

        return null;
    }
}
