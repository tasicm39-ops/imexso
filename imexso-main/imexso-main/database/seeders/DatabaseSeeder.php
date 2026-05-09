<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SitePageSeeder::class);

        if (app()->environment('local')) {
            User::query()->firstOrCreate(
                ['email' => 'admin@imexso.com'],
                [
                    'name' => 'Local Admin',
                    'first_name' => 'Local',
                    'last_name' => 'Admin',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'is_validated' => true,
                    'is_seller' => false,
                    'is_admin' => true,
                ]
            );
        }
    }
}
