<?php
namespace App\Http\Controllers;
use App\Models\ThemeGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class ThemeGeneralController extends Controller
{
    public function index()
    {
        $themes = ThemeGeneral::get();
        return response()->json($themes);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Couleurs' => 'required|string',
                'Horaire_Travail' => 'required|integer',
            ];
            // Custom error messages
            $customMessages = [
                'Couleurs.required' => 'Les couleurs sont requises.',
                'Couleurs.string' => 'Les couleurs doivent être une chaîne de caractères.',
                'Horaire_Travail.required' => 'L\'horaire de travail est requis.',
                'Horaire_Travail.integer' => 'L\'horaire de travail doit être un nombre entier.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new theme general
            $theme = ThemeGeneral::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Thème général créé avec succès',
                'data' => $theme
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
        $theme = ThemeGeneral::findOrFail($id);
        return response()->json($theme);
    }

    public function update(Request $request, $id)
    {
        try {
            $theme = ThemeGeneral::findOrFail($id);

            // Define validation rules
            $rules = [
                'Couleurs' => 'sometimes|required|string',
                'Horaire_Travail' => 'sometimes|required|integer',
            ];
            // Custom error messages
            $customMessages = [
                'Couleurs.required' => 'Les couleurs sont requises.',
                'Couleurs.string' => 'Les couleurs doivent être une chaîne de caractères.',
                'Horaire_Travail.required' => 'L\'horaire de travail est requis.',
                'Horaire_Travail.integer' => 'L\'horaire de travail doit être un nombre entier.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the theme general
            $theme->fill($validatedData);
            $theme->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Thème général mis à jour avec succès',
                'data' => $theme
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
        $theme = ThemeGeneral::findOrFail($id);
        $theme->delete();
        return response()->json(null, 204);
    }
}