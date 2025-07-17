<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;

class ContactSeeder extends Seeder
{
    public function run()
    {
        $contacts = [
            ['Nom' => 'Smith', 'Prenom' => 'Jane', 'Birthday' => now(), 'N_assurance' => '123456', 'Cnss' => '789012', 'Telephone' => '1234567890', 'Email' => 'jane.smith@example.com', 'Adresse' => '123 Main St', 'preferences' => 'Some preferences'],
            // Add more contacts as needed
        ];

        foreach ($contacts as $contactData) {
            Contact::create($contactData);
        }
    }
}