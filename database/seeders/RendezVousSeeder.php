<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RendezVous;
use App\Models\User;
use App\Models\Contact;
use App\Models\Prestation;

class RendezVousSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $contacts = Contact::all();
        $prestations = Prestation::all();

        $rendezvous = [
            ['Frequence' => 'Weekly', 'Date' => now(), 'Status' => 'Scheduled', 'ID_User' => $users->first()->ID_User, 'ID_Contact' => $contacts->first()->ID_Contact, 'ID_Prestation' => $prestations->first()->ID_Prestation],
            // Add more rendezvous as needed
        ];

        foreach ($rendezvous as $rendezvousData) {
            RendezVous::create($rendezvousData);
        }
    }
}