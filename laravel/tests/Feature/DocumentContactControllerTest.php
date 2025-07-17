<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Contact;
use App\Models\DocumentContact;
use Tymon\JWTAuth\Facades\JWTAuth;


class DocumentContactControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $contact;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and get JWT token
        $this->user = User::factory()->create();
        $response = $this->post('/api/login', [
            'Email' => $this->user->email,
            'Password' => 'password' // Assuming this is the default password in your factory
        ]);
        $this->token = JWTAuth::fromUser($this->user);

        // Create a contact
        $this->contact = Contact::factory()->create();

        // Configure fake storage
        Storage::fake('local');
    }

    /** @test */
    public function unauthorized_users_cannot_access_document_endpoints()
    {
        $response = $this->getJson('/api/document_contacts');
        $response->assertStatus(401);

        $response = $this->postJson('/api/document_contacts');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_list_all_documents()
    {
        DocumentContact::factory()->count(3)->create([
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/document_contacts');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_create_new_document()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/document_contacts', [
            'Nom_Doc' => 'Test Document',
            'Doc' => $file,
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'Id_Document_contact',
                    'Nom_Doc',
                    'Doc',
                    'ID_Contact'
                ]
            ]);
        $fileName = now()->format('Y-m-d_His') . '_' . $file->getClientOriginalName();
        Storage::disk('local')->assertExists('document_contacts/' . $fileName);
    }

    /** @test */
    public function it_validates_required_fields_when_creating()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/document_contacts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Nom_Doc', 'Doc', 'ID_Contact']);
    }

    /** @test */
    public function it_validates_file_upload()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/document_contacts', [
            'Nom_Doc' => 'Test Document',
            'Doc' => 'invalid-file',
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Doc']);
    }

    /** @test */
    public function it_can_show_specific_document()
    {
        $document = DocumentContact::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/document_contacts/{$document->Id_Document_contact}");

        $response->assertStatus(200)
            ->assertJson([
                'Id_Document_contact' => $document->Id_Document_contact,
                'Nom_Doc' => $document->Nom_Doc,
                'ID_Contact' => $document->ID_Contact
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_document()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/document_contacts/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_document()
    {
        $document = DocumentContact::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $newFile = UploadedFile::fake()->create('updated-document.pdf', 100);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/document_contacts/{$document->Id_Document_contact}", [
            'Nom_Doc' => 'Updated Document',
            'Doc' => $newFile,
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Document contact mis à jour avec succès'
            ]);

        $this->assertDatabaseHas('document_contacts', [
            'Id_Document_contact' => $document->Id_Document_contact,
            'Nom_Doc' => 'Updated Document'
        ]);
    }

    /** @test */
    public function it_can_update_document_without_changing_file()
    {
        $document = DocumentContact::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/document_contacts/{$document->Id_Document_contact}", [
            'Nom_Doc' => 'Updated Document Name Only',
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('document_contacts', [
            'Id_Document_contact' => $document->Id_Document_contact,
            'Nom_Doc' => 'Updated Document Name Only'
        ]);
    }

    /** @test */
    public function it_can_delete_document()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $document = DocumentContact::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact,
            'Doc' => $file->store('document_contacts')
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/document_contacts/{$document->Id_Document_contact}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('document_contacts', [
            'Id_Document_contact' => $document->Id_Document_contact
        ]);

        Storage::disk('local')->assertMissing($document->Doc);
    }

    /** @test */
    public function it_handles_invalid_contact_id()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/document_contacts', [
            'Nom_Doc' => 'Test Document',
            'Doc' => $file,
            'ID_Contact' => 999999 // Non-existent contact ID
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ID_Contact']);
    }

    /** @test */
    public function it_can_show_document_file()
    {
        // Create the storage directory if it doesn't exist
        $storageDir = storage_path('app/document_contacts');
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Create a fake document with a specific name
        $originalFileName = 'document.pdf';
        $file = UploadedFile::fake()->create($originalFileName, 100);

        // Generate the same filename format as in the controller
        $fileName = now()->format('Y-m-d_His') . '_' . $originalFileName;
        $path = 'document_contacts/' . $fileName;

        // Actually store the file in the real storage location
        $file->move(storage_path('app/document_contacts'), $fileName);

        // Create document with this exact path
        $document = DocumentContact::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact,
            'Doc' => $path
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->get("/api/document_contacts/{$document->Id_Document_contact}/document");

        $response->assertStatus(200)
            ->assertHeader('Content-Type');

        // Cleanup
        if (file_exists(storage_path('app/' . $path))) {
            unlink(storage_path('app/' . $path));
        }
    }

    /** @test */
    public function it_handles_missing_document_file()
    {
        $document = DocumentContact::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact,
            'Doc' => 'non_existent_file.pdf'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->get("/api/document_contacts/{$document->Id_Document_contact}/document");

        $response->assertStatus(500);
    }
}
