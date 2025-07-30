<?php
namespace App\Http\Controllers;
use App\Models\DocumentContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Validation\ValidationException;

class DocumentContactController extends Controller
{
    public function index()
    {
        $documents = DocumentContact::with('contact')->get();
        return response()->json($documents);
    }

    public function store(Request $request)
    {
        try {
            // Define validation rules
            $rules = [
                'Nom_Doc' => 'required|string',
                'Doc' => 'required|file',
                'ID_Contact' => 'required|exists:contacts,ID_Contact',
            ];
            // Custom error messages
            $customMessages = [
                'Nom_Doc.required' => 'Le nom du document est requis.',
                'Doc.required' => 'Le fichier du document est requis.',
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

            // Handle file upload for Doc
            $file = $request->file('Doc');
            $fileName = now()->format('Y-m-d_His') . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('document_contacts', $fileName);
            $validatedData['Doc'] = $path;

            // Create a new document
            $document = DocumentContact::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Document contact créé avec succès',
                'data' => $document
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
        $document = DocumentContact::with('contact')->findOrFail($id);
        return response()->json($document);
    }

    public function update(Request $request, $id)
    {
        try {
            $document = DocumentContact::findOrFail($id);

            // Define validation rules
            $rules = [
                'Nom_Doc' => 'sometimes|required|string',
                'Doc' => 'nullable|file',
                'ID_Contact' => 'sometimes|required|exists:contacts,ID_Contact',
            ];
            // Custom error messages
            $customMessages = [
                'Nom_Doc.required' => 'Le nom du document est requis.',
                'Doc.required' => 'Le fichier du document est requis.',
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

            // Handle file upload for Doc
            if ($request->hasFile('Doc')) {
                // Delete the old document if it exists
                if ($document->Doc) {
                    Storage::delete($document->Doc);
                }
                $file = $request->file('Doc');
                $fileName = now()->format('Y-m-d_His') . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('document_contacts', $fileName);
                $validatedData['Doc'] = $path;
            }

            // Update the document
            $document->fill($validatedData);
            $document->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Document contact mis à jour avec succès',
                'data' => $document
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
        $document = DocumentContact::findOrFail($id);

        // Delete the associated document if it exists
        if ($document->Doc) {
            Storage::delete($document->Doc);
        }

        $document->delete();
        return response()->json(null, 204);
    }

    public function showDocument($id)
    {
        $document = DocumentContact::findOrFail($id);
        return response()->file(storage_path('app/' . $document->Doc));
    }
}