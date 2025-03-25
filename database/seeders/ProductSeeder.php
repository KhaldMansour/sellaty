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

        $productCount = 10;

        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        $categoryIds = Category::take(10)->pluck('id')->toArray();

        $userIds = User::take(10)->pluck('id')->toArray();

        if (empty($userIds) || empty($categoryIds)) {
            return;
        }

        // Loop to create the products
        foreach (range(1, $productCount) as $index) {
            $userId = $userIds[array_rand($userIds)];
            $categoryId = $categoryIds[array_rand($categoryIds)];

            // Create a new product
            $product = Product::create([
                'name' =>[
                    'en' => $faker->word,
                    'ar' => $faker->word,
                ],
                'description' => [
                    'en' => $faker->sentence,
                    'ar' => $faker->sentence,
                ],
                'price' => $faker->randomFloat(2, 5, 100), // Price between 5 and 100
                'quantity' => $faker->numberBetween(1, 50), // Quantity between 1 and 50
                'featured' => $faker->boolean,
                'user_id' => $userId,
            ]);

            $product->categories()->attach($categoryId);

            $imageFolder = database_path('seeders/seeded_data/products');

            $productImageUrl = $this->handleProductImage($imageFolder, $index);

            if ($productImageUrl) {
                $product->images()->create([
                    'image_url' => $productImageUrl,
                ]);
            };
        }
    }

    protected function handleProductImage($imageFolder, $index)
    {
        $imageName = 'product_' . $index . '.png';

        $imagePath = $imageFolder . '/' . $imageName;

        if (file_exists($imagePath)) {
            $imageStoragePath = 'products/' . $imageName;
            Storage::disk('public')->put($imageStoragePath, file_get_contents($imagePath));

            return asset('storage/' . $imageStoragePath);
        }

        return null;
    }
}
