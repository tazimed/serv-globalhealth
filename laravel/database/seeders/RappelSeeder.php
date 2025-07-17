<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rappel;
use App\Models\User;

class RappelSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $rappels = [
            ['Rappel' => 'Meeting Reminder', 'Etat' => 'Pending', 'ID_User' => $users->first()->ID_User],
            // Add more rappels as needed
        ];

        foreach ($rappels as $rappelData) {
            Rappel::create($rappelData);
        }
    }
}