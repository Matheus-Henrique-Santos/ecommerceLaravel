<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Safe to run more than once.
     * Demo accounts use the factory password ("password").
     */
    public function run(): void
    {
        $this->seedUser(User::factory()->admin(), 'Admin', 'admin@example.com');
        $this->seedUser(User::factory(), 'Cliente', 'cliente@example.com');

        $this->call(CatalogSeeder::class);
    }

    private function seedUser(UserFactory $factory, string $name, string $email): void
    {
        if (User::query()->where('email', $email)->exists()) {
            return;
        }

        $factory->create(['name' => $name, 'email' => $email]);
    }
}
