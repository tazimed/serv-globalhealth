<?php
namespace App\Http\Controllers;
use App\Models\Prestation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class PrestationController extends Controller
{
    public function index()
    {
        $prestations = Prestation::with('category', 'rendezVous')->get();
        return response()->json($prestations);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Prestations' => 'required|string|max:255',
                'Durees' => 'required|integer',
                'Prix' => 'required|numeric',
                'ID_Categories' => 'required|exists:categories,ID_Categories',
            ];
            // Custom error messages
            $customMessages = [
                'Prestations.required' => 'La prestation est requise.',
                'Prestations.string' => 'La prestation doit être une chaîne de caractères.',
                'Prestations.max' => 'La prestation ne peut pas dépasser :max caractères.',
                'Durees.required' => 'La durée est requise.',
                'Durees.integer' => 'La durée doit être un nombre entier.',
                'Prix.required' => 'Le prix est requis.',
                'Prix.numeric' => 'Le prix doit être un nombre.',
                'ID_Categories.required' => 'L\'ID de la catégorie est requis.',
                'ID_Categories.exists' => 'L\'ID de la catégorie doit exister dans la table des catégories.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new prestation
            $prestation = Prestation::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Prestation créée avec succès',
                'data' => $prestation
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
        $prestation = Prestation::with('category', 'rendezVous')->findOrFail($id);
        return response()->json($prestation);
    }

    public function update(Request $request, $id)
    {
        try {
            $prestation = Prestation::findOrFail($id);

            // Define validation rules
            $rules = [
                'Prestations' => 'sometimes|required|string|max:255',
                'Durees' => 'sometimes|required|integer',
                'Prix' => 'sometimes|required|numeric',
                'ID_Categories' => 'sometimes|required|exists:categories,ID_Categories',
            ];
            // Custom error messages
            $customMessages = [
                'Prestations.required' => 'La prestation est requise.',
                'Prestations.string' => 'La prestation doit être une chaîne de caractères.',
                'Prestations.max' => 'La prestation ne peut pas dépasser :max caractères.',
                'Durees.required' => 'La durée est requise.',
                'Durees.integer' => 'La durée doit être un nombre entier.',
                'Prix.required' => 'Le prix est requis.',
                'Prix.numeric' => 'Le prix doit être un nombre.',
                'ID_Categories.required' => 'L\'ID de la catégorie est requis.',
                'ID_Categories.exists' => 'L\'ID de la catégorie doit exister dans la table des catégories.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the prestation
            $prestation->fill($validatedData);
            $prestation->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Prestation mise à jour avec succès',
                'data' => $prestation
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
        $prestation = Prestation::findOrFail($id);
        $prestation->delete();
        return response()->json(null, 204);
    }
}