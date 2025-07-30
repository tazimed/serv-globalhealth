<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PointageUserSeeder extends Seeder
{
    public function run()
    {
        // Get all users and pointages
        $users = DB::table('users')->pluck('ID_User');
        $pointages = DB::table('pointages')->get();
        
        $pointageUsers = [];
        
        foreach ($users as $userId) {
            foreach ($pointages as $pointage) {
                // 20% chance of being absent
                $isAbsent = rand(1, 5) === 1;
                
                // If not absent, generate random work hours (0-8)
                $workHours = $isAbsent ? 0 : rand(1, 8);
                
                // Convert pointage date to datetime for timestamps
                $pointageDate = Carbon::parse($pointage->Date);
                
                $pointageUsers[] = [
                    'ID_User' => $userId,
                    'ID_Pointage' => $pointage->ID_Pointage,
                    'Heur_Travail' => $workHours,
                    'Absence' => $isAbsent ? 1 : 0,  // Fixed typo here from 'Absance' to 'Absence'
                    'created_at' => $pointageDate->toDateTimeString(),
                    'updated_at' => $pointageDate->toDateTimeString(),
                ];
                
                // Insert in batches of 100 for better performance
                if (count($pointageUsers) >= 100) {
                    DB::table('pointage_user')->insert($pointageUsers);
                    $pointageUsers = [];
                }
            }
        }
        
        // Insert any remaining records
        if (!empty($pointageUsers)) {
            DB::table('pointage_user')->insert($pointageUsers);
        }
    }
}