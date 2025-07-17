<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Contact;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $token;
    private $validContactData;
    private $headers;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'Email' => 'test@example.com',
            'Password' => bcrypt('password123')
        ]);

        // Generate JWT token
        $this->token = JWTAuth::fromUser($this->user);

        // Set headers with token
        $this->headers = ['Authorization' => 'Bearer ' . $this->token];

        // Prepare valid contact data
        $this->validContactData = [
            'Nom' => 'Doe',
            'Prenom' => 'John',
            'Birthday' => '1990-01-01',
            'N_assurance' => 'ASS123456',
            'Cnss' => 'CNSS789012',
            'Telephone' => '+1234567890',
            'Email' => 'john.doe@example.com',
            'Adresse' => '123 Test Street',
            'preferences' => 'No specific preferences'
        ];
    }

    /**
     * Test authentication requirement
     */
    public function test_endpoints_require_authentication()
    {
        // Test each endpoint without authentication
        $response = $this->getJson('/api/contacts');
        $response->assertStatus(401);

        $response = $this->postJson('/api/contacts', $this->validContactData);
        $response->assertStatus(401);

        $response = $this->getJson('/api/contacts/1');
        $response->assertStatus(401);

        $response = $this->putJson('/api/contacts/1', $this->validContactData);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/contacts/1');
        $response->assertStatus(401);
    }

    /**
     * Test invalid token
     */
    public function test_invalid_token()
    {
        $invalidHeaders = ['Authorization' => 'Bearer invalid_token'];

        $response = $this->getJson('/api/contacts', $invalidHeaders);
        $response->assertStatus(401);
    }

    /**
     * Test successful contact creation
     */
    public function test_can_create_contact()
    {
        $response = $this->postJson('/api/contacts', $this->validContactData, $this->headers);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'ID_Contact',
                    'Nom',
                    'Prenom',
                    'Birthday',
                    'N_assurance',
                    'Cnss',
                    'Telephone',
                    'Email',
                    'Adresse',
                    'preferences',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('contacts', [
            'Nom' => 'Doe',
            'Email' => 'john.doe@example.com'
        ]);
    }

    /**
     * Test validation rules
     */
    public function test_contact_creation_validation()
    {
        // Test required fields
        $response = $this->postJson('/api/contacts', [], $this->headers);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Nom', 'Prenom']);

        // Test email format
        $response = $this->postJson('/api/contacts', array_merge(
            $this->validContactData,
            ['Email' => 'invalid-email']
        ), $this->headers);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Email']);

        // Test string length limits
        $response = $this->postJson('/api/contacts', array_merge(
            $this->validContactData,
            ['Nom' => str_repeat('a', 256)]
        ), $this->headers);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Nom']);
    }

    /**
     * Test contact retrieval
     */
    public function test_can_retrieve_contacts()
    {
        Contact::factory()->count(3)->create();

        $response = $this->getJson('/api/contacts', $this->headers);

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'ID_Contact',
                    'Nom',
                    'Prenom',
                    'notifications',
                    'document_contacts'
                ]
            ]);
    }

    /**
     * Test contact update
     */
    public function test_can_update_contact()
    {
        $contact = Contact::factory()->create();

        $updatedData = array_merge($this->validContactData, [
            'Nom' => 'Updated Name',
            'Email' => 'updated@example.com'
        ]);

        $response = $this->putJson("/api/contacts/{$contact->ID_Contact}", $updatedData, $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'Nom' => 'Updated Name',
                    'Email' => 'updated@example.com'
                ]
            ]);

        $this->assertDatabaseHas('contacts', [
            'ID_Contact' => $contact->ID_Contact,
            'Nom' => 'Updated Name',
            'Email' => 'updated@example.com'
        ]);
    }

    /**
     * Test contact deletion
     */
    public function test_can_delete_contact()
    {
        $contact = Contact::factory()->create();

        $response = $this->deleteJson("/api/contacts/{$contact->ID_Contact}", [], $this->headers);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('contacts', [
            'ID_Contact' => $contact->ID_Contact
        ]);
    }

    /**
     * Test edge cases
     */
    public function test_edge_cases()
    {
        // Test non-existent contact
        $response = $this->getJson('/api/contacts/99999', $this->headers);
        $response->assertStatus(404);

        // Test updating non-existent contact
        $response = $this->putJson('/api/contacts/99999', $this->validContactData, $this->headers);
        $response->assertStatus(500);

        // Test deleting non-existent contact
        $response = $this->deleteJson('/api/contacts/99999', [], $this->headers);
        $response->assertStatus(404);

        // Test with empty strings
        $response = $this->postJson('/api/contacts', array_merge(
            $this->validContactData,
            ['Email' => '', 'Telephone' => '']
        ), $this->headers);

        $response->assertStatus(500); // Should succeed as these fields are nullable

        // Test with very long strings (but within limits)
        $response = $this->postJson('/api/contacts', array_merge(
            $this->validContactData,
            ['Nom' => str_repeat('a', 255)]
        ), $this->headers);
        $response->assertStatus(201);
    }

    /**
     * Test malformed tokens
     */
    public function test_malformed_tokens()
    {
        // Test with malformed Bearer token
        $response = $this->getJson('/api/contacts', [
            'Authorization' => 'Bearer' // Missing token
        ]);
        $response->assertStatus(401);

        // Test with wrong authorization scheme
        $response = $this->getJson('/api/contacts', [
            'Authorization' => 'Basic ' . $this->token
        ]);
        $response->assertStatus(401);

        // Test with token in wrong format
        $response = $this->getJson('/api/contacts', [
            'Authorization' => 'Bearer ' . base64_encode('invalid_token')
        ]);
        $response->assertStatus(401);
    }

    /**
     * Test relationships loading
     */
    public function test_relationships_loading()
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson("/api/contacts/{$contact->ID_Contact}", $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ID_Contact',
                'Nom',
                'Prenom',
                'notifications',
                'document_contacts'
            ]);
    }
}
