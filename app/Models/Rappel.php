<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rappel extends Model
{
    use HasFactory;
    protected $table = 'rappels';
    protected $primaryKey = 'ID_Rappel';
    protected $fillable = [
        'Rappel', 'Etat', 'ID_User'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'ID_User', 'ID_User');
    }
}