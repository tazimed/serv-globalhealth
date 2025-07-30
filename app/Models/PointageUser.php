<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointageUser extends Model
{
    protected $table = 'pointage_user';
    protected $primaryKey = ['ID_Pointage', 'ID_User'];
    protected $fillable = ['ID_Pointage', 'ID_User', 'Heur_Travail', 'Abssance'];
    public $incrementing = false;
    public $timestamps = false;
}