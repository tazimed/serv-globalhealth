<?php
namespace App\Http\Controllers;
use App\Models\RendezVous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class RendezVousController extends Controller
{
    public function index()
    {
        $rendezvous = RendezVous::with('user', 'contact', 'prestation')->get();
        return response()->json($rendezvous);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Duration' => 'required|int',
                'Date' => 'required|date',
                'Status' => 'required|string',
                'subject' => 'required|string',
                'ID_User' => 'required|exists:users,ID_User',
                'ID_Contact' => 'required|exists:contacts,ID_Contact',
                'ID_Prestation' => 'sometimes|exists:prestations,ID_Prestation',
            ];
            // Custom error messages
            $customMessages = [
                'Frequence.required' => 'La fréquence est requise.',
                'Frequence.int' => 'La fréquence doit être un chiffre.',
                'Date.required' => 'La date est requise.',
                'Date.date' => 'La date doit être une date valide.',
                'Status.required' => 'Le statut est requis.',
                'Status.string' => 'Le statut doit être une chaîne de caractères.',
                'subject.required' => 'Le statut est requis.',
                'subject.string' => 'Le statut doit être une chaîne de caractères.',
                'ID_User.required' => 'L\'ID de l\'utilisateur est requis.',
                'ID_User.exists' => 'L\'ID de l\'utilisateur doit exister dans la table des utilisateurs.',
                'ID_Contact.required' => 'L\'ID du contact est requis.',
                'ID_Contact.exists' => 'L\'ID du contact doit exister dans la table des contacts.',
                'ID_Prestation.exists' => 'L\'ID de la prestation doit exister dans la table des prestations.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new rendezvous
            $rendezvous = RendezVous::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Rendez-vous créé avec succès',
                'data' => $rendezvous
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
        $rendezvous = RendezVous::with('user', 'contact', 'prestation')->findOrFail($id);
        return response()->json($rendezvous);
    }

    public function update(Request $request, $id)
    {
        try {
            $rendezvous = RendezVous::findOrFail($id);

            // Define validation rules
            $rules = [
                'Duration' => 'sometimes|required|int',
                'Date' => 'sometimes|required|date',
                'subject' => 'required|string',
                'Status' => 'sometimes|required|string',
                'ID_User' => 'sometimes|required|exists:users,ID_User',
                'ID_Contact' => 'sometimes|required|exists:contacts,ID_Contact',
                'ID_Prestation' => 'sometimes|required|exists:prestations,ID_Prestation',
            ];
            // Custom error messages
            $customMessages = [
                'Frequence.required' => 'La fréquence est requise.',
                'Frequence.int' => 'La fréquence doit être un chiffre.',
                'Date.required' => 'La date est requise.',
                'Date.date' => 'La date doit être une date valide.',
                'Status.required' => 'Le statut est requis.',
                'Status.string' => 'Le statut doit être une chaîne de caractères.',                
                'subject.required' => 'Le statut est requis.',
                'subject.string' => 'Le statut doit être une chaîne de caractères.',
                'ID_User.required' => 'L\'ID de l\'utilisateur est requis.',
                'ID_User.exists' => 'L\'ID de l\'utilisateur doit exister dans la table des utilisateurs.',
                'ID_Contact.required' => 'L\'ID du contact est requis.',
                'ID_Contact.exists' => 'L\'ID du contact doit exister dans la table des contacts.',
                'ID_Prestation.required' => 'L\'ID de la prestation est requis.',
                'ID_Prestation.exists' => 'L\'ID de la prestation doit exister dans la table des prestations.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the rendezvous
            $rendezvous->fill($validatedData);
            $rendezvous->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Rendez-vous mis à jour avec succès',
                'data' => $rendezvous
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
        $rendezvous = RendezVous::findOrFail($id);
        $rendezvous->delete();
        return response()->json(null, 204);
    }
}