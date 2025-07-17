<?php
namespace App\Http\Controllers;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class PaiementController extends Controller
{
    public function index()
    {
        $paiements = Paiement::with('user')->get();
        return response()->json($paiements);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Date' => 'required|date',
                'Type' => 'required|string',
                'Absence_sup' => 'nullable|numeric',
                'ID_User' => 'required|exists:users,ID_User',
            ];
            // Custom error messages
            $customMessages = [
                'Date.required' => 'La date est requise.',
                'Date.date' => 'La date doit être une date valide.',
                'Type.required' => 'Le type est requis.',
                'Type.string' => 'Le type doit être une chaîne de caractères.',
                'Absence_sup.numeric' => 'L\'absence supplémentaire doit être un nombre.',
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
            // Create a new paiement
            $paiement = Paiement::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Paiement créé avec succès',
                'data' => $paiement
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
        $paiement = Paiement::with('user')->findOrFail($id);
        return response()->json($paiement);
    }

    public function update(Request $request, $id)
    {
        try {
            $paiement = Paiement::findOrFail($id);

            // Define validation rules
            $rules = [
                'Date' => 'sometimes|required|date',
                'Type' => 'sometimes|required|string',
                'Absence_sup' => 'nullable|numeric',
                'ID_User' => 'sometimes|required|exists:users,ID_User',
            ];
            // Custom error messages
            $customMessages = [
                'Date.required' => 'La date est requise.',
                'Date.date' => 'La date doit être une date valide.',
                'Type.required' => 'Le type est requis.',
                'Type.string' => 'Le type doit être une chaîne de caractères.',
                'Absence_sup.numeric' => 'L\'absence supplémentaire doit être un nombre.',
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

            // Update the paiement
            $paiement->fill($validatedData);
            $paiement->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Paiement mis à jour avec succès',
                'data' => $paiement
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
        $paiement = Paiement::findOrFail($id);
        $paiement->delete();
        return response()->json(null, 204);
    }
}