<?php

namespace Tests\Feature;

use App\Models\RendezVous;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Prestation;
use App\Models\Category;
use Tymon\JWTAuth\Facades\JWTAuth;

class PrestationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and get JWT token
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);

        // Create a category for testing
        $this->category = Category::factory()->create();
    }

    /** @test */
    public function unauthenticated_users_cannot_access_prestation_endpoints()
    {
        // Test index endpoint
        $response = $this->getJson('/api/prestations');
        $response->assertStatus(401);

        // Test store endpoint
        $response = $this->postJson('/api/prestations', []);
        $response->assertStatus(401);

        // Test show endpoint
        $response = $this->getJson('/api/prestations/1');
        $response->assertStatus(401);

        // Test update endpoint
        $response = $this->putJson('/api/prestations/1', []);
        $response->assertStatus(401);

        // Test delete endpoint
        $response = $this->deleteJson('/api/prestations/1');
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_create_prestation()
    {
        $prestationData = [
            'Prestations' => 'Test Prestation',
            'Durees' => 2,
            'Prix' => 50.00,
            'ID_Categories' => $this->category->ID_Categories
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/prestations', $prestationData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'ID_Prestation',
                    'Prestations',
                    'Durees',
                    'Prix',
                    'ID_Categories',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('prestations', [
            'Prestations' => 'Test Prestation',
            'Prix' => 50.00
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_prestation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/prestations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Prestations', 'Durees', 'Prix', 'ID_Categories']);
    }

    /** @test */
    public function it_validates_field_types_when_creating_prestation()
    {
        $invalidData = [
            'Prestations' => 123, // should be string
            'Durees' => 'invalid', // should be integer
            'Prix' => 'invalid', // should be numeric
            'ID_Categories' => 999999 // non-existent category
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/prestations', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Prestations', 'Durees', 'Prix', 'ID_Categories']);
    }

    /** @test */
    public function authenticated_user_can_get_all_prestations()
    {
        // Create some test prestations
        Prestation::factory()->count(3)->create([
            'ID_Categories' => $this->category->ID_Categories
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/prestations');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function authenticated_user_can_get_single_prestation()
    {
        $prestation = Prestation::factory()->create([
            'ID_Categories' => $this->category->ID_Categories
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/prestations/{$prestation->ID_Prestation}");

        $response->assertStatus(200)
            ->assertJson([
                'ID_Prestation' => $prestation->ID_Prestation,
                'Prestations' => $prestation->Prestations
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_prestation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/prestations/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function authenticated_user_can_update_prestation()
    {
        $prestation = Prestation::factory()->create([
            'ID_Categories' => $this->category->ID_Categories
        ]);

        $updateData = [
            'Prestations' => 'Updated Prestation',
            'Prix' => 75.00
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/prestations/{$prestation->ID_Prestation}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'Prestations' => 'Updated Prestation',
                    'Prix' => 75.00
                ]
            ]);

        $this->assertDatabaseHas('prestations', [
            'ID_Prestation' => $prestation->ID_Prestation,
            'Prestations' => 'Updated Prestation',
            'Prix' => 75.00
        ]);
    }

    /** @test */
    public function authenticated_user_can_delete_prestation()
    {
        $prestation = Prestation::factory()->create([
            'ID_Categories' => $this->category->ID_Categories
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/prestations/{$prestation->ID_Prestation}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('prestations', [
            'ID_Prestation' => $prestation->ID_Prestation
        ]);
    }

    /** @test */
    public function it_handles_cascade_deletion_with_related_records()
    {
        // Create a prestation
        $prestation = Prestation::factory()->create([
            'ID_Categories' => $this->category->ID_Categories
        ]);

        // Create multiple rendez-vous records associated with this prestation
        $rendezVous1 = RendezVous::factory()->create([
            'ID_Prestation' => $prestation->ID_Prestation
        ]);

        $rendezVous2 = RendezVous::factory()->create([
            'ID_Prestation' => $prestation->ID_Prestation
        ]);

        // Verify the relationships are set up correctly
        $this->assertEquals(2, $prestation->rendezVous()->count());

        // Delete the prestation
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/prestations/{$prestation->ID_Prestation}");

        $response->assertStatus(204);

        // Verify the prestation is deleted
        $this->assertDatabaseMissing('prestations', [
            'ID_Prestation' => $prestation->ID_Prestation
        ]);

        // Verify all related rendez-vous records are also deleted
        $this->assertDatabaseMissing('rendez_vous', [
            'ID_Rendez_Vous' => $rendezVous1->id
        ]);

        $this->assertDatabaseMissing('rendez_vous', [
            'ID_Rendez_Vous' => $rendezVous2->id
        ]);

        // Additional verification that no rendez-vous records exist for this prestation
        $this->assertEquals(
            0,
            RendezVous::where('ID_Prestation', $prestation->ID_Prestation)->count()
        );
    }

    /** @test */
    public function it_validates_maximum_string_length()
    {
        $longString = str_repeat('a', 256); // Create a string longer than max length

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/prestations', [
            'Prestations' => $longString,
            'Durees' => 2,
            'Prix' => 50.00,
            'ID_Categories' => $this->category->ID_Categories
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Prestations']);
    }

    /** @test */
    public function it_validates_price_range()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/prestations', [
            'Prestations' => 'Test Prestation',
            'Durees' => 2,
            'Prix' => 99999999.99, // Testing price exceeding decimal(8,2)
            'ID_Categories' => $this->category->ID_Categories
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_maintains_relationships_after_updates()
    {
        $prestation = Prestation::factory()->create([
            'ID_Categories' => $this->category->ID_Categories
        ]);

        $newCategory = Category::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/prestations/{$prestation->ID_Prestation}", [
            'ID_Categories' => $newCategory->ID_Categories
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('prestations', [
            'ID_Prestation' => $prestation->ID_Prestation,
            'ID_Categories' => $newCategory->ID_Categories
        ]);
    }
}
