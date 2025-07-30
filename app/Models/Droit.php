<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Droit extends Model
{
    use HasFactory;
    protected $table = 'droits';
    protected $primaryKey = 'ID_Droit';
    protected $fillable = [
        'Droit', 'Lecture', 'Ajouter', 'Modifier', 'Supprimer', 'ID_Role'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'ID_Role', 'ID_Role');
    }
}