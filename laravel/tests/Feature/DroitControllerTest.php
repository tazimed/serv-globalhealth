<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Droit;
use App\Models\Role;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class DroitControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $role;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a role
        $this->role = Role::factory()->create();

        // Create and authenticate a user
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_droit_endpoints()
    {
        // Index
        $response = $this->getJson('/api/droits');
        $response->assertStatus(401);

        // Store
        $response = $this->postJson('/api/droits', []);
        $response->assertStatus(401);

        // Show
        $response = $this->getJson('/api/droits/1');
        $response->assertStatus(401);

        // Update
        $response = $this->putJson('/api/droits/1', []);
        $response->assertStatus(401);

        // Delete
        $response = $this->deleteJson('/api/droits/1');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_list_all_droits()
    {
        // Create some droits
        Droit::factory()->count(3)->create(['ID_Role' => $this->role->ID_Role]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/droits');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'ID_Droit',
                    'Droit',
                    'Lecture',
                    'Ajouter',
                    'Modifier',
                    'Supprimer',
                    'ID_Role',
                    'role'
                ]
            ]);
    }

    /** @test */
    public function it_can_create_a_droit()
    {
        $droitData = [
            'Droit' => 'Test Permission',
            'Lecture' => true,
            'Ajouter' => true,
            'Modifier' => false,
            'Supprimer' => false,
            'ID_Role' => $this->role->ID_Role
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/droits', $droitData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Droit créé avec succès',
                'data' => $droitData
            ]);

        $this->assertDatabaseHas('droits', $droitData);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_droit()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/droits', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'Droit',
                'Lecture',
                'Ajouter',
                'Modifier',
                'Supprimer',
                'ID_Role'
            ]);
    }

    /** @test */
    public function it_can_show_a_droit()
    {
        $droit = Droit::factory()->create(['ID_Role' => $this->role->ID_Role]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/droits/{$droit->ID_Droit}");

        $response->assertStatus(200)
            ->assertJson([
                'ID_Droit' => $droit->ID_Droit,
                'Droit' => $droit->Droit,
                'ID_Role' => $droit->ID_Role
            ]);
    }

    /** @test */
    public function it_returns_404_when_showing_non_existent_droit()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/droits/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_a_droit()
    {
        $droit = Droit::factory()->create(['ID_Role' => $this->role->ID_Role]);

        $updateData = [
            'Droit' => 'Updated Permission',
            'Lecture' => false,
            'Ajouter' => true,
            'Modifier' => true,
            'Supprimer' => false,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/droits/{$droit->ID_Droit}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Droit mis à jour avec succès'
            ]);

        $this->assertDatabaseHas('droits', array_merge(
            ['ID_Droit' => $droit->ID_Droit],
            $updateData
        ));
    }

    /** @test */
    public function it_validates_boolean_fields()
    {
        $droitData = [
            'Droit' => 'Test Permission',
            'Lecture' => 'not-a-boolean',
            'Ajouter' => 'invalid',
            'Modifier' => 'not-a-boolean',
            'Supprimer' => 'true',
            'ID_Role' => $this->role->ID_Role
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/droits', $droitData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'Lecture',
                'Ajouter',
                'Modifier',
                'Supprimer'
            ]);
    }

    /** @test */
    public function it_validates_role_exists()
    {
        $droitData = [
            'Droit' => 'Test Permission',
            'Lecture' => true,
            'Ajouter' => true,
            'Modifier' => false,
            'Supprimer' => false,
            'ID_Role' => 99999 // Non-existent role ID
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/droits', $droitData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ID_Role']);
    }

    /** @test */
    public function it_can_delete_a_droit()
    {
        $droit = Droit::factory()->create(['ID_Role' => $this->role->ID_Role]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/droits/{$droit->ID_Droit}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('droits', ['ID_Droit' => $droit->ID_Droit]);
    }

    /** @test */
    public function it_returns_404_when_deleting_non_existent_droit()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/droits/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_enforces_string_length_limit_for_droit_name()
    {
        $droitData = [
            'Droit' => str_repeat('a', 256), // Exceeds 255 characters
            'Lecture' => true,
            'Ajouter' => true,
            'Modifier' => false,
            'Supprimer' => false,
            'ID_Role' => $this->role->ID_Role
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/droits', $droitData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Droit']);
    }

    /** @test */
    public function it_maintains_relationship_with_role()
    {
        $droit = Droit::factory()->create(['ID_Role' => $this->role->ID_Role]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/droits/{$droit->ID_Droit}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ID_Droit',
                'Droit',
                'ID_Role',
                'role' => [
                    'ID_Role',
                    // Add other role fields you want to verify
                ]
            ]);
    }
}
