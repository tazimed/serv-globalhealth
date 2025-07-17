<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Rappel;
use Tymon\JWTAuth\Facades\JWTAuth;

class RappelControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and get JWT token
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test successful reminder creation
     */
    public function test_can_create_rappel()
    {
        $rappelData = [
            'Rappel' => 'Test reminder',
            'Etat' => 'pending',
            'ID_User' => $this->user->ID_User
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/rappels', $rappelData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'ID_Rappel',
                    'Rappel',
                    'Etat',
                    'ID_User',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /**
     * Test validation errors during reminder creation
     */
    public function test_cannot_create_rappel_with_invalid_data()
    {
        $invalidData = [
            'Rappel' => '',  // Empty reminder
            'Etat' => '',    // Empty state
            'ID_User' => 999 // Non-existent user
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/rappels', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Rappel', 'Etat', 'ID_User']);
    }

    /**
     * Test unauthorized access
     */
    public function test_cannot_create_rappel_without_authentication()
    {
        $rappelData = [
            'Rappel' => 'Test reminder',
            'Etat' => 'pending',
            'ID_User' => $this->user->ID_User
        ];

        $response = $this->postJson('/api/rappels', $rappelData);

        $response->assertStatus(401);
    }

    /**
     * Test successful reminder retrieval
     */
    public function test_can_get_rappel_list()
    {
        // Create some test reminders
        Rappel::factory()->count(3)->create([
            'ID_User' => $this->user->ID_User
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/rappels');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * Test successful single reminder retrieval
     */
    public function test_can_get_single_rappel()
    {
        $rappel = Rappel::factory()->create([
            'ID_User' => $this->user->ID_User
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/rappels/{$rappel->ID_Rappel}");

        $response->assertStatus(200)
            ->assertJson([
                'ID_Rappel' => $rappel->ID_Rappel,
                'Rappel' => $rappel->Rappel,
                'Etat' => $rappel->Etat,
                'ID_User' => $this->user->ID_User
            ]);
    }

    /**
     * Test not found error for non-existent reminder
     */
    public function test_cannot_get_non_existent_rappel()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/rappels/999');

        $response->assertStatus(404);
    }

    /**
     * Test successful reminder update
     */
    public function test_can_update_rappel()
    {
        $rappel = Rappel::factory()->create([
            'ID_User' => $this->user->ID_User
        ]);

        $updateData = [
            'Rappel' => 'Updated reminder',
            'Etat' => 'completed'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/rappels/{$rappel->ID_Rappel}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'Rappel' => 'Updated reminder',
                    'Etat' => 'completed'
                ]
            ]);
    }

    /**
     * Test partial reminder update
     */
    public function test_can_partially_update_rappel()
    {
        $rappel = Rappel::factory()->create([
            'ID_User' => $this->user->ID_User
        ]);

        $updateData = [
            'Etat' => 'completed'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/rappels/{$rappel->ID_Rappel}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'Etat' => 'completed'
                ]
            ]);
    }

    /**
     * Test successful reminder deletion
     */
    public function test_can_delete_rappel()
    {
        $rappel = Rappel::factory()->create([
            'ID_User' => $this->user->ID_User
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/rappels/{$rappel->ID_Rappel}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('rappels', ['ID_Rappel' => $rappel->ID_Rappel]);
    }

    /**
     * Test cascade deletion when user is deleted
     */
    public function test_rappels_are_deleted_when_user_is_deleted()
    {
        $rappel = Rappel::factory()->create([
            'ID_User' => $this->user->ID_User
        ]);

        $this->user->delete();

        $this->assertDatabaseMissing('rappels', ['ID_Rappel' => $rappel->ID_Rappel]);
    }

    /**
     * Test reminder creation with XSS attempt
     */
    public function test_rappel_creation_sanitizes_xss()
    {
        $xssPayload = "<script>alert('xss')</script>Test reminder";

        $rappelData = [
            'Rappel' => $xssPayload,
            'Etat' => 'pending',
            'ID_User' => $this->user->ID_User
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/rappels', $rappelData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('rappels', [
            'Rappel' => htmlspecialchars($xssPayload, ENT_QUOTES, 'UTF-8')
        ]);
    }

    /**
     * Test reminder creation with very long input
     */
    public function test_can_handle_long_rappel_text()
    {
        $longText = str_repeat('a', 1000);

        $rappelData = [
            'Rappel' => $longText,
            'Etat' => 'pending',
            'ID_User' => $this->user->ID_User
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/rappels', $rappelData);

        $response->assertStatus(201);
    }
}
