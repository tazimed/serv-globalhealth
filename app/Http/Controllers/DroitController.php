<?php
namespace App\Http\Controllers;
use App\Models\Droit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Validation\ValidationException;

class DroitController extends Controller
{
    public function index()
    {
        $droits = Droit::with('role')->get();
        return response()->json($droits);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Droit' => 'required|string|max:255',
                'Lecture' => 'required|boolean',
                'Ajouter' => 'required|boolean',
                'Modifier' => 'required|boolean',
                'Supprimer' => 'required|boolean',
                'ID_Role' => 'required|exists:roles,ID_Role',
            ];
            // Custom error messages
            $customMessages = [
                'Droit.required' => 'Le droit est requis.',
                'Droit.string' => 'Le droit doit être une chaîne de caractères.',
                'Droit.max' => 'Le droit ne peut pas dépasser :max caractères.',
                'ID_Role.required' => 'L\'ID du rôle est requis.',
                'ID_Role.exists' => 'L\'ID du rôle doit exister dans la table des rôles.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new droit
            $droit = Droit::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Droit créé avec succès',
                'data' => $droit
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
        $droit = Droit::with('role')->findOrFail($id);
        return response()->json($droit);
    }

    public function update(Request $request, $id)
    {
        try {
            $droit = Droit::findOrFail($id);

            // Define validation rules
            $rules = [
                'Droit' => 'sometimes|required|string|max:255',
                'Lecture' => 'sometimes|required|boolean',
                'Ajouter' => 'sometimes|required|boolean',
                'Modifier' => 'sometimes|required|boolean',
                'Supprimer' => 'sometimes|required|boolean',
                'ID_Role' => 'sometimes|required|exists:roles,ID_Role',
            ];
            // Custom error messages
            $customMessages = [
                'Droit.required' => 'Le droit est requis.',
                'Droit.string' => 'Le droit doit être une chaîne de caractères.',
                'Droit.max' => 'Le droit ne peut pas dépasser :max caractères.',
                'ID_Role.required' => 'L\'ID du rôle est requis.',
                'ID_Role.exists' => 'L\'ID du rôle doit exister dans la table des rôles.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the droit
            $droit->fill($validatedData);
            $droit->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Droit mis à jour avec succès',
                'data' => $droit
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
        $droit = Droit::findOrFail($id);
        $droit->delete();
        return response()->json(null, 204);
    }
}