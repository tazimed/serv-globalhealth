<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use App\Models\Prestation;
use App\Models\RendezVous;
use Tymon\JWTAuth\Facades\JWTAuth;

class RendezVousControllerTest extends TestCase
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
    public function unauthenticated_users_cannot_access_rendez_vous_endpoints()
    {
        $response = $this->getJson('/api/rendez_vous');
        $response->assertStatus(401);

        $response = $this->postJson('/api/rendez_vous', []);
        $response->assertStatus(401);
    }

    /** @test */
    public function can_get_all_rendez_vous()
    {
        RendezVous::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/rendez_vous');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function can_create_rendez_vous()
    {
        $contact = Contact::factory()->create();
        $prestation = Prestation::factory()->create();

        $data = [
            'Frequence' => 'hebdomadaire',
            'Date' => '2025-03-01',
            'Status' => 'planifié',
            'ID_User' => $this->user->ID_User,
            'ID_Contact' => $contact->ID_Contact,
            'ID_Prestation' => $prestation->ID_Prestation
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/rendez_vous', $data);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Rendez-vous créé avec succès'
            ]);

        $this->assertDatabaseHas('rendez_vous', [
            'Frequence' => 'hebdomadaire',
            'Date' => '2025-03-01'
        ]);
    }

    /** @test */
    public function cannot_create_rendez_vous_with_invalid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/rendez_vous', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Frequence', 'Date', 'Status', 'ID_Contact', 'ID_Prestation']);
    }

    /** @test */
    public function cannot_create_rendez_vous_with_nonexistent_foreign_keys()
    {
        $data = [
            'Frequence' => 'hebdomadaire',
            'Date' => '2025-03-01',
            'Status' => 'planifié',
            'ID_User' => 999999,
            'ID_Contact' => 999999,
            'ID_Prestation' => 999999
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/rendez_vous', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ID_User', 'ID_Contact', 'ID_Prestation']);
    }

    /** @test */
    public function can_update_rendez_vous()
    {
        $rendezvous = RendezVous::factory()->create();

        $data = [
            'Frequence' => 'mensuelle',
            'Status' => 'confirmé'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/rendez_vous/{$rendezvous->ID_Rendez_Vous}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Rendez-vous mis à jour avec succès'
            ]);

        $this->assertDatabaseHas('rendez_vous', [
            'ID_Rendez_Vous' => $rendezvous->ID_Rendez_Vous,
            'Frequence' => 'mensuelle',
            'Status' => 'confirmé'
        ]);
    }

    /** @test */
    public function can_show_rendez_vous()
    {
        $rendezvous = RendezVous::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/rendez_vous/{$rendezvous->ID_Rendez_Vous}");

        $response->assertStatus(200)
            ->assertJson([
                'ID_Rendez_Vous' => $rendezvous->ID_Rendez_Vous
            ]);
    }

    /** @test */
    public function returns_404_when_rendez_vous_not_found()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/rendez_vous/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function can_delete_rendez_vous()
    {
        $rendezvous = RendezVous::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/rendez_vous/{$rendezvous->ID_Rendez_Vous}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('rendez_vous', [
            'ID_Rendez_Vous' => $rendezvous->ID_Rendez_Vous
        ]);
    }

    /** @test */
    public function date_must_be_valid()
    {
        $contact = Contact::factory()->create();
        $prestation = Prestation::factory()->create();

        $data = [
            'Frequence' => 'hebdomadaire',
            'Date' => 'invalid-date',
            'Status' => 'planifié',
            'ID_User' => $this->user->ID_User,
            'ID_Contact' => $contact->ID_Contact,
            'ID_Prestation' => $prestation->ID_Prestation
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/rendez_vous', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['Date']);
    }

    /** @test */
    public function can_update_partial_rendez_vous_data()
    {
        $rendezvous = RendezVous::factory()->create();
        $originalDate = $rendezvous->Date;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/rendez_vous/{$rendezvous->ID_Rendez_Vous}", [
            'Status' => 'annulé'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('rendez_vous', [
            'ID_Rendez_Vous' => $rendezvous->ID_Rendez_Vous,
            'Status' => 'annulé',
            'Date' => $originalDate
        ]);
    }

    /** @test */
    public function rendez_vous_cascade_deletes_work()
    {
        $rendezvous = RendezVous::factory()->create();
        $id = $rendezvous->ID_Rendez_Vous;

        // Delete the user (which should cascade to delete the rendez-vous)
        $rendezvous->user->delete();

        $this->assertDatabaseMissing('rendez_vous', [
            'ID_Rendez_Vous' => $id
        ]);
    }
}
