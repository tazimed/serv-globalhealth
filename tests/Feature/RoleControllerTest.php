<?php

namespace Tests\Feature;

use App\Models\Droit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Basic CRUD Tests
    public function test_can_get_all_roles()
    {
        // Arrange
        $roles = Role::factory()->count(3)->create();

        // Act
        $response = $this->getJson('/api/roles');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => ['ID_Role', 'Role', 'users', 'droits']
            ]);
    }

    public function test_can_create_role()
    {
        // Arrange
        $roleData = ['Role' => 'Test Role'];

        // Act
        $response = $this->postJson('/api/roles', $roleData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Rôle créé avec succès',
                'data' => [
                    'Role' => 'Test Role'
                ]
            ]);

        $this->assertDatabaseHas('roles', $roleData);
    }

    public function test_can_show_role()
    {
        // Arrange
        $role = Role::factory()->create();

        // Act
        $response = $this->getJson("/api/roles/{$role->ID_Role}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'ID_Role' => $role->ID_Role,
                'Role' => $role->Role
            ]);
    }

    public function test_can_update_role()
    {
        // Arrange
        $role = Role::factory()->create();
        $updateData = ['Role' => 'Updated Role'];

        // Act
        $response = $this->putJson("/api/roles/{$role->ID_Role}", $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Rôle mis à jour avec succès'
            ]);

        $this->assertDatabaseHas('roles', $updateData);
    }

    public function test_can_delete_role()
    {
        // Arrange
        $role = Role::factory()->create();

        // Act
        $response = $this->deleteJson("/api/roles/{$role->ID_Role}");

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('roles', ['ID_Role' => $role->ID_Role]);
    }

    // Validation Tests
    public function test_role_creation_requires_role_field()
    {
        // Act
        $response = $this->postJson('/api/roles', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Role']);
    }

    public function test_role_name_cannot_exceed_max_length()
    {
        // Arrange
        $roleData = ['Role' => str_repeat('a', 256)];

        // Act
        $response = $this->postJson('/api/roles', $roleData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Role']);
    }

    // Edge Cases
    public function test_cannot_create_role_with_invalid_data_type()
    {
        // Arrange
        $roleData = ['Role' => ['invalid_array']];

        // Act
        $response = $this->postJson('/api/roles', $roleData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Role']);
    }

    public function test_cannot_update_nonexistent_role()
    {
        // Act
        $response = $this->putJson('/api/roles/999999', ['Role' => 'Test Role']);
        // Assert
        $response->assertStatus(500);
        $response->assertJson([
            'status' => 'error',
            'message' => "Une erreur s'est produite lors du traitement de votre demande",
            'error' => 'No query results for model [App\\Models\\Role] 999999',
        ]);
    }

    public function test_cannot_delete_nonexistent_role()
    {
        // Act
        $response = $this->deleteJson('/api/roles/999999');

        // Assert
        $response->assertStatus(404);
    }

    // Relationship Tests
    public function test_role_includes_related_users_and_droits()
    {
        // Arrange
        $role = Role::factory()->create();
        // Add related users and droits if you have factories for them
        User::factory()->count(2)->create(['ID_Role' => $role->ID_Role]);
        Droit::factory()->count(2)->create(['ID_Role' => $role->ID_Role]);

        // Act
        $response = $this->getJson("/api/roles/{$role->ID_Role}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'ID_Role',
                'Role',
                'users',
                'droits'
            ]);
    }

    // Security Tests
    public function test_special_characters_in_role_name() //implement input sanitization
    {
        // Arrange
        $roleData = ['Role' => 'Test<script>alert("xss")</script>Role'];

        // Act
        $response = $this->postJson('/api/roles', $roleData);

        // Assert
        $response->assertStatus(201);
        // Ensure the role name in the database is safely stored (sanitized or escaped)
        $this->assertDatabaseHas('roles', ['Role' => 'Test&lt;script&gt;alert("xss")&lt;/script&gt;Role']);

        // Optionally, you could also check the response to ensure it's sanitized on output
        $response->assertJsonFragment(['Role' => 'Test&lt;script&gt;alert("xss")&lt;/script&gt;Role']);
    }

    public function test_sql_injection_attempt_in_role_name()
    {
        // Arrange
        $roleData = ['Role' => "Test'; DROP TABLE roles; --"];

        // Act
        $response = $this->postJson('/api/roles', $roleData);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', ['Role' => "Test'; DROP TABLE roles; --"]);
        // Verify that the table still exists and no SQL injection occurred
        $this->assertDatabaseCount('roles', 1);
    }
}
