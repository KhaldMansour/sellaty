<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CustomField;
use App\Models\CustomFieldOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CarMakeAndModelSeeder extends Seeder
{
    public function run(): void
    {
        $carCategory = Category::firstOrCreate(
            ['name->en' => 'Cars'],
            ['name->ar' => 'سيارات'],
            ['image_url' => 'https://www.thedigitalbunch.com/_next/image?url=%2F_next%2Fstatic%2Fmedia%2F109-thumbnail.ddd6d67e.jpg&w=1920&q=75']
        );

        $response = Http::get('https://carapi.app/api/models/v2');

        if (!$response->ok()) {
            $this->command->error('Failed to fetch car data.');

            return;
        }

        $models = collect($response->json('data'));

        $makeField = CustomField::updateOrCreate(
            [
                'category_id' => $carCategory->id,
                'name' => 'Car Brand',
            ],
            [
                'type' => 'select',
                'required' => true,
            ]
        );

        $modelField = CustomField::updateOrCreate(
            [
                'category_id' => $carCategory->id,
                'name' => 'Car Model',
            ],
            [
                'type' => 'select',
                'required' => true,
            ]
        );

        $grouped = $models->groupBy('make');

        foreach ($grouped as $make => $modelsList) {
            $makeOption = CustomFieldOption::firstOrCreate([
                'custom_field_id' => $makeField->id,
                'value' => $make,
            ]);

            foreach ($modelsList->pluck('name')->unique() as $modelName) {
                CustomFieldOption::firstOrCreate([
                    'custom_field_id' => $modelField->id,
                    'value' => $modelName,
                    'parent_option_id' => $makeOption->id,
                ]);
            }
        }

        $this->command->info('Car Brand and Model fields and options seeded successfully.');
    }
}
