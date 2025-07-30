<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ThemeGeneral;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class ThemeGeneralControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and get JWT token for authentication
        $this->user = User::factory()->create();
        $response = $this->post('/api/login', [
            'Email' => $this->user->email,
            'Password' => 'password' // Assuming this is the default password in your UserFactory
        ]);

        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test successful theme creation
     */
    public function test_can_create_theme()
    {
        $themeData = [
            'Couleurs' => 'Blue',
            'Horaire_Travail' => 8
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post('/api/theme_generals', $themeData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'ID_Thems',
                    'Couleurs',
                    'Horaire_Travail',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /**
     * Test theme creation validation
     */
    public function test_theme_creation_validation()
    {
        $invalidData = [
            'Couleurs' => '',
            'Horaire_Travail' => 'not-a-number'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post('/api/theme_generals', $invalidData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => [
                    'Couleurs',
                    'Horaire_Travail'
                ]
            ]);
    }

    /**
     * Test retrieving all themes
     */
    public function test_can_get_all_themes()
    {
        // Create some test themes
        ThemeGeneral::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/theme_generals');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /**
     * Test retrieving a specific theme
     */
    public function test_can_get_specific_theme()
    {
        $theme = ThemeGeneral::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/theme_generals/' . $theme->ID_Thems);

        $response->assertStatus(200)
            ->assertJson([
                'ID_Thems' => $theme->ID_Thems,
                'Couleurs' => $theme->Couleurs,
                'Horaire_Travail' => $theme->Horaire_Travail,
            ]);
    }

    /**
     * Test updating a theme
     */
    public function test_can_update_theme()
    {
        $theme = ThemeGeneral::factory()->create();
        $updateData = [
            'Couleurs' => 'Updated Color',
            'Horaire_Travail' => 10
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->put('/api/theme_generals/' . $theme->ID_Thems, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'Couleurs' => 'Updated Color',
                    'Horaire_Travail' => 10
                ]
            ]);
    }

    /**
     * Test partial theme update
     */
    public function test_can_partially_update_theme()
    {
        $theme = ThemeGeneral::factory()->create();
        $partialUpdate = [
            'Couleurs' => 'New Color'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->put('/api/theme_generals/' . $theme->ID_Thems, $partialUpdate);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'Couleurs' => 'New Color',
                    'Horaire_Travail' => $theme->Horaire_Travail
                ]
            ]);
    }

    /**
     * Test deleting a theme
     */
    public function test_can_delete_theme()
    {
        $theme = ThemeGeneral::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->delete('/api/theme_generals/' . $theme->ID_Thems);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('theme_general', ['ID_Thems' => $theme->ID_Thems]);
    }

    /**
     * Test accessing endpoints without authentication
     */
    public function test_authentication_required()
    {
        $response = $this->get('/api/theme_generals');
        $response->assertStatus(401);

        $response = $this->post('/api/theme_generals', [
            'Couleurs' => 'Test',
            'Horaire_Travail' => 8
        ]);
        $response->assertStatus(401);
    }

    /**
     * Test edge cases for Horaire_Travail
     */
    public function test_horaire_travail_bounds()
    {
        $testCases = [
            ['hours' => 0, 'shouldPass' => false],
            ['hours' => 25, 'shouldPass' => false],
            ['hours' => -1, 'shouldPass' => false],
            ['hours' => 24, 'shouldPass' => true],
            ['hours' => 1, 'shouldPass' => true],
        ];

        foreach ($testCases as $case) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->post('/api/theme_generals', [
                'Couleurs' => 'Test Color',
                'Horaire_Travail' => $case['hours']
            ]);

            if ($case['shouldPass']) {
                $response->assertStatus(201);
            } else {
                $response->assertStatus(422);
            }
        }
    }

    /**
     * Test non-existent theme retrieval
     */
    public function test_get_non_existent_theme()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/theme_generals/99999');

        $response->assertStatus(404);
    }

    /**
     * Test concurrent updates
     */
    public function test_concurrent_updates()
    {
        $theme = ThemeGeneral::factory()->create();

        // Simulate two concurrent updates
        $update1 = [
            'Couleurs' => 'Color 1',
            'Horaire_Travail' => 8
        ];

        $update2 = [
            'Couleurs' => 'Color 2',
            'Horaire_Travail' => 9
        ];

        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->put('/api/theme_generals/' . $theme->ID_Thems, $update1);

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->put('/api/theme_generals/' . $theme->ID_Thems, $update2);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Verify the last update won
        $this->assertDatabaseHas('theme_general', [
            'ID_Thems' => $theme->ID_Thems,
            'Couleurs' => 'Color 2',
            'Horaire_Travail' => 9
        ]);
    }
}
