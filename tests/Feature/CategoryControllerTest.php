<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;
use App\Models\User;
use App\Models\Prestation;
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and get JWT token
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function guest_cannot_access_category_endpoints()
    {
        // Attempt to access endpoints without authentication
        $response = $this->getJson('/api/categories');
        $response->assertStatus(401);

        $response = $this->postJson('/api/categories', ['Categories' => 'Test Category']);
        $response->assertStatus(401);

        $response = $this->getJson('/api/categories/1');
        $response->assertStatus(401);

        $response = $this->putJson('/api/categories/1', ['Categories' => 'Updated Category']);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/categories/1');
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_get_all_categories()
    {
        // Create test categories
        Category::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => ['ID_Categories', 'Categories', 'created_at', 'updated_at', 'prestations']
            ]);
    }

    /** @test */
    public function authenticated_user_can_create_category()
    {
        $categoryData = ['Categories' => 'New Test Category'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Catégorie créée avec succès',
                'data' => [
                    'Categories' => 'New Test Category'
                ]
            ]);

        $this->assertDatabaseHas('categories', $categoryData);
    }

    /** @test */
    public function category_creation_validates_required_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Categories']);
    }

    /** @test */
    public function category_name_cannot_exceed_maximum_length()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/categories', [
            'Categories' => str_repeat('a', 256)
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Categories']);
    }

    /** @test */
    public function authenticated_user_can_view_specific_category()
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/categories/{$category->ID_Categories}");

        $response->assertStatus(200)
            ->assertJson([
                'ID_Categories' => $category->ID_Categories,
                'Categories' => $category->Categories
            ]);
    }

    /** @test */
    public function returns_404_when_viewing_nonexistent_category()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/categories/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function authenticated_user_can_update_category()
    {
        $category = Category::factory()->create();
        $updateData = ['Categories' => 'Updated Category Name'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/categories/{$category->ID_Categories}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Catégorie mise à jour avec succès',
                'data' => [
                    'Categories' => 'Updated Category Name'
                ]
            ]);

        $this->assertDatabaseHas('categories', $updateData);
    }

    /** @test */
    public function category_update_validates_data()
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/categories/{$category->ID_Categories}", [
            'Categories' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Categories']);
    }

    /** @test */
    public function authenticated_user_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/categories/{$category->ID_Categories}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['ID_Categories' => $category->ID_Categories]);
    }

    /** @test */
    public function deleting_nonexistent_category_returns_404()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/categories/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function category_relationships_are_properly_loaded()
    {
        $category = Category::factory()->create();

        // Create related prestations
        $prestations = Prestation::factory()->count(3)->create([
            'ID_Categories' => $category->ID_Categories
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/categories/{$category->ID_Categories}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ID_Categories',
                'Categories',
                'created_at',
                'updated_at',
                'prestations' => [
                    '*' => [
                        'ID_Prestation',
                        'ID_Categories'
                        // Add other prestation fields as needed
                    ]
                ]
            ])
            ->assertJsonCount(3, 'prestations');
    }

    /** @test */
    public function category_creation_handles_duplicate_names()
    {
        // Create initial category
        $categoryName = 'Test Category';
        Category::factory()->create(['Categories' => $categoryName]);

        // Attempt to create another category with the same name
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/categories', [
            'Categories' => $categoryName
        ]);

        // Note: If you want to prevent duplicate categories, you should add a unique validation rule
        // This test currently passes because the backend allows duplicates
        $response->assertStatus(201);
    }

    /** @test */
    public function category_handles_special_characters()
    {
        $specialCharCategory = 'Catégorie #1 & špécial!';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/categories', [
            'Categories' => $specialCharCategory
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'Categories' => $specialCharCategory
                ]
            ]);

        $this->assertDatabaseHas('categories', [
            'Categories' => $specialCharCategory
        ]);
    }
}
