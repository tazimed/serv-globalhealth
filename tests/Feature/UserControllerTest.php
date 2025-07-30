<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $role;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a role for testing
        $this->role = Role::factory()->create();

        // Create a test user
        $this->user = User::factory()->create([
            'ID_Role' => $this->role->ID_Role,
            'Email' => 'test@example.com',
            'Password' => Hash::make('password123')
        ]);

        // Get JWT token
        $response = $this->post('/api/login', [
            'Email' => 'test@example.com',
            'Password' => 'password123'
        ]);

        $this->token = $response->json()['token'];
    }

    /** @test */
    public function user_can_register_with_valid_data()
    {
        Storage::fake('local');

        $userData = [
            'Nom' => 'Doe',
            'Prenom' => 'John',
            'Email' => 'john.doe@example.com',
            'Password' => 'password123',
            'Photo' => UploadedFile::fake()->image('avatar.jpg'),
            'Post' => 'Developer',
            'Tel' => '1234567890',
            'Sex' => 'Male',
            'Adresse' => '123 Test St',
            'Specialisation' => 'PHP Development',
            'Salaire' => 5000.00,
            'Heur_sup_prime' => 25.00,
            'Delai_rappel' => 7,
            'ID_Role' => $this->role->ID_Role
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'token'
            ]);

        $this->assertDatabaseHas('users', [
            'Email' => 'john.doe@example.com',
            'Nom' => 'Doe',
            'Prenom' => 'John'
        ]);

        Storage::disk('local')->assertExists('user_photos/' . basename($response->json()['data']['Photo']));
    }

    /** @test */
    public function user_cannot_register_with_duplicate_email()
    {
        $userData = [
            'Nom' => 'Doe',
            'Prenom' => 'John',
            'Email' => 'test@example.com', // Already exists from setUp
            'Password' => 'password123',
            'ID_Role' => $this->role->ID_Role
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertStatus(400)
            ->assertJson([
                'Email' => ['Cette adresse email est déjà enregistrée.']
            ]);

    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $response = $this->post('/api/login', [
            'Email' => 'test@example.com',
            'Password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user'
            ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $response = $this->post('/api/login', [
            'Email' => 'test@example.com',
            'Password' => 'wrongpassword'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_get_their_profile()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->get('/api/users/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ID_User',
                'Nom',
                'Prenom',
                'Email'
            ]);
    }

    /** @test */
    public function user_can_update_their_profile()
    {
        Storage::fake('local');

        $updateData = [
            'Nom' => 'Updated',
            'Prenom' => 'Name',
            'Tel' => '9876543210',
            'Photo' => UploadedFile::fake()->image('new_avatar.jpg')
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post('/api/users/' . $this->user->ID_User, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Utilisateur mis à jour avec succès'
            ]);

        $this->assertDatabaseHas('users', [
            'ID_User' => $this->user->ID_User,
            'Nom' => 'Updated',
            'Prenom' => 'Name',
            'Tel' => '9876543210'
        ]);
    }

    /** @test */
    public function user_cannot_update_email_to_existing_one()
    {
        User::factory()->create(['Email' => 'existing@example.com']);

        $updateData = [
            'Email' => 'existing@example.com'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post('/api/users/' . $this->user->ID_User, $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Email']);
    }

    /** @test */
    public function user_can_be_deleted()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->delete('/api/users/' . $this->user->ID_User);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', [
            'ID_User' => $this->user->ID_User
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->get('/api/users');
        $response->assertStatus(401);

        $response = $this->get('/api/users/me');
        $response->assertStatus(401);

        $response = $this->post('/api/users/1', ['Nom' => 'Test']);
        $response->assertStatus(401);

        $response = $this->delete('/api/users/1');
        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_logout()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->post('/api/users/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);
    }

    /** @test */
    public function registration_validates_required_fields()
    {
        $response = $this->post('/api/register', []);

        $response->assertStatus(400)
            ->assertJson([
                'Nom' => ['Le nom est requis.'],
                'Prenom' => ['Le prénom est requis.'],
                'Email' => ['L\'email est requis.'],
                'Password' => ['Le mot de passe est requis.'],
                'ID_Role' => ['L\'ID du rôle est requis.']
            ]);
    }

    /** @test */
    public function user_photo_can_be_retrieved()
    {
        // Create the storage directory if it doesn't exist
        $storageDir = storage_path('app/user_photos');
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Create a fake image with a specific name
        $originalFileName = 'test_photo.jpg';
        $file = UploadedFile::fake()->image($originalFileName);

        // Generate the same filename format as in the registration
        $fileName = now()->format('Y-m-d_His') . '_' . $originalFileName;
        $path = 'user_photos/' . $fileName;

        // Actually store the file in the real storage location
        $file->move(storage_path('app/user_photos'), $fileName);

        // Create a user with this exact path
        $user = User::factory()->create([
            'Photo' => $path
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->get('/api/users/' . $user->ID_User . '/photo');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'image/jpeg');

        // Cleanup
        if (file_exists(storage_path('app/' . $path))) {
            unlink(storage_path('app/' . $path));
        }
    }

    /** @test */
    public function password_must_be_minimum_eight_characters()
    {
        $userData = [
            'Nom' => 'Test',
            'Prenom' => 'User',
            'Email' => 'test.user@example.com',
            'Password' => '123', // Too short
            'ID_Role' => $this->role->ID_Role
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertStatus(400)
            ->assertJson([
                'Password' => ['Le mot de passe doit contenir au moins 8 caractères.']
            ]);
    }
}
