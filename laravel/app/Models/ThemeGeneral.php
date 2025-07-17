<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeGeneral extends Model
{
    use HasFactory;
    protected $table = 'theme_general';
    protected $primaryKey = 'ID_Thems';
    protected $fillable = [
        'Couleurs', 'Horaire_Travail'
    ];
}