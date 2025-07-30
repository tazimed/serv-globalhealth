<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $table = 'contacts';
    protected $primaryKey = 'ID_Contact';
    protected $fillable = [
        'Nom', 'Prenom', 'Birthday', 'N_assurance', 'Cnss', 'Telephone', 'Email', 'Adresse', 'preferences'
    ];

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'ID_Contact', 'ID_Contact');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'ID_Contact', 'ID_Contact');
    }

    public function documentContacts()
    {
        return $this->hasMany(DocumentContact::class, 'ID_Contact', 'ID_Contact');
    }
}