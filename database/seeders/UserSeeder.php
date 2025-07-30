<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('fr_FR');
        $users = [];

        $specialisations = ['Laravel', 'React', 'Vue.js', 'Node.js', 'Docker', 'DevOps', 'UI/UX', 'Mobile', 'Full Stack', 'Backend', 'Frontend', 'Data Science', 'AI/ML', 'Cybersecurity', 'Cloud Computing'];
        $posts = ['Développeur', 'Designer', 'Chef de projet', 'DevOps', 'Data Scientist', 'Architecte logiciel', 'Testeur', 'Scrum Master', 'Product Owner', 'Tech Lead'];

        // Create admin user
        $users[] = [
            'Nom' => 'Admin',
            'Prenom' => 'System',
            'Email' => 'admin@example.com',
            'Password' => Hash::make('password123'),
            'Photo' => 'https://i.pravatar.cc/300?img=0',
            'Post' => 'Administrateur',
            'Tel' => '0612345678',
            'Sex' => 'Homme',
            'Adresse' => '123 Rue Admin, 20000 Casablanca, Maroc',
            'Specialisation' => 'Administration',
            'Salaire' => 15000,
            'Heur_sup_prime' => 0,
            'Delai_rappel' => 1,
            'ID_Role' => 1,
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Generate 14 more users
        for ($i = 0; $i < 14; $i++) {
            $gender = $faker->randomElement(['Homme', 'Femme']);
            $nom = $gender === 'Homme' ? $faker->lastName : $faker->lastName . 'e';
            $prenom = $gender === 'Homme' ? $faker->firstNameMale : $faker->firstNameFemale;
            $email = strtolower($prenom . '.' . $nom . '@example.com');
            $email = str_replace(
                [' ', 'é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ÿ', 'ç'], 
                ['', 'e', 'e', 'e', 'e', 'a', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'y', 'c'], 
                $email
            );

            $users[] = [
                'Nom' => $nom,
                'Prenom' => $prenom,
                'Email' => $email,
                'Password' => Hash::make('password123'),
                'Photo' => 'https://i.pravatar.cc/300?img=' . ($i + 1),
                'Post' => $faker->randomElement($posts),
                'Tel' => '06' . $faker->numberBetween(10000000, 99999999),
                'Sex' => $gender,
                'Adresse' => $faker->streetAddress . ', ' . $faker->postcode . ' ' . $faker->city . ', Maroc',
                'Specialisation' => $faker->randomElement($specialisations),
                'Salaire' => $faker->numberBetween(4000, 15000),
                'Heur_sup_prime' => $faker->numberBetween(0, 40),
                'Delai_rappel' => $faker->numberBetween(1, 10),
                'ID_Role' => 2,
                'email_verified_at' => now(),
                'remember_token' => \Illuminate\Support\Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('users')->insert($users);
    }
}