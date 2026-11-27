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
        $adminEmail = trim((string) env('SEED_ADMIN_EMAIL', ''));
        $adminPassword = (string) env('SEED_ADMIN_PASSWORD', '');
        $adminName = trim((string) env('SEED_ADMIN_NAME', 'Admin Batik Daffa')) ?: 'Admin Batik Daffa';

        if ($adminEmail !== '' && strlen($adminPassword) >= 12) {
            $admin = User::firstOrNew(['email' => strtolower($adminEmail)]);
            if (! $admin->exists) {
                $admin->forceFill([
                    'name' => $adminName,
                    'email' => strtolower($adminEmail),
                    'password' => Hash::make($adminPassword),
                    'is_admin' => true,
                ])->save();
            }
        }

        $this->call([
            ProductSeeder::class,
            StoreVisualSeeder::class,
        ]);
    }
}
