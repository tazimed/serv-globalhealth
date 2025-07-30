<?php
namespace App\Http\Controllers;
use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class PointageController extends Controller
{
    public function index()
    {
        $pointages = Pointage::with('users')->get();
        return response()->json($pointages);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Date' => 'required|date',
            ];
            // Custom error messages
            $customMessages = [
                'Date.required' => 'La date est requise.',
                'Date.date' => 'La date doit être une date valide.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new pointage
            $pointage = Pointage::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Pointage créé avec succès',
                'data' => $pointage
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
        $pointage = Pointage::with('users')->findOrFail($id);
        return response()->json($pointage);
    }

    public function update(Request $request, $id)
    {
        try {
            $pointage = Pointage::findOrFail($id);

            // Define validation rules
            $rules = [
                'Date' => 'sometimes|required|date',
            ];
            // Custom error messages
            $customMessages = [
                'Date.required' => 'La date est requise.',
                'Date.date' => 'La date doit être une date valide.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the pointage
            $pointage->fill($validatedData);
            $pointage->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Pointage mis à jour avec succès',
                'data' => $pointage
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
        $pointage = Pointage::findOrFail($id);
        $pointage->delete();
        return response()->json(null, 204);
    }
}