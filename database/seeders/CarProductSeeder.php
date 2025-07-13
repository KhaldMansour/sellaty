<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\CustomFieldOption;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CarProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $productCount = 10; // how many car products to create

        // Get the Cars category (make sure it exists)
        $carCategory = Category::where('slug', 'cars')->first();
        if (!$carCategory) {
            $this->command->error('Cars category not found. Please seed categories first.');

            return;
        }

        // Get users to assign to products
        $userIds = User::take(10)->pluck('id')->toArray();
        if (empty($userIds)) {
            $this->command->error('No users found to assign products.');

            return;
        }

        // Get Car Make and Car Model custom fields
        $carMakeField = $carCategory->customFields()->where('name', 'make')->first();
        $carModelField = $carCategory->customFields()->where('name', 'model')->first();

        // dd($carMakeField , $carModelField);


        if (!$carMakeField || !$carModelField) {
            $this->command->error('Car Make or Car Model custom fields not found.');

            return;
        }
        // Get Car Makes options (parent options, i.e., parent_option_id == null)
        $carMakes = CustomFieldOption::where('custom_field_id', $carMakeField->id)
            ->whereNull('parent_option_id')
            ->get();

        // dd($carMakes);

        if ($carMakes->isEmpty()) {
            $this->command->error('No Car Make options found.');

            return;
        }


        // Prepare images folder & clear existing product images in storage
        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }
        $files = Storage::disk('public')->files('products');
        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }

        // Load seed images from local seeder folder
        $imageFolder = database_path('seeders/seeded_data/products');
        $imageFiles = array_diff(scandir($imageFolder), ['..', '.']);

        $conditions = ['new', 'used', 'refurbished'];
        $deliveryOptions = ['pickup', 'shipping', 'local_delivery'];

        foreach (range(1, $productCount) as $index) {
            $userId = $userIds[array_rand($userIds)];

            // Pick a random Car Make
            $makeOption = $carMakes->random();

            // Get Car Models for this Make (child options of Car Model with parent_option_id = $makeOption->id)
            $carModels = CustomFieldOption::where('custom_field_id', $carModelField->id)
                ->where('parent_option_id', $makeOption->id)
                ->get();

            if ($carModels->isEmpty()) {
                continue;
            }

            $modelOption = $carModels->random();

            $product = Product::create([
                'name' => $makeOption->value . ' ' . $modelOption->value,
                'description' => $faker->sentence(),
                'price' => $faker->randomFloat(2, 5000, 80000),
                'quantity' => $faker->numberBetween(1, 10),
                'featured' => $faker->boolean(30),
                'user_id' => $userId,
                'brand' => $makeOption->name,
                'model' => $modelOption->name,
                'duration' => $faker->randomElement(['30 days', '60 days', '90 days']),
                'condition' => [
                    $faker->randomElement($conditions),
                ],
                'delivery_options' => [
                    $faker->randomElement($deliveryOptions),
                ],
                'address' => $faker->streetAddress(),
                'country' => $faker->country(),
                'state' => $faker->state(),
                'city' => $faker->city(),
                'postal_code' => $faker->postcode(),
                'listed_until' => $faker->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
                'negotiable' => $faker->boolean(50),
                'deliverable' => $faker->boolean(50),
                'currency' => 'USD',
                'longitude' => $faker->longitude(),
                'latitude' => $faker->latitude(),
            ]);

            $product->categories()->attach($carCategory->id);

            $product->customFieldValues()->updateOrCreate(
                ['custom_field_id' => $carMakeField->id],
                ['value' => $makeOption->value]
            );

            $product->customFieldValues()->updateOrCreate(
                ['custom_field_id' => $carModelField->id],
                ['value' => $modelOption->value]
            );

            foreach ($imageFiles as $imageFile) {
                $productImageUrl = $this->handleProductImage($imageFolder, $imageFile);

                if ($productImageUrl) {
                    $product->images()->create([
                        'image_url' => $productImageUrl,
                    ]);
                }
            }
        }

        $this->command->info("Seeded $productCount car products successfully.");
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
