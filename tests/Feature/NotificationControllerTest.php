<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use App\Models\Notification;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $contact;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and get JWT token
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);

        // Create a contact for testing
        $this->contact = Contact::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_get_all_notifications()
    {
        // Arrange
        Notification::factory()->count(3)->create([
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/notifications');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => ['ID_Notification', 'Notification', 'Etat', 'ID_Contact', 'created_at', 'updated_at']
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_notifications()
    {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_create_notification()
    {
        $notificationData = [
            'Notification' => 'Test notification',
            'Etat' => 'active',
            'ID_Contact' => $this->contact->ID_Contact
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/notifications', $notificationData);

        $response->assertStatus(201)
            ->assertJsonPath('data.Notification', $notificationData['Notification'])
            ->assertJsonPath('data.Etat', $notificationData['Etat']);
    }

    /** @test */
    public function notification_creation_requires_valid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/notifications', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Notification', 'Etat', 'ID_Contact']);
    }

    /** @test */
    public function notification_creation_validates_contact_exists()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/notifications', [
            'Notification' => 'Test notification',
            'Etat' => 'active',
            'ID_Contact' => 99999 // Non-existent contact ID
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ID_Contact']);
    }

    /** @test */
    public function authenticated_user_can_view_specific_notification()
    {
        $notification = Notification::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/notifications/{$notification->ID_Notification}");

        $response->assertStatus(200)
            ->assertJson(['ID_Notification' => $notification->ID_Notification]);
    }

    /** @test */
    public function returns_404_for_non_existent_notification()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/notifications/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function authenticated_user_can_update_notification()
    {
        $notification = Notification::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $updateData = [
            'Notification' => 'Updated notification',
            'Etat' => 'inactive'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/notifications/{$notification->ID_Notification}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.Notification', $updateData['Notification'])
            ->assertJsonPath('data.Etat', $updateData['Etat']);
    }

    /** @test */
    public function partial_update_of_notification_is_possible()
    {
        $notification = Notification::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/notifications/{$notification->ID_Notification}", [
            'Etat' => 'inactive'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.Etat', 'inactive')
            ->assertJsonPath('data.Notification', $notification->Notification);
    }

    /** @test */
    public function authenticated_user_can_delete_notification()
    {
        $notification = Notification::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/notifications/{$notification->ID_Notification}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('notifications', [
            'ID_Notification' => $notification->ID_Notification
        ]);
    }

    /** @test */
    public function deleting_non_existent_notification_returns_404()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/notifications/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function notification_is_deleted_when_contact_is_deleted()
    {
        $notification = Notification::factory()->create([
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $this->contact->delete();

        $this->assertDatabaseMissing('notifications', [
            'ID_Notification' => $notification->ID_Notification
        ]);
    }

    /** @test */
    public function large_notification_text_can_be_stored()
    {
        $longText = str_repeat('a', 1000);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/notifications', [
            'Notification' => $longText,
            'Etat' => 'active',
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.Notification', $longText);
    }

    /** @test */
    public function notification_state_must_be_string()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/notifications', [
            'Notification' => 'Test notification',
            'Etat' => 123, // Numeric instead of string
            'ID_Contact' => $this->contact->ID_Contact
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Etat']);
    }
}
