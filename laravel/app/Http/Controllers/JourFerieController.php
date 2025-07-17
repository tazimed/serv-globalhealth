<?php
namespace App\Http\Controllers;
use App\Models\JourFerie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class JourFerieController extends Controller
{
    public function index()
    {
        $joursFeries = JourFerie::get();
        return response()->json($joursFeries);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Date_debut' => 'required|date',
                'Date_fin' => 'required|date|after_or_equal:Date_debut',
            ];
            // Custom error messages
            $customMessages = [
                'Date_debut.required' => 'La date de début est requise.',
                'Date_debut.date' => 'La date de début doit être une date valide.',
                'Date_fin.required' => 'La date de fin est requise.',
                'Date_fin.date' => 'La date de fin doit être une date valide.',
                'Date_fin.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new jour ferie
            $jourFerie = JourFerie::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Jour férié créé avec succès',
                'data' => $jourFerie
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
        $jourFerie = JourFerie::findOrFail($id);
        return response()->json($jourFerie);
    }

    public function update(Request $request, $id)
    {
        try {
            $jourFerie = JourFerie::findOrFail($id);

            // Define validation rules
            $rules = [
                'Date_debut' => 'sometimes|required|date',
                'Date_fin' => 'sometimes|required|date|after_or_equal:Date_debut',
            ];
            // Custom error messages
            $customMessages = [
                'Date_debut.required' => 'La date de début est requise.',
                'Date_debut.date' => 'La date de début doit être une date valide.',
                'Date_fin.required' => 'La date de fin est requise.',
                'Date_fin.date' => 'La date de fin doit être une date valide.',
                'Date_fin.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the jour ferie
            $jourFerie->fill($validatedData);
            $jourFerie->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Jour férié mis à jour avec succès',
                'data' => $jourFerie
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
        $jourFerie = JourFerie::findOrFail($id);
        $jourFerie->delete();
        return response()->json(null, 204);
    }
}