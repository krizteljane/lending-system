<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('DEPLOY_ADMIN_EMAIL');
        $password = env('DEPLOY_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => env('DEPLOY_ADMIN_NAME', 'Administrator'),
                'password' => $password,
            ],
        );
    }
}
