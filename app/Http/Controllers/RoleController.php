<?php
namespace App\Http\Controllers;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('users', 'droits')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Role' => 'required|string|max:255',
            ];
            // Custom error messages
            $customMessages = [
                'Role.required' => 'Le rôle est requis.',
                'Role.string' => 'Le rôle doit être une chaîne de caractères.',
                'Role.max' => 'Le rôle ne peut pas dépasser :max caractères.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new role
            $role = Role::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Rôle créé avec succès',
                'data' => $role
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
        $role = Role::with('users', 'droits')->findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);

            // Define validation rules
            $rules = [
                'Role' => 'sometimes|required|string|max:255',
            ];
            // Custom error messages
            $customMessages = [
                'Role.required' => 'Le rôle est requis.',
                'Role.string' => 'Le rôle doit être une chaîne de caractères.',
                'Role.max' => 'Le rôle ne peut pas dépasser :max caractères.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the role
            $role->fill($validatedData);
            $role->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Rôle mis à jour avec succès',
                'data' => $role
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
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(null, 204);
    }
}