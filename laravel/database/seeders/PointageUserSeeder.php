<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PointageUser;
use App\Models\Pointage;
use App\Models\User;

class PointageUserSeeder extends Seeder
{
    public function run()
    {
        $pointages = Pointage::all();
        $users = User::all();

        foreach ($pointages as $pointage) {
            foreach ($users as $user) {
                PointageUser::factory()->create([
                    'ID_Pointage' => $pointage->ID_Pointage,
                    'ID_User' => $user->ID_User,
                ]);
            }
        }
    }
}