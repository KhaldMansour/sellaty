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

        $admin = [
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'email' => config('filament.admin_email'),
                'username' => 'admin',
                'profile_photo' => 'https://picsum.photos/200/300?',
                'phone_number' => $faker->unique()->phoneNumber,
                'password' => Hash::make(config('filament.admin_password')),
                'roles' => json_encode([User::ROLE_SUPER_ADMIN]),
                'is_verified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

        $dummyUser = [
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'email' => $faker->unique()->email,
            'username' => $faker->unique()->userName,
            'profile_photo' => 'https://picsum.photos/200/300?',
            'phone_number' => config('app.dummy_login_phone_number'),
            'password' => Hash::make(config('app.dummy_login_password')),
            'roles' => json_encode([User::ROLE_USER]),
            'is_verified' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        User::insert([$admin , $dummyUser]);
    }
}
