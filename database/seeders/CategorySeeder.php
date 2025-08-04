<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $categoryNames = [
            ['en' => 'Home', 'ar' => 'الرئيسيه'],
            ['en' => 'Electronics', 'ar' => 'الكترونيات'],
        ];
        $categoryCount = count($categoryNames);

        foreach (range(1, $categoryCount) as $index) {
            $name = $categoryNames[$index - 1];
            $imageUrl = $this->handleCategoryImage($imageFolder, $name['en']);
            $slug = Str::of($name['en'])
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '_')
                ->trim('_');


            if ($imageUrl) {
                $categoryData = [
                    'name' => json_encode([
                        'en' => $name['en'],
                        'ar' => $name['ar'],
                    ]),
                    'description' => json_encode([
                        'en' => $faker->sentence,
                        'ar' => $faker->sentence,
                    ]),
                    'slug' => $slug . '-' . $index,
                    'image_url' => $imageUrl,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ];

                DB::table('categories')->insert($categoryData);
            }
        }
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
