<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentUser;
use App\Models\User;

class DocumentUserSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $documents = [
            ['Nom_Doc' => 'Sample Document 1', 'Doc' => 'path/to/sample_document_1.pdf', 'ID_User' => $users->first()->ID_User],
            // Add more documents as needed
        ];

        foreach ($documents as $documentData) {
            DocumentUser::create($documentData);
        }
    }
}