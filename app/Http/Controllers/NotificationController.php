<?php
namespace App\Http\Controllers;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('contact')->get();
        return response()->json($notifications);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Notification' => 'required|string',
                'Etat' => 'required|string',
                'ID_Contact' => 'required|exists:contacts,ID_Contact',
            ];
            // Custom error messages
            $customMessages = [
                'Notification.required' => 'La notification est requise.',
                'Notification.string' => 'La notification doit être une chaîne de caractères.',
                'Etat.required' => 'L\'état est requis.',
                'Etat.string' => 'L\'état doit être une chaîne de caractères.',
                'ID_Contact.required' => 'L\'ID du contact est requis.',
                'ID_Contact.exists' => 'L\'ID du contact doit exister dans la table des contacts.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new notification
            $notification = Notification::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification créée avec succès',
                'data' => $notification
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
        $notification = Notification::with('contact')->findOrFail($id);
        return response()->json($notification);
    }

    public function update(Request $request, $id)
    {
        try {
            $notification = Notification::findOrFail($id);

            // Define validation rules
            $rules = [
                'Notification' => 'sometimes|required|string',
                'Etat' => 'sometimes|required|string',
                'ID_Contact' => 'sometimes|required|exists:contacts,ID_Contact',
            ];
            // Custom error messages
            $customMessages = [
                'Notification.required' => 'La notification est requise.',
                'Notification.string' => 'La notification doit être une chaîne de caractères.',
                'Etat.required' => 'L\'état est requis.',
                'Etat.string' => 'L\'état doit être une chaîne de caractères.',
                'ID_Contact.required' => 'L\'ID du contact est requis.',
                'ID_Contact.exists' => 'L\'ID du contact doit exister dans la table des contacts.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the notification
            $notification->fill($validatedData);
            $notification->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification mise à jour avec succès',
                'data' => $notification
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
        $notification = Notification::findOrFail($id);
        $notification->delete();
        return response()->json(null, 204);
    }
}