<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    use HasFactory;
    protected $table = 'rendez_vous';
    protected $primaryKey = 'ID_Rendez_Vous';
    protected $fillable = [
        'Duration', 'Date', 'Status', 'ID_User', 'ID_Contact', 'ID_Prestation','subject'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'ID_User', 'ID_User');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'ID_Contact', 'ID_Contact');
    }

    public function prestation()
    {
        return $this->belongsTo(Prestation::class, 'ID_Prestation', 'ID_Prestation');
    }
}