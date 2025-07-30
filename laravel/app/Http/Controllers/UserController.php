<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User; // Change this to your User model namespace
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Nom' => 'required|string|max:255',
                'Prenom' => 'required|string|max:255',
                'Email' => 'required|email|unique:users,Email',
                'Password' => 'required|string|min:8',
                'Photo' => 'nullable|file',
                'Post' => 'nullable|string',
                'Tel' => 'nullable|string',
                'Adresse' => 'nullable|string',
                'Specialisation' => 'nullable|string',
                'Salaire' => 'nullable|numeric',
                'Heur_sup_prime' => 'nullable|numeric',
                'Delai_rappel' => 'nullable|integer',
                'Sex' => 'nullable|string',
                'ID_Role' => 'required|exists:roles,ID_Role',
            ];
            // Custom error messages
            $customMessages = [
                'Nom.required' => 'Le nom est requis.',
                'Prenom.required' => 'Le prénom est requis.',
                'Email.required' => 'L\'email est requis.',
                'Email.email' => 'Veuillez entrer une adresse email valide.',
                'Email.unique' => 'Cette adresse email est déjà enregistrée.',
                'Password.required' => 'Le mot de passe est requis.',
                'Password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'ID_Role.required' => 'L\'ID du rôle est requis.',
                'ID_Role.exists' => 'L\'ID du rôle doit exister dans la table des rôles.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                return response()->json($validator->errors()->first(), 400);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Handle file upload for Photo
            if ($request->hasFile('Photo')) {
                $file = $request->file('Photo');
                $fileName = now()->format('Y-m-d_His') . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('user_photos', $fileName);
                $validatedData['Photo'] = $path;
            }

            // Hash the password
            $validatedData['Password'] = bcrypt($request->Password);

            // Create a new user
            $user = User::create($validatedData);

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur créé avec succès',
                'data' => $user,
                'token' => $token
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur s\'est produite lors du traitement de votre demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Email' => 'required|string',
            'Password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = User::where('Email', $request->Email)->first();
        if ($user && password_verify($request->Password, $user->Password)) {
            try {
                $token = JWTAuth::fromUser($user);
                return response()->json(['message' => 'Login successful', 'token' => $token, 'user' => $user]);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Could not create token'], 500);
            }
        } else {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function index()
    {
        // return response()->json(['message' => 'This route is protected and requires authentication'], 403);
        $users = User::with('role', 'paiements', 'rappels', 'documentUsers', 'conges', 'pointages', 'rendezVous')
            ->where('ID_Role', '!=', 1)
            ->get();
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::with('role', 'paiements', 'rappels', 'documentUsers', 'conges', 'pointages', 'rendezVous')->findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Define validation rules
            $rules = [
                'Nom' => 'sometimes|required|string|max:255',
                'Prenom' => 'sometimes|required|string|max:255',
                'Email' => 'sometimes|required|email|unique:users,Email,' . $id . ',ID_User',
                'Password' => 'sometimes|required|string|min:8',
                'Photo' => 'nullable|file',
                'Post' => 'nullable|string',
                'Tel' => 'nullable|string',
                'Adresse' => 'nullable|string',
                'Specialisation' => 'nullable|string',
                'Salaire' => 'nullable|numeric',
                'Heur_sup_prime' => 'nullable|numeric',
                'Delai_rappel' => 'nullable|integer',
                'Sex' => 'nullable|string',
                'ID_Role' => 'sometimes|required|exists:roles,ID_Role',
            ];
            // Custom error messages
            $customMessages = [
                'Nom.required' => 'Le nom est requis.',
                'Prenom.required' => 'Le prénom est requis.',
                'Email.required' => 'L\'email est requis.',
                'Email.email' => 'Veuillez entrer une adresse email valide.',
                'Email.unique' => 'Cette adresse email est déjà enregistrée.',
                'Password.required' => 'Le mot de passe est requis.',
                'Password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'ID_Role.required' => 'L\'ID du rôle est requis.',
                'ID_Role.exists' => 'L\'ID du rôle doit exister dans la table des rôles.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Handle file upload for Photo
            if ($request->hasFile('Photo')) {
                // Delete the old photo if it exists
                if ($user->Photo) {
                    Storage::delete($user->Photo);
                }
                $file = $request->file('Photo');
                $fileName = now()->format('Y-m-d_His') . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('user_photos', $fileName);
                $validatedData['Photo'] = $path;
            }

        if ($request->has('Password')) {
            $validatedData['Password'] = bcrypt($request->Password);
        }
            // Update the user
            $user->fill($validatedData);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur mis à jour avec succès',
                'data' => $user
            ], 200);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $firstErrorMessage = reset($errors)[0]; // Get the first error message

            return response()->json([
                'status' => 'error',
                'message' => $firstErrorMessage, // Use the first error message
                'errors' => $errors // Return all validation errors
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
        $user = User::findOrFail($id);

        // Delete the associated photo if it exists
        if ($user->Photo) {
            Storage::delete($user->Photo);
        }

        $user->delete();
        return response()->json(null, 204);
    }

    public function showPhoto($id)
    {
        $user = User::findOrFail($id);
        return response()->file(storage_path('app/' . $user->Photo));
    }
}
