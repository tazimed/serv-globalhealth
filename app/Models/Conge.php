<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conge extends Model
{
    use HasFactory;
    protected $table = 'conges';
    protected $primaryKey = 'Id_Conge';
    protected $fillable = [
        'Date_debut', 'Date_fin', 'Type', 'ID_User'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'ID_User', 'ID_User');
    }
}