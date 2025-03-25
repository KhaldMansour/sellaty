<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        if (User::count() > 0) {
            return;
        };

        $faker = Faker::create();

        $users = collect(range(1, 10))->map(function () use ($faker) {
            return [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone_number' => $faker->unique()->phoneNumber,
                'password' => Hash::make('password'),
                'is_verified' => $faker->boolean,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        User::insert($users->toArray());
    }
}
