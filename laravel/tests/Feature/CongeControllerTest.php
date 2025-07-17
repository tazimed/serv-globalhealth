<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Conge;
use Tymon\JWTAuth\Facades\JWTAuth;

class CongeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $token;
    private $validCongeData;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user and generate JWT token
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);

        // Set up valid conge data
        $this->validCongeData = [
            'Date_debut' => now()->format('Y-m-d'),
            'Date_fin' => now()->addDays(5)->format('Y-m-d'),
            'Type' => 'Congé annuel',
            'ID_User' => $this->user->ID_User
        ];
    }

    /**
     * Test authentication requirements
     */
    public function test_unauthenticated_users_cannot_access_conge_endpoints()
    {
        // Test each endpoint without token
        $response = $this->getJson('/api/conges');
        $response->assertStatus(401);

        $response = $this->postJson('/api/conges', $this->validCongeData);
        $response->assertStatus(401);

        $response = $this->getJson('/api/conges/1');
        $response->assertStatus(401);

        $response = $this->putJson('/api/conges/1', $this->validCongeData);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/conges/1');
        $response->assertStatus(401);
    }

    /**
     * Test creating a new conge with valid data
     */
    public function test_can_create_conge_with_valid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/conges', $this->validCongeData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'Id_Conge',
                    'Date_debut',
                    'Date_fin',
                    'Type',
                    'ID_User',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('conges', [
            'Date_debut' => $this->validCongeData['Date_debut'],
            'Date_fin' => $this->validCongeData['Date_fin'],
            'Type' => $this->validCongeData['Type'],
            'ID_User' => $this->validCongeData['ID_User']
        ]);
    }

    /**
     * Test validation rules for creating a conge
     */
    public function test_cannot_create_conge_with_invalid_data()
    {
        // Test with empty data
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/conges', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Date_debut', 'Date_fin', 'Type', 'ID_User']);

        // Test with end date before start date
        $invalidData = $this->validCongeData;
        $invalidData['Date_fin'] = now()->subDays(1)->format('Y-m-d');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/conges', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Date_fin']);

        // Test with non-existent user ID
        $invalidData = $this->validCongeData;
        $invalidData['ID_User'] = 99999;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/conges', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ID_User']);
    }

    /**
     * Test retrieving conges
     */
    public function test_can_retrieve_conges()
    {
        // Create multiple conges
        Conge::factory()->count(3)->create(['ID_User' => $this->user->ID_User]);

        // Test index endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/conges');

        $response->assertStatus(200)
            ->assertJsonCount(3);

        // Test show endpoint
        $conge = Conge::first();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/conges/{$conge->Id_Conge}");

        $response->assertStatus(200)
            ->assertJson([
                'Id_Conge' => $conge->Id_Conge,
                'Type' => $conge->Type
            ]);
    }

    /**
     * Test updating a conge
     */
    public function test_can_update_conge()
    {
        $conge = Conge::factory()->create(['ID_User' => $this->user->ID_User]);

        $updatedData = [
            'Type' => 'Congé maladie',
            'Date_fin' => now()->addDays(10)->format('Y-m-d')
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/conges/{$conge->Id_Conge}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'Type' => 'Congé maladie'
                ]
            ]);

        $this->assertDatabaseHas('conges', [
            'Id_Conge' => $conge->Id_Conge,
            'Type' => 'Congé maladie'
        ]);
    }

    /**
     * Test deleting a conge
     */
    public function test_can_delete_conge()
    {
        $conge = Conge::factory()->create(['ID_User' => $this->user->ID_User]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/conges/{$conge->Id_Conge}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('conges', ['Id_Conge' => $conge->Id_Conge]);
    }

    /**
     * Test invalid token format
     */
    public function test_invalid_token_format()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token_format'
        ])->getJson('/api/conges');

        $response->assertStatus(401);
    }

    /**
     * Test edge cases
     */
    public function test_edge_cases()
    {
        // Test creating overlapping leaves
        $existingConge = Conge::factory()->create([
            'ID_User' => $this->user->ID_User,
            'Date_debut' => now()->format('Y-m-d'),
            'Date_fin' => now()->addDays(5)->format('Y-m-d')
        ]);

        $overlappingData = $this->validCongeData;
        $overlappingData['Date_debut'] = now()->addDays(2)->format('Y-m-d');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/conges', $overlappingData);

        $response->assertStatus(422);

        // Test with very long date ranges
        $longRangeData = $this->validCongeData;
        $longRangeData['Date_debut'] = now()->format('Y-m-d');
        $longRangeData['Date_fin'] = now()->addYears(2)->format('Y-m-d');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/conges', $longRangeData);

        $response->assertStatus(422);

        // Test retrieving non-existent conge
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/conges/99999');

        $response->assertStatus(404);
    }

    /**
     * Test security cases
     */
    public function test_security_cases()
    {
        // Create another user and their token
        $anotherUser = User::factory()->create();
        $anotherToken = JWTAuth::fromUser($anotherUser);

        // Create a conge for another user
        $otherUserConge = Conge::factory()->create(['ID_User' => $anotherUser->ID_User]);

        // Test accessing another user's conge
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/conges/{$otherUserConge->Id_Conge}");

        $response->assertStatus(403);

        // Test updating another user's conge
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/conges/{$otherUserConge->Id_Conge}", $this->validCongeData);

        $response->assertStatus(403);

        // Test deleting another user's conge
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/conges/{$otherUserConge->Id_Conge}");

        $response->assertStatus(403);

        // Test with malformed token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer malformed.jwt.token'
        ])->getJson('/api/conges');

        $response->assertStatus(401);
    }
}
