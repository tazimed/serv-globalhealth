<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // Règles de validation communes
    protected $validationRules = [
        'nom' => 'required|string|max:255',
        'description' => 'nullable|string',
        'prix' => 'required|numeric|min:0|max:999999.99'
    ];
    
    // Messages d'erreur en français
    protected $validationMessages = [
        'nom.required' => 'Le champ nom est obligatoire.',
        'nom.string' => 'Le nom doit être une chaîne de caractères.',
        'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
        'description.string' => 'La description doit être une chaîne de caractères.',
        'prix.required' => 'Le champ prix est obligatoire.',
        'prix.numeric' => 'Le prix doit être un nombre.',
        'prix.min' => 'Le prix ne peut pas être négatif.',
        'prix.max' => 'Le prix ne peut pas dépasser 999999.99.'
    ];

    /**
     * Liste tous les services
     */
    public function index()
    {
        $services = Service::all();
        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Crée un nouveau service
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), $this->validationRules, $this->validationMessages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $service = Service::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Service créé avec succès',
            'data' => $service
        ], 201);
    }

    /**
     * Affiche un service spécifique
     */
    public function show($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }

    /**
     * Met à jour un service existant
     */
    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service non trouvé'
            ], 404);
        }

        $validator = validator($request->all(), $this->validationRules, $this->validationMessages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $service->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Service mis à jour avec succès',
            'data' => $service
        ]);
    }

    /**
     * Supprime un service
     */
    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service non trouvé'
            ], 404);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service supprimé avec succès'
        ]);
    }
}