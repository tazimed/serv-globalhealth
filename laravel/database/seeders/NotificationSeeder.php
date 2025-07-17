<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\Contact;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $contacts = Contact::all();

        $notifications = [
            ['Notification' => 'New Message', 'Etat' => 'Unread', 'ID_Contact' => $contacts->first()->ID_Contact],
            // Add more notifications as needed
        ];

        foreach ($notifications as $notificationData) {
            Notification::create($notificationData);
        }
    }
}