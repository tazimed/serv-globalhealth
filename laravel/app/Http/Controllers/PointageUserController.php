<?php
namespace App\Http\Controllers;
use App\Models\PointageUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class PointageUserController extends Controller
{
    public function store(Request $request, $pointageId, $userId)
    {
        try {
            // Define validation rules
            $rules = [
                'Heur_Travail' => 'nullable|integer',
                'Abssance' => 'nullable|boolean',
            ];
            // Custom error messages
            $customMessages = [
                'Heur_Travail.integer' => 'Les heures de travail doivent être un nombre entier.',
                'Abssance.boolean' => 'L\'absence doit être un booléen.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Create or update pointage user
            $pointageUser = PointageUser::updateOrCreate(
                [
                    'ID_Pointage' => $pointageId,
                    'ID_User' => $userId,
                ],
                array_merge($validatedData, [
                    'ID_Pointage' => $pointageId,
                    'ID_User' => $userId,
                ])
            );

            return response()->json([
                "pointageUser" => $pointageUser,
                "error" => false,
                "message" => "Pointage utilisateur ajouté avec succès."
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'La validation a échoué',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur s\'est produite lors du traitement de votre demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($pointageId, $userId)
    {
        try {
            $deleted = PointageUser::where('ID_Pointage', $pointageId)
                ->where('ID_User', $userId)
                ->delete();

            if ($deleted) {
                return response()->json(['message' => 'Supprimé avec succès'], 200);
            } else {
                return response()->json(['message' => 'Aucun enregistrement trouvé pour suppression'], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur s\'est produite lors du traitement de votre demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
