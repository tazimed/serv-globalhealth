<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pointage;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class PointageControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user and get JWT token
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test successful pointage creation
     */
    public function test_can_create_pointage()
    {
        $data = [
            'Date' => '2025-02-19'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post('/api/pointages', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'ID_Pointage',
                    'Date',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /**
     * Test validation errors when creating pointage
     */
    public function test_cannot_create_pointage_with_invalid_data()
    {
        $invalidData = [
            'Date' => 'invalid-date'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post('/api/pointages', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors'
            ]);
    }

    /**
     * Test missing required fields
     */
    public function test_cannot_create_pointage_without_required_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post('/api/pointages', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => [
                    'Date'
                ]
            ]);
    }

    /**
     * Test successful pointage retrieval
     */
    public function test_can_get_pointage()
    {
        $pointage = Pointage::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->get('/api/pointages/' . $pointage->ID_Pointage);

        $response->assertStatus(200)
            ->assertJson([
                'ID_Pointage' => $pointage->ID_Pointage,
                'Date' => $pointage->Date
            ]);
    }

    /**
     * Test pointage not found
     */
    public function test_returns_404_when_pointage_not_found()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->get('/api/pointages/999999');

        $response->assertStatus(404);
    }

    /**
     * Test successful pointage update
     */
    public function test_can_update_pointage()
    {
        $pointage = Pointage::factory()->create();
        $updatedData = [
            'Date' => '2025-02-20'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->put('/api/pointages/' . $pointage->ID_Pointage, $updatedData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('pointages', [
            'ID_Pointage' => $pointage->ID_Pointage,
            'Date' => '2025-02-20'
        ]);
    }

    /**
     * Test successful pointage deletion
     */
    public function test_can_delete_pointage()
    {
        $pointage = Pointage::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->delete('/api/pointages/' . $pointage->ID_Pointage);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('pointages', [
            'ID_Pointage' => $pointage->ID_Pointage
        ]);
    }

    /**
     * Test authentication requirement
     */
    public function test_endpoints_require_authentication()
    {
        // Test each endpoint without authentication
        $this->get('/api/pointages')->assertStatus(401);
        $this->post('/api/pointages')->assertStatus(401);
        $this->get('/api/pointages/1')->assertStatus(401);
        $this->put('/api/pointages/1')->assertStatus(401);
        $this->delete('/api/pointages/1')->assertStatus(401);
    }

    /**
     * Test listing all pointages
     */
    public function test_can_list_all_pointages()
    {
        // Create multiple pointages
        Pointage::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->get('/api/pointages');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * Test relationship with users
     */
    public function test_pointage_user_relationship()
    {
        $pointage = Pointage::factory()->create();
        $users = User::factory()->count(2)->create();

        // Attach users to pointage with pivot data
        $pointage->users()->attach($users[0]->id, [
            'Heur_Travail' => 8,
            'Abssance' => false
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->get('/api/pointages/' . $pointage->ID_Pointage);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'users' => [
                    '*' => [
                        'id',
                        'pivot' => [
                            'Heur_Travail',
                            'Abssance'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test concurrent updates
     */
    public function test_concurrent_updates()
    {
        $pointage = Pointage::factory()->create();

        // Simulate two simultaneous updates
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->put('/api/pointages/' . $pointage->ID_Pointage, [
            'Date' => '2025-02-21'
        ]);

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->put('/api/pointages/' . $pointage->ID_Pointage, [
            'Date' => '2025-02-22'
        ]);

        // Both should succeed as there's no version control
        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // The last update should be the one persisted
        $this->assertDatabaseHas('pointages', [
            'ID_Pointage' => $pointage->ID_Pointage,
            'Date' => '2025-02-22'
        ]);
    }
}
