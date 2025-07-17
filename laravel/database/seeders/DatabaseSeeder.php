<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class, // Ensure this is called first
            UserSeeder::class,
            ThemeGeneralSeeder::class,
            ContactSeeder::class,
            PrestationSeeder::class,
            RendezVousSeeder::class,
            RappelSeeder::class,
            PointageSeeder::class,
            PointageUserSeeder::class,
            PaiementSeeder::class,
            NotificationSeeder::class,
            JourFerieSeeder::class,
            DroitSeeder::class,
            CongeSeeder::class,
              // Then call UserSeeder
            // Add other seeders here
        ]);
    }
}
