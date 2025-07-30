<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JourFerie extends Model
{
    use HasFactory;
    protected $table = 'jour_feries';
    protected $primaryKey = 'Id_Jour_feries';
    protected $fillable = [
        'Date_debut', 'Date_fin'
    ];
}