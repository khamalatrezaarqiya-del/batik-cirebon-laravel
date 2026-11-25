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
            ['email' => 'Jajareja@gmail.com'],
            [
                'name' => 'Admin Batik Daffa',
                'password' => 'Jajareja@16',
                'is_admin' => true,
            ],
        );

        $this->call([
            ProductSeeder::class,
        ]);
    }
}
