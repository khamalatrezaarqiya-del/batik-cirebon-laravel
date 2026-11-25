<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@batikdaffa.com'],
            [
                'name' => 'Admin Batik Daffa',
                'password' => 'admin12345',
                'is_admin' => true,
            ],
        );

        $this->call([
            ProductSeeder::class,
        ]);
    }
}
