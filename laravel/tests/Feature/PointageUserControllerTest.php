<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pointage;
use App\Models\PointageUser;
use Tymon\JWTAuth\Facades\JWTAuth;

class PointageUserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $token;
    private $pointage;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user and get JWT token
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);

        // Create a test pointage
        $this->pointage = Pointage::factory()->create();
    }

    /**
     * Test successful creation of pointage-user relationship
     */
    public function test_can_create_pointage_user_relationship()
    {
        $data = [
            'Heur_Travail' => 8,
            'Abssance' => false
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post("/api/pointage_users/{$this->pointage->ID_Pointage}/{$this->user->ID_User}", $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'pointageUser' => [
                    'ID_Pointage',
                    'ID_User',
                    'Heur_Travail',
                    'Abssance'
                ],
                'error',
                'message'
            ]);
    }

    /**
     * Test validation of working hours
     */
    public function test_validates_working_hours()
    {
        $invalidData = [
            'Heur_Travail' => 'not-a-number',
            'Abssance' => false
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post("/api/pointage_users/{$this->pointage->ID_Pointage}/{$this->user->ID_User}", $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => [
                    'Heur_Travail'
                ]
            ]);
    }

    /**
     * Test validation of absence field
     */
    public function test_validates_absence_field()
    {
        $invalidData = [
            'Heur_Travail' => 8,
            'Abssance' => 'not-a-boolean'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post("/api/pointage_users/{$this->pointage->ID_Pointage}/{$this->user->ID_User}", $invalidData);

        $response->assertStatus(422);
    }

    /**
     * Test deleting pointage-user relationship
     */
    public function test_can_delete_pointage_user_relationship()
    {
        // First create the relationship
        PointageUser::create([
            'ID_Pointage' => $this->pointage->ID_Pointage,
            'ID_User' => $this->user->ID_User,
            'Heur_Travail' => 8,
            'Abssance' => false
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->delete("/api/pointage_users/{$this->pointage->ID_Pointage}/{$this->user->ID_User}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Supprimé avec succès']);

        $this->assertDatabaseMissing('pointage_user', [
            'ID_Pointage' => $this->pointage->ID_Pointage,
            'ID_User' => $this->user->ID_User
        ]);
    }

    /**
     * Test deleting non-existent relationship
     */
    public function test_delete_nonexistent_relationship_returns_404()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->delete("/api/pointage_users/{$this->pointage->ID_Pointage}/99999");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Aucun enregistrement trouvé pour suppression']);
    }

    /**
     * Test authentication requirement
     */
    public function test_endpoints_require_authentication()
    {
        $data = [
            'Heur_Travail' => 8,
            'Abssance' => false
        ];

        // Test without authentication
        $this->post("/api/pointage_users/{$this->pointage->ID_Pointage}/{$this->user->ID_User}", $data)
            ->assertStatus(401);

        $this->delete("/api/pointage_users/{$this->pointage->ID_Pointage}/{$this->user->ID_User}")
            ->assertStatus(401);
    }

    /**
     * Test with invalid pointage ID
     */
    public function test_handles_invalid_pointage_id()
    {
        $data = [
            'Heur_Travail' => 8,
            'Abssance' => false
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post("/api/pointage_users/99999/{$this->user->ID_User}", $data);

        $response->assertStatus(500);
    }

    /**
     * Test with invalid user ID
     */
    public function test_handles_invalid_user_id()
    {
        $data = [
            'Heur_Travail' => 8,
            'Abssance' => false
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post("/api/pointage_users/{$this->pointage->ID_Pointage}/99999", $data);

        $response->assertStatus(500);
    }
}
