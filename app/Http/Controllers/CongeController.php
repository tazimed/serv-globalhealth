<?php
namespace App\Http\Controllers;
use App\Models\Conge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class CongeController extends Controller
{
    public function index()
    {
        $conges = Conge::with('user')->get();
        return response()->json($conges);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Date_debut' => 'required|date',
                'Date_fin' => 'required|date|after_or_equal:Date_debut',
                'Type' => 'required|string',
                'ID_User' => 'required|exists:users,ID_User',
            ];
            // Custom error messages
            $customMessages = [
                'Date_debut.required' => 'La date de début est requise.',
                'Date_debut.date' => 'La date de début doit être une date valide.',
                'Date_fin.required' => 'La date de fin est requise.',
                'Date_fin.date' => 'La date de fin doit être une date valide.',
                'Date_fin.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',
                'Type.required' => 'Le type est requis.',
                'Type.string' => 'Le type doit être une chaîne de caractères.',
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
            // Create a new conge
            $conge = Conge::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Congé créé avec succès',
                'data' => $conge
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
        $conge = Conge::with('user')->findOrFail($id);
        return response()->json($conge);
    }

    public function update(Request $request, $id)
    {
        try {
            $conge = Conge::findOrFail($id);

            // Define validation rules
            $rules = [
                'Date_debut' => 'sometimes|required|date',
                'Date_fin' => 'sometimes|required|date|after_or_equal:Date_debut',
                'Type' => 'sometimes|required|string',
                'ID_User' => 'sometimes|required|exists:users,ID_User',
            ];
            // Custom error messages
            $customMessages = [
                'Date_debut.required' => 'La date de début est requise.',
                'Date_debut.date' => 'La date de début doit être une date valide.',
                'Date_fin.required' => 'La date de fin est requise.',
                'Date_fin.date' => 'La date de fin doit être une date valide.',
                'Date_fin.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',
                'Type.required' => 'Le type est requis.',
                'Type.string' => 'Le type doit être une chaîne de caractères.',
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

            // Update the conge
            $conge->fill($validatedData);
            $conge->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Congé mis à jour avec succès',
                'data' => $conge
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
        $conge = Conge::findOrFail($id);
        $conge->delete();
        return response()->json(null, 204);
    }
}