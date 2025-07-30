<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Paiement;
use App\Models\User;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class PaiementControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_paiement_endpoints()
    {
        // Index
        $response = $this->getJson('/api/paiements');
        $response->assertStatus(401);

        // Store
        $response = $this->postJson('/api/paiements', []);
        $response->assertStatus(401);

        // Show
        $response = $this->getJson('/api/paiements/1');
        $response->assertStatus(401);

        // Update
        $response = $this->putJson('/api/paiements/1', []);
        $response->assertStatus(401);

        // Delete
        $response = $this->deleteJson('/api/paiements/1');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_list_all_paiements()
    {
        // Create some paiements
        Paiement::factory()->count(3)->create(['ID_User' => $this->user->ID_User]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/paiements');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'ID_Paiement',
                    'Date',
                    'Type',
                    'Absence_sup',
                    'ID_User',
                    'user'
                ]
            ]);
    }

    /** @test */
    public function it_can_create_a_paiement()
    {
        $paiementData = [
            'Date' => '2025-02-19',
            'Type' => 'Salaire',
            'Absence_sup' => 2.5,
            'ID_User' => $this->user->ID_User
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/paiements', $paiementData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Paiement créé avec succès',
                'data' => $paiementData
            ]);

        $this->assertDatabaseHas('paiements', $paiementData);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_paiement()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/paiements', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'Date',
                'Type',
                'ID_User'
            ]);
    }

    /** @test */
    public function it_validates_date_format()
    {
        $paiementData = [
            'Date' => 'invalid-date',
            'Type' => 'Salaire',
            'Absence_sup' => 2.5,
            'ID_User' => $this->user->ID_User
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/paiements', $paiementData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Date']);
    }

    /** @test */
    public function it_validates_numeric_absence_sup()
    {
        $paiementData = [
            'Date' => '2025-02-19',
            'Type' => 'Salaire',
            'Absence_sup' => 'not-a-number',
            'ID_User' => $this->user->ID_User
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/paiements', $paiementData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Absence_sup']);
    }

    /** @test */
    public function it_can_show_a_paiement()
    {
        $paiement = Paiement::factory()->create(['ID_User' => $this->user->ID_User]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/paiements/{$paiement->ID_Paiement}");

        $response->assertStatus(200)
            ->assertJson([
                'ID_Paiement' => $paiement->ID_Paiement,
                'Date' => $paiement->Date,
                'Type' => $paiement->Type,
                'Absence_sup' => (string)$paiement->Absence_sup,
                'ID_User' => $paiement->ID_User
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_non_existent_paiement()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/paiements/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_a_paiement()
    {
        $paiement = Paiement::factory()->create(['ID_User' => $this->user->ID_User]);

        $updateData = [
            'Date' => '2025-03-01',
            'Type' => 'Prime',
            'Absence_sup' => 1.5
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/paiements/{$paiement->ID_Paiement}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Paiement mis à jour avec succès'
            ]);

        $this->assertDatabaseHas('paiements', array_merge(
            ['ID_Paiement' => $paiement->ID_Paiement],
            $updateData
        ));
    }

    /** @test */
    public function it_can_handle_partial_updates()
    {
        $paiement = Paiement::factory()->create(['ID_User' => $this->user->ID_User]);

        $updateData = [
            'Type' => 'Prime'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/paiements/{$paiement->ID_Paiement}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('paiements', [
            'ID_Paiement' => $paiement->ID_Paiement,
            'Type' => 'Prime',
            'Date' => $paiement->Date,
            'Absence_sup' => $paiement->Absence_sup
        ]);
    }

    /** @test */
    public function it_validates_decimal_precision_for_absence_sup()
    {
        $paiementData = [
            'Date' => '2025-02-19',
            'Type' => 'Salaire',
            'Absence_sup' => 999999.99, // Valid value for decimal(8,2)
            'ID_User' => $this->user->ID_User
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/paiements', $paiementData);

        // Assert the response status
        $response->assertStatus(201); // Or assertStatus(200) depending on your API response

        // Fetch the created Paiement record
        $created = Paiement::latest()->first();

        // Ensure the Paiement was created successfully
        $this->assertNotNull($created);

        // Assert the decimal precision
        $this->assertEquals(8, strlen(explode('.', $created->Absence_sup)[0])); // Check total digits before the decimal
        $this->assertLessThanOrEqual(2, strlen(explode('.', $created->Absence_sup)[1])); // Check decimal places
    }

    /** @test */
    public function it_can_delete_a_paiement()
    {
        $paiement = Paiement::factory()->create(['ID_User' => $this->user->ID_User]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/paiements/{$paiement->ID_Paiement}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('paiements', ['ID_Paiement' => $paiement->ID_Paiement]);
    }

    /** @test */
    public function it_maintains_relationship_with_user()
    {
        $paiement = Paiement::factory()->create(['ID_User' => $this->user->ID_User]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/paiements/{$paiement->ID_Paiement}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ID_Paiement',
                'Date',
                'Type',
                'Absence_sup',
                'ID_User',
                'user' => [
                    'ID_User',
                    // Add other user fields you want to verify
                ]
            ]);
    }

    /** @test */
    public function it_validates_future_dates()
    {
        $futureDate = Carbon::now()->addYears(10)->format('Y-m-d');

        $paiementData = [
            'Date' => $futureDate,
            'Type' => 'Salaire',
            'Absence_sup' => 2.5,
            'ID_User' => $this->user->ID_User
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/paiements', $paiementData);

        // This should still work as there's no validation against future dates
        // But you might want to add such validation depending on your business rules
        $response->assertStatus(201);
    }
}
