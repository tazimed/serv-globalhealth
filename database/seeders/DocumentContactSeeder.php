<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentContact;
use App\Models\Contact;

class DocumentContactSeeder extends Seeder
{
    public function run()
    {
        $contacts = Contact::all();

        $documents = [
            ['Nom_Doc' => 'Sample Document 1', 'Doc' => 'path/to/sample_document_1.pdf', 'ID_Contact' => $contacts->first()->ID_Contact],
            // Add more documents as needed
        ];

        foreach ($documents as $documentData) {
            DocumentContact::create($documentData);
        }
    }
}