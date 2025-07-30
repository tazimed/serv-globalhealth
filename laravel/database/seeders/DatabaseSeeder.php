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
            // Seed roles first as they're required for users
            RoleSeeder::class,
            
            // Then seed users (depends on roles)
            UserSeeder::class,
            
            // Then seed pointages (independent)
            PointageSeeder::class,
            
            // Then associate users with pointages
            PointageUserSeeder::class,
            
            // Other seeders that might be needed
            ThemeGeneralSeeder::class,
            ContactSeeder::class,
            
            // Seed categories before prestations
            CategorySeeder::class,
            
            // Then seed prestations (depends on categories)
            PrestationSeeder::class,
            RendezVousSeeder::class,
            RappelSeeder::class,
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
