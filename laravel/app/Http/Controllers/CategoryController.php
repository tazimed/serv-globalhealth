<?php
namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('prestations')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Categories' => 'required|string|max:255',
            ];
            // Custom error messages
            $customMessages = [
                'Categories.required' => 'La catégorie est requise.',
                'Categories.string' => 'La catégorie doit être une chaîne de caractères.',
                'Categories.max' => 'La catégorie ne peut pas dépasser :max caractères.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();
            // Create a new category
            $category = Category::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Catégorie créée avec succès',
                'data' => $category
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
        $category = Category::with('prestations')->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);

            // Define validation rules
            $rules = [
                'Categories' => 'sometimes|required|string|max:255',
            ];
            // Custom error messages
            $customMessages = [
                'Categories.required' => 'La catégorie est requise.',
                'Categories.string' => 'La catégorie doit être une chaîne de caractères.',
                'Categories.max' => 'La catégorie ne peut pas dépasser :max caractères.',
            ];
            // Validate the request
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            // Process the validated data
            $validatedData = $validator->validated();

            // Update the category
            $category->fill($validatedData);
            $category->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Catégorie mise à jour avec succès',
                'data' => $category
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
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}