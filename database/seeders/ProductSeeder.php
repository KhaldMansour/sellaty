<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run()
    {
        if (Product::count() > 0) {
            return;
        }

        $faker = Faker::create();

        $productCount = 2;

        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        $files = Storage::disk('public')->files('products');

        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }

        $categoryIds = Category::take(10)->pluck('id')->toArray();

        $userIds = User::take(10)->pluck('id')->toArray();

        if (empty($userIds) || empty($categoryIds)) {
            return;
        }

        foreach (range(1, $productCount) as $index) {
            $userId = $userIds[array_rand($userIds)];
            $categoryId = $categoryIds[array_rand($categoryIds)];

            $imageFolder = database_path('seeders/seeded_data/products');
            $imageFiles = array_diff(scandir($imageFolder), ['..', '.']);

            $product = Product::create([
                'name' => $faker->word(),
                'description' => $faker->sentence(),
                'price' => $faker->randomFloat(2, 5, 100),
                'quantity' => $faker->numberBetween(1, 50),
                'featured' => $faker->boolean,
                'user_id' => $userId,
            ]);

            $product->categories()->attach($categoryId);

            $imageFolder = database_path('seeders/seeded_data/products');

            foreach ($imageFiles as $imageFile) {
                $productImageUrl = $this->handleProductImage($imageFolder, $imageFile);

                if ($productImageUrl) {
                    $product->images()->create([
                        'image_url' => $productImageUrl,
                    ]);
                }
            }
        }
    }

    protected function handleProductImage($imageFolder, $imageFile)
    {
        $imagePath = $imageFolder . '/' . $imageFile;

        if (file_exists($imagePath)) {
            $extension = pathinfo($imageFile, PATHINFO_EXTENSION);
            $baseName = pathinfo($imageFile, PATHINFO_FILENAME);
            $uniqueName = $baseName . '_' . uniqid() . '.' . $extension;

            $imageStoragePath = 'products/' . $uniqueName;

            Storage::disk('public')->put($imageStoragePath, file_get_contents($imagePath));

            return asset('storage/' . $imageStoragePath);
        }

        return null;
    }
}
