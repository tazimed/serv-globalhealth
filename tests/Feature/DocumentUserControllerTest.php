<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\DocumentUser;
use Tymon\JWTAuth\Facades\JWTAuth;

class DocumentUserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_document_endpoints()
    {
        $response = $this->getJson('/api/document_users');
        $response->assertStatus(401);

        $response = $this->postJson('/api/document_users');
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_get_list_of_documents()
    {
        DocumentUser::factory()->count(3)->create([
            'ID_User' => $this->user->ID_User
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/document_users');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function user_can_create_document_with_valid_data()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/document_users', [
            'Nom_Doc' => 'Test Document',
            'Doc' => $file,
            'ID_User' => $this->user->ID_User
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'Id_Document',
                    'Nom_Doc',
                    'Doc',
                    'ID_User'
                ]
            ]);

        $this->assertDatabaseHas('document_users', [
            'Nom_Doc' => 'Test Document',
            'ID_User' => $this->user->ID_User
        ]);

        $fileName = now()->format('Y-m-d_His') . '_' . $file->getClientOriginalName();
        Storage::disk('local')->assertExists('document_users/' . $fileName);
    }

    /** @test */
    public function user_cannot_create_document_without_required_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/document_users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Nom_Doc', 'Doc', 'ID_User']);
    }

    /** @test */
    public function user_can_view_specific_document()
    {
        $document = DocumentUser::factory()->create([
            'ID_User' => $this->user->ID_User
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/document_users/{$document->Id_Document}");

        $response->assertStatus(200)
            ->assertJson([
                'Id_Document' => $document->Id_Document,
                'Nom_Doc' => $document->Nom_Doc,
                'ID_User' => $this->user->ID_User
            ]);
    }

    /** @test */
    public function user_can_update_document()
    {
        Storage::fake('local');

        $document = DocumentUser::factory()->create([
            'ID_User' => $this->user->ID_User
        ]);

        $newFile = UploadedFile::fake()->create('updated-document.pdf', 1000);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/document_users/{$document->Id_Document}", [
            'Nom_Doc' => 'Updated Document',
            'Doc' => $newFile,
            'ID_User' => $this->user->ID_User,
            '_method' => 'POST'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Document utilisateur mis à jour avec succès'
            ]);

        $this->assertDatabaseHas('document_users', [
            'Id_Document' => $document->Id_Document,
            'Nom_Doc' => 'Updated Document'
        ]);
    }

    /** @test */
    public function user_can_delete_document()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $document = DocumentUser::factory()->create([
            'ID_User' => $this->user->ID_User,
            'Doc' => $file->store('document_users')
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/document_users/{$document->Id_Document}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('document_users', [
            'Id_Document' => $document->Id_Document
        ]);

        Storage::disk('local')->assertMissing($document->Doc);
    }

    /** @test */
    public function user_cannot_access_nonexistent_document()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/document_users/99999");

        $response->assertStatus(404);
    }

    /** @test */
    public function user_cannot_create_document_with_invalid_user_id()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/document_users', [
            'Nom_Doc' => 'Test Document',
            'Doc' => $file,
            'ID_User' => 99999 // Non-existent user ID
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ID_User']);
    }

    /** @test */
    public function user_cannot_upload_invalid_file_types()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create(
            'malicious.php',
            1000,
            'application/x-httpd-php'
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/document_users', [
            'Nom_Doc' => 'Test Document',
            'Doc' => $file,
            'ID_User' => $this->user->ID_User
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_can_download_document()
    {
        // Create the storage directory if it doesn't exist
        $storageDir = storage_path('app/document_users');
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Create a fake document with a specific name
        $originalFileName = 'document.pdf';
        $file = UploadedFile::fake()->create($originalFileName, 100);

        // Generate the same filename format as in the controller
        $fileName = now()->format('Y-m-d_His') . '_' . $originalFileName;
        $path = 'document_users/' . $fileName;

        // Actually store the file in the real storage location
        $file->move(storage_path('app/document_users'), $fileName);

        // Create document with this exact path
        $document = DocumentUser::factory()->create([
            'ID_User' => $this->user->ID_User,
            'Doc' => $path
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->get("/api/document_users/{$document->Id_Document}/document");

        $response->assertStatus(200)
            ->assertHeader('Content-Type');

        // Cleanup
        if (file_exists(storage_path('app/' . $path))) {
            unlink(storage_path('app/' . $path));
        }
    }

    /** @test */
    public function large_file_upload_is_handled_properly()
    {
        Storage::fake('local');

        $largeFile = UploadedFile::fake()->create('large-document.pdf', 10240); // 10MB

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/document_users', [
            'Nom_Doc' => 'Large Document',
            'Doc' => $largeFile,
            'ID_User' => $this->user->ID_User
        ]);

        $response->assertStatus(201);
    }
}
