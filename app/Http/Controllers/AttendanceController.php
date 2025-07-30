<?php

namespace App\Http\Controllers;

use App\Models\Pointage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // Enregistrer ou mettre à jour une absence
    public function recordAbsence(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,ID_User',
            'date' => 'required|date',
            'is_absent' => 'required|boolean'
        ]);

        // Créer ou récupérer le pointage pour la date spécifiée
        $pointage = Pointage::firstOrCreate(
            ['Date' => $validated['date']],
            ['created_at' => now(), 'updated_at' => now()]
        );

        // Mettre à jour ou créer l'enregistrement pointage_user
        DB::table('pointage_user')->updateOrInsert(
            [
                'ID_Pointage' => $pointage->ID_Pointage,
                'ID_User' => $validated['user_id']
            ],
            [
                'Heur_Travail' => 8, // Toujours 8 heures
                'Absence' => $validated['is_absent'] ? 1 : 0,
                'updated_at' => now()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Statut de présence mis à jour avec succès'
        ]);
    }


    public function attendance(Request $request)
    {
        $attendances = Pointage::with([
            'users' => function ($query) {
                $query->select('users.ID_User', 'users.Nom', 'users.Prenom')
                    ->withPivot('Heur_Travail', 'Absence');
            }
        ])
            ->get() // بدل first()
            ->flatMap(function ($pointage) {
                return $pointage->users->map(function ($user) use ($pointage) {
                    return [
                        'ID_User' => $user->ID_User,
                        'Date' => $pointage->Date,
                        'status' => $user->pivot->Absence,
                    ];
                });
            });


        return response()->json($attendances);
    }

    // Récupérer l'historique d'un utilisateur
    public function getUserAttendanceHistory($userId)
    {
        try {
            // Log the incoming request
            \Log::info('Fetching attendance history for user ID: ' . $userId);

            // Validate user ID
            if (!is_numeric($userId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user ID format',
                    'user_id' => $userId
                ], 400);
            }

            // Check if user exists
            $userExists = DB::table('users')->where('ID_User', $userId)->exists();
            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'user_id' => $userId
                ], 404);
            }

            $history = DB::table('pointages')
                ->join('pointage_user', 'pointages.ID_Pointage', '=', 'pointage_user.ID_Pointage')
                ->where('pointage_user.ID_User', $userId)
                ->orderBy('pointages.Date', 'desc')
                ->select(
                    'pointages.Date',
                    'pointage_user.Heur_Travail as hours_worked',
                    'pointage_user.Abssance as is_absent',
                    'pointage_user.created_at as recorded_at'
                )
                ->get();

            \Log::info('Successfully retrieved ' . $history->count() . ' attendance records for user ID: ' . $userId);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getUserAttendanceHistory: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching attendance history',
                'error' => $e->getMessage(),
                'trace' => env('APP_DEBUG') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}
