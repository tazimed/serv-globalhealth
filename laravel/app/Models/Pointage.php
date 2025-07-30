<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    use HasFactory;
    protected $table = 'pointages';
    protected $primaryKey = 'ID_Pointage';
    protected $fillable = [
        'Date'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'pointage_user', 'ID_Pointage', 'ID_User')->withPivot('Heur_Travail', 'Absence');
    }
}
