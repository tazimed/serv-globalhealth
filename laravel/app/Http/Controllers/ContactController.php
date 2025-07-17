<?php
namespace App\Http\Controllers;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::with('notifications', 'documentContacts')->get();
        return response()->json($contacts);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Nom' => 'required|string|max:255',
                'Prenom' => 'required|string|max:255',
                'Birthday' => 'nullable|date',
                'N_assurance' => 'nullable|string',
                'Cnss' => 'nullable|string',
                'Telephone' => 'nullable|string',
                'Email' => 'nullable|email',
                'Adresse' => 'nullable|string',
                'preferences' => 'nullable|string',
            ];
            // Custom error messages
            $customMessages = [
                'Nom.required' => 'Le nom est requis.',
                'Nom.string' => 'Le nom doit être une chaîne de caractères.',
                'Nom.max' => 'Le nom ne peut pas dépasser :max caractères.',
                'Prenom.required' => 'Le prénom est requis.',
                'Prenom.string' => 'Le prénom doit être une chaîne de caractères.',
                'Prenom.max' => 'Le prénom ne peut pas dépasser :max caractères.',
                'Birthday.date' => 'La date d\'anniversaire doit être une date valide.',
                'N_assurance.string' => 'Le numéro d\'assurance doit être une chaîne de caractères.',
                'Cnss.string' => 'Le numéro CNSS doit être une chaîne de caractères.',
                'Telephone.string' => 'Le téléphone doit être une chaîne de caractères.',
                'Email.email' => 'Veuillez entrer une adresse email valide.',
                'Adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
                'preferences.string' => 'Les préférences doivent être une chaîne de caractères.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new contact
            $contact = Contact::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Contact créé avec succès',
                'data' => $contact
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
        $contact = Contact::with('notifications', 'documentContacts')->findOrFail($id);
        return response()->json($contact);
    }

    public function update(Request $request, $id)
    {
        try {
            $contact = Contact::findOrFail($id);

            // Define validation rules
            $rules = [
                'Nom' => 'sometimes|required|string|max:255',
                'Prenom' => 'sometimes|required|string|max:255',
                'Birthday' => 'nullable|date',
                'N_assurance' => 'nullable|string',
                'Cnss' => 'nullable|string',
                'Telephone' => 'nullable|string',
                'Email' => 'nullable|email',
                'Adresse' => 'nullable|string',
                'preferences' => 'nullable|string',
            ];
            // Custom error messages
            $customMessages = [
                'Nom.required' => 'Le nom est requis.',
                'Nom.string' => 'Le nom doit être une chaîne de caractères.',
                'Nom.max' => 'Le nom ne peut pas dépasser :max caractères.',
                'Prenom.required' => 'Le prénom est requis.',
                'Prenom.string' => 'Le prénom doit être une chaîne de caractères.',
                'Prenom.max' => 'Le prénom ne peut pas dépasser :max caractères.',
                'Birthday.date' => 'La date d\'anniversaire doit être une date valide.',
                'N_assurance.string' => 'Le numéro d\'assurance doit être une chaîne de caractères.',
                'Cnss.string' => 'Le numéro CNSS doit être une chaîne de caractères.',
                'Telephone.string' => 'Le téléphone doit être une chaîne de caractères.',
                'Email.email' => 'Veuillez entrer une adresse email valide.',
                'Adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
                'preferences.string' => 'Les préférences doivent être une chaîne de caractères.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the contact
            $contact->fill($validatedData);
            $contact->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Contact mis à jour avec succès',
                'data' => $contact
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
        $contact = Contact::findOrFail($id);
        $contact->delete();
        return response()->json(null, 204);
    }
}