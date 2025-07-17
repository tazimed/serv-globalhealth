<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';
    protected $primaryKey = 'ID_Notification';
    protected $fillable = [
        'Notification', 'Etat', 'ID_Contact'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'ID_Contact', 'ID_Contact');
    }
}