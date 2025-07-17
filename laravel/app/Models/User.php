<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    protected $table = 'users';
    protected $primaryKey = 'ID_User';
    protected $fillable = [
        'Nom', 'Prenom', 'Email', 'Password', 'Photo', 'Post', 'Tel', 'Adresse', 'Specialisation', 'Salaire', 'Heur_sup_prime', 'Delai_rappel', 'Sex', 'ID_Role'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'ID_Role', 'ID_Role');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'ID_User', 'ID_User');
    }

    public function rappels()
    {
        return $this->hasMany(Rappel::class, 'ID_User', 'ID_User');
    }

    public function documentUsers()
    {
        return $this->hasMany(DocumentUser::class, 'ID_User', 'ID_User');
    }

    public function conges()
    {
        return $this->hasMany(Conge::class, 'ID_User', 'ID_User');
    }

    public function pointages()
    {
        return $this->belongsToMany(Pointage::class, 'pointage_user', 'ID_User', 'ID_Pointage')->withPivot('Heur_Travail', 'Abssance');
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'ID_User', 'ID_User');
    }

        // Implement the methods from JWTSubject
        public function getJWTIdentifier()
        {
            return $this->getKey();
        }
    
        public function getJWTCustomClaims()
        {
            return [];
        }
}