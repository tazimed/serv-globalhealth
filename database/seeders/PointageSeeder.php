<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PointageSeeder extends Seeder
{
    public function run()
    {
        $pointages = [];
        $today = Carbon::today();

        // Generate pointages for the last 15 days
        for ($i = 0; $i < 15; $i++) {
            $pointageDate = $today->copy()->subDays(14 - $i);
            $pointages[] = [
                'Date' => $pointageDate->format('Y-m-d'),
                'created_at' => $pointageDate->toDateTimeString(),
                'updated_at' => $pointageDate->toDateTimeString(),
            ];
        }

        // Insert all pointages at once for better performance
        DB::table('pointages')->insert($pointages);
    }
}
