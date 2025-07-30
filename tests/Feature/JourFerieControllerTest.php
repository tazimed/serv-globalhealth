<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\JourFerie;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class JourFerieControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $token;
    private $validHoliday;
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

        // Prepare valid holiday data
        $this->validHoliday = [
            'Date_debut' => '2025-01-01',
            'Date_fin' => '2025-01-02'
        ];
    }

    /**
     * Test successful holiday creation
     */
    public function test_can_create_holiday()
    {
        $response = $this->postJson('/api/jours_feries', $this->validHoliday, $this->headers);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'Id_Jour_feries',
                    'Date_debut',
                    'Date_fin',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('jour_feries', [
            'Date_debut' => $this->validHoliday['Date_debut'],
            'Date_fin' => $this->validHoliday['Date_fin']
        ]);
    }

    /**
     * Test validation rules for holiday creation
     */
    public function test_holiday_creation_validation()
    {
        // Test missing dates
        $response = $this->postJson('/api/jours_feries', [], $this->headers);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Date_debut', 'Date_fin']);

        // Test invalid date format
        $response = $this->postJson('/api/jours_feries', [
            'Date_debut' => 'invalid-date',
            'Date_fin' => 'invalid-date'
        ], $this->headers);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Date_debut', 'Date_fin']);

        // Test end date before start date
        $response = $this->postJson('/api/jours_feries', [
            'Date_debut' => '2025-01-02',
            'Date_fin' => '2025-01-01'
        ], $this->headers);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Date_fin']);
    }

    /**
     * Test holiday listing
     */
    public function test_can_list_holidays()
    {
        // Create some test holidays
        JourFerie::factory()->count(3)->create();

        $response = $this->getJson('/api/jours_feries', $this->headers);

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * Test viewing a specific holiday
     */
    public function test_can_show_holiday()
    {
        $holiday = JourFerie::factory()->create();

        $response = $this->getJson("/api/jours_feries/{$holiday->Id_Jour_feries}", $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'Id_Jour_feries' => $holiday->Id_Jour_feries,
                'Date_debut' => $holiday->Date_debut,
                'Date_fin' => $holiday->Date_fin
            ]);
    }

    /**
     * Test holiday update
     */
    public function test_can_update_holiday()
    {
        $holiday = JourFerie::factory()->create();

        $updatedData = [
            'Date_debut' => '2025-02-01',
            'Date_fin' => '2025-02-02'
        ];

        $response = $this->putJson("/api/jours_feries/{$holiday->Id_Jour_feries}", $updatedData, $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Jour férié mis à jour avec succès'
            ]);

        $this->assertDatabaseHas('jour_feries', [
            'Id_Jour_feries' => $holiday->Id_Jour_feries,
            'Date_debut' => $updatedData['Date_debut'],
            'Date_fin' => $updatedData['Date_fin']
        ]);
    }

    /**
     * Test holiday deletion
     */
    public function test_can_delete_holiday()
    {
        $holiday = JourFerie::factory()->create();

        $response = $this->deleteJson("/api/jours_feries/{$holiday->Id_Jour_feries}", [], $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('jour_feries', [
            'Id_Jour_feries' => $holiday->Id_Jour_feries
        ]);
    }

    /**
     * Test edge cases
     */
    public function test_edge_cases()
    {
        // Test viewing non-existent holiday
        $response = $this->getJson('/api/jours_feries/99999', $this->headers);
        $response->assertStatus(404);

        // Test updating non-existent holiday
        $response = $this->putJson('/api/jours_feries/99999', $this->validHoliday, $this->headers);
        $response->assertStatus(500);

        // Test deleting non-existent holiday
        $response = $this->deleteJson('/api/jours_feries/99999', [], $this->headers);
        $response->assertStatus(404);

        // Test same day holiday
        $sameDayHoliday = [
            'Date_debut' => '2025-01-01',
            'Date_fin' => '2025-01-01'
        ];
        $response = $this->postJson('/api/jours_feries', $sameDayHoliday, $this->headers);
        $response->assertStatus(201);
    }

    /**
     * Test date range validation and business logic
     */
    public function test_date_range_validation()
    {
        // Test far future dates
        $farFutureHoliday = [
            'Date_debut' => '2050-01-01',
            'Date_fin' => '2050-01-02'
        ];
        $response = $this->postJson('/api/jours_feries', $farFutureHoliday, $this->headers);
        $response->assertStatus(201);

        // Test past dates
        $pastHoliday = [
            'Date_debut' => '2020-01-01',
            'Date_fin' => '2020-01-02'
        ];
        $response = $this->postJson('/api/jours_feries', $pastHoliday, $this->headers);
        $response->assertStatus(201);

        // Test long duration holiday
        $longHoliday = [
            'Date_debut' => '2025-01-01',
            'Date_fin' => '2025-12-31'
        ];
        $response = $this->postJson('/api/jours_feries', $longHoliday, $this->headers);
        $response->assertStatus(201);
    }

    /**
     * Test concurrent modifications
     */
    public function test_concurrent_modifications()
    {
        $holiday = JourFerie::factory()->create();

        // Simulate two users trying to update the same holiday
        $user2 = User::factory()->create();
        $token2 = JWTAuth::fromUser($user2);
        $headers2 = ['Authorization' => 'Bearer ' . $token2];

        // First update
        $response1 = $this->putJson("/api/jours_feries/{$holiday->Id_Jour_feries}", [
            'Date_debut' => '2025-03-01',
            'Date_fin' => '2025-03-02'
        ], $this->headers);

        // Second update
        $response2 = $this->putJson("/api/jours_feries/{$holiday->Id_Jour_feries}", [
            'Date_debut' => '2025-04-01',
            'Date_fin' => '2025-04-02'
        ], $headers2);

        // Both updates should succeed as there's no optimistic locking
        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // The last update should be the one persisted
        $this->assertDatabaseHas('jour_feries', [
            'Id_Jour_feries' => $holiday->Id_Jour_feries,
            'Date_debut' => '2025-04-01',
            'Date_fin' => '2025-04-02'
        ]);
    }
}
