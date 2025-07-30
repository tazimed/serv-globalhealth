<?php
namespace App\Http\Controllers;
use App\Models\Rappel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class RappelController extends Controller
{
    public function index()
    {
        $rappels = Rappel::with('user')->get();
        return response()->json($rappels);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Rappel' => 'required|string',
                'Etat' => 'required|string',
                'ID_User' => 'required|exists:users,ID_User',
            ];
            // Custom error messages
            $customMessages = [
                'Rappel.required' => 'Le rappel est requis.',
                'Rappel.string' => 'Le rappel doit être une chaîne de caractères.',
                'Etat.required' => 'L\'état est requis.',
                'Etat.string' => 'L\'état doit être une chaîne de caractères.',
                'ID_User.required' => 'L\'ID de l\'utilisateur est requis.',
                'ID_User.exists' => 'L\'ID de l\'utilisateur doit exister dans la table des utilisateurs.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new rappel
            $rappel = Rappel::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Rappel créé avec succès',
                'data' => $rappel
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

    public function show($id)
    {
        $rappel = Rappel::with('user')->findOrFail($id);
        return response()->json($rappel);
    }

    public function update(Request $request, $id)
    {
        try {
            $rappel = Rappel::findOrFail($id);

            // Define validation rules
            $rules = [
                'Rappel' => 'sometimes|required|string',
                'Etat' => 'sometimes|required|string',
                'ID_User' => 'sometimes|required|exists:users,ID_User',
            ];
            // Custom error messages
            $customMessages = [
                'Rappel.required' => 'Le rappel est requis.',
                'Rappel.string' => 'Le rappel doit être une chaîne de caractères.',
                'Etat.required' => 'L\'état est requis.',
                'Etat.string' => 'L\'état doit être une chaîne de caractères.',
                'ID_User.required' => 'L\'ID de l\'utilisateur est requis.',
                'ID_User.exists' => 'L\'ID de l\'utilisateur doit exister dans la table des utilisateurs.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the rappel
            $rappel->fill($validatedData);
            $rappel->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Rappel mis à jour avec succès',
                'data' => $rappel
            ], 200);
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

    public function destroy($id)
    {
        $rappel = Rappel::findOrFail($id);
        $rappel->delete();
        return response()->json(null, 204);
    }
}